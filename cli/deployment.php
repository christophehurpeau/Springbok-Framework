<?php
exit('deprecated');
$options=$argv['options']+array('simulation'=>true,'backup'=>false);
$projectPath=$argv['projectPath'];
if(!is_dir($projectPath))
	throw new Exception('Project path does not exists: '.$projectPath);

$dbName=Workspace::findValueDb_nameById($argv['workspace_id']);
if(empty($dbName)) die('Unknown workspace');
Model::$__dbName=$dbName;

switch($argv['type']){
	case 'app':
		$deployment=Deployment::QOne()->byId($argv['deployment_id'])->with('Server')->fetch();
		if(empty($deployment) || empty($deployment->server)) die('Unknown deployment');
		
		$sc=ServerCore::findOneIdAndPathByServer_idAndVersion($deployment->server_id,Springbok::VERSION);
		if(empty($sc)){
			echo '-- CORE VERSION IS NOT UP-TO-DATE ON SERVER --'.PHP_EOL;
			sleep(4);
			echo UExec::exec('php '.escapeshellarg(dirname(CORE).DS.'depl_'.$deployment->server->name.'.php'));
			
			$sc=ServerCore::findOneIdAndPathByServer_idAndVersion($deployment->server_id,Springbok::VERSION);
			if(empty($sc)) die('CORE UPDATE FAILED !'.PHP_EOL);
		}
		
		$argv['target']=$deployment->path().DS;
		
		
		if (!$options['simulation']){
			 if ($options['backup']){
			 	$options['exclude']=NULL; // --exclude .* ?
				$target = $options['backup'].DS;
				UExec::rsync($projectPath,$target,$options);
			 }
		}
		$options['exclude']=array('logs/','db','data','.htacess','authfile','schema.php','job.php','cli.php');
		
		
		$baseDefine="
define('DS', DIRECTORY_SEPARATOR);
define('CORE','".$deployment->server->core_dir.DS.$sc->path.DS."');
define('APP', __DIR__.DS);
define('ENV','".$deployment->server->env_name."');";
		
		$indexContentStarted="<?php".$baseDefine."
include CORE.'app.php';";

		$indexContentStopped="<?php
header('HTTP/1.1 503 Service Temporarily Unavailable',true,503);
define('DS', DIRECTORY_SEPARATOR);
define('CORE','".$deployment->server->core_dir.DS.$sc->path.DS."');
define('APP', __DIR__.DS);
if(file_exists((".'$filename'."=CORE.'maintenance.php'))) include ".'$filename'.";
else echo '<h1>503 Service Temporarily Unavailable</h1>';";
		
		$tmpfname = tempnam('/tmp','coredepl');
		
		file_put_contents($tmpfname,"<?php".$baseDefine."
".'$action'."='schema';
include CORE.'cli.php';");
		echo UExec::copyFile($tmpfname,$argv['target'].'schema.php',$options['ssh']);
		
		file_put_contents($tmpfname,"<?php".$baseDefine."
".'$action'."='job';
include CORE.'cli.php';");
		echo UExec::copyFile($tmpfname,$argv['target'].'job.php',$options['ssh']);
		
				
		file_put_contents($tmpfname,"<?php".$baseDefine."
".'$action'."=".'$argv[1];'."
include CORE.'cli.php';");
		echo UExec::copyFile($tmpfname,$argv['target'].'cli.php',$options['ssh']);
		
		
		if(file_exists($filename=$projectPath.'web'.DS.'js'.DS.'global.js')){
			$jsFile=file($filename);
			$line0="var baseUrl='".$deployment->base_url."',webdir=baseUrl+'web/',imgdir=webdir+'img/',jsdir=webdir+'js/';\n";
			if($jsFile[0]!=$line0){
				$jsFile[0]=$line0;
				file_put_contents($filename,implode('',$jsFile));
			}
		}
		
		// 503 Service Temporarily Unavailable
		file_put_contents($tmpfname,$indexContentStopped);
		echo UExec::copyFile($tmpfname,$argv['target'].'index.php',$options['ssh']);
		
		if(!empty($argv['entries']))
			foreach($argv['entries'] as $entry) 
				echo UExec::copyFile($tmpfname,$argv['target'].$entry.'.php',$options['ssh']);
		
		break;
	case 'core':
		$server_core_dir=Server::findValueCore_dirById($argv['server_id']);
		if(empty($server_core_dir)) die('Unknown server');
		
		if(ServerCore::existByServer_idAndVersion($argv['server_id'],Springbok::VERSION)) die('this core is already up-to-date');
		
		$sc_path='springbok-'.date('m-d');
		if($id=ServerCore::findValueIdByServer_idAndPath($argv['server_id'],$sc_path)) ServerCore::updateOneFieldByPk($id,'version',Springbok::VERSION);
		else{
			$sc=new ServerCore();
			$sc->server_id=$argv['server_id'];
			$sc->version=Springbok::VERSION;
			$sc->path=$sc_path;
			$sc->insert();
		}
		$argv['target']=$server_core_dir.DS.$sc_path.DS;
		$options['exclude']=array('.svn/');
		break;
	default:
		die('unknown type');
}

echo UExec::rsync($projectPath,$argv['target'],$options);

if($argv['type'] == 'app'){
	if(is_dir($argv['dbPath'])){
		$options['exclude']=array('.svn/');
		echo UExec::rsync($argv['dbPath'],$argv['target'].'db/',$options);
	}
	
	Deployment::updateOneFieldByPk($deployment->id,'server_core_id',$sc->id);
	
	
	echo UExec::exec('php '.escapeshellarg($argv['target'].'schema.php'),$options['ssh']);
	
	file_put_contents($tmpfname,$indexContentStarted);
	echo UExec::copyFile($tmpfname,$argv['target'].'index.php',$options['ssh']);
	
	if(!empty($argv['entries']))
		foreach($argv['entries'] as $entry) 
			echo UExec::copyFile($tmpfname,$argv['target'].$entry.'.php',$options['ssh']);
	
	/* UPDATE CRON */
	
	if(file_exists($jobsFilePath=$projectPath.'config'.DS.'jobs.php')){
		$jobs=include $jobsFilePath;
		
		/*
		 * minute (0-59), hour (0-23, 0 = midnight), day (1-31), month (1-12), weekday (0-6, 0 = Sunday), command
		 * x,y = at x and y
		 * x-y = every _ between x and y
		 * * /x = every x _ => * /10 => 0,10,20,30,40,50
		*/
		$cronfile='';
		
		foreach($jobs as $jobName=>$job){
			$cronfile.=$job.' www-data php '.escapeshellarg($argv['target'].'job.php').' '.$jobName.PHP_EOL;
		}
		
		if(!empty($cronfile)){
			file_put_contents($tmpfname,$cronfile);
			echo UExec::copyFile($tmpfname,'/etc/cron.d/springbok-'.$argv['deployment_id'],$options['ssh']);
		}
	}
	
	/* Delete tmp file */
	unlink($tmpfname);
}