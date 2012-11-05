<?php
include_once __DIR__.DS.'AEnhance.php';
include_once __DIR__.DS.'DefaultFolderEnhancer.php';
include_once __DIR__.DS.'DelayedEnhance.php';
include_once __DIR__.DS.'EnhancedApp.php';
include_once __DIR__.'/../utils/UColors.php';
include_once __DIR__.'/../components/CLogger.php';

define('CORE_SRC',dirname(CORE).'/src/');

class EnhanceApp extends AEnhance{
	public function __construct($dirname){
		$this->enhanced=new EnhancedApp('app',$dirname);
	}
	
	public function init(){
		foreach(array('project_name','projectName') as $attr){
			if(!$this->enhanced->appConfigExist($attr)) throw new Exception('Missing attr config : '.$attr);
		}
	}
	
	public function initDev(&$dev){
		parent::initDev($dev);
		
		global $enhancers;
		foreach($enhancers as $className){
			$className::initFolder($dev,$this->enhanced->getConfig());
			if($className::$CACHE_PATH!==false) if(!file_exists($newDir=$this->enhanced->getTmpDir().$className::$CACHE_PATH)) mkdir($newDir,0777);
		}
		
		$d=new Folder($dev->getPath().'logs',0777);
		//use springbok-chown
		//$d=new Folder($dataFolder=(dirname($dev->getPath()).'/data/'),0777);
		//$d=new Folder($dataFolder.'logs/',0777);
	}
	
	public function initProd(&$prod){	
		parent::initProd($prod);
		
		global $enhancers;
		foreach($enhancers as $className)
			$className::initFolder($prod,$this->enhanced->getConfig());
		
		$d=new Folder($prod->getPath().'logs',0777);
	}
	
	public function afterInit(&$dev,&$prod){
		$this->createIndexFile($dev,$prod);
		
		$base="<?php
define('DS', DIRECTORY_SEPARATOR);
define('CORE','".dirname(CORE)."/dev/"."');
define('CLIBS','".dirname(CORE)."/libs/dev/');
define('APP', __DIR__.'/dev/');";
		file_put_contents($this->enhanced->getAppDir().'cli.php',$base.'unset($argv[0]);'."\n".'$action=array_shift($argv);'."\n"."include CORE.'cli.php';");
		file_put_contents($this->enhanced->getAppDir().'job.php',$base.'$action="job";'."\n"."include CORE.'cli.php';");
		file_put_contents($this->enhanced->getAppDir().'daemon.php',$base.'$action="daemon";'."\n"."include CORE.'cli.php';");
	}
	
	
	private function recursiveCopyDir(&$srcDir,$dests,$recursiveMkdir=true){
		$logger=$this->enhanced->getLogger();
		$logger->log('RD: '.$srcDir->getName());
		$dests=array_map(function(&$d) use(&$srcDir){return $d.$srcDir->getName().'/';},$dests);
		$isLink=is_link($srcDir->getPath());
		
		if($isLink) throw new Exception("IS link ; ".$srcDir->getPath() );
		
		
		if(!file_exists($dests[0])) $isLink ? symlink($dests[0],$readLink=readlink($srcDir->getPath())) : mkdir($dests[0],0775,$recursiveMkdir);
		if(!file_exists($dests[1])) $isLink ? symlink($dests[0],isset($readLink)?$readLink:readlink($srcDir->getPath())) : mkdir($dests[1],0775,$recursiveMkdir);
		
		if($isLink) return;
		
		 /*if(!file_exists($dests[0]) || !file_exists($dests[1])){
			$this->newDef['changes']['all'][]=$srcDir->getPath();
			$srcDir->copyTo($dests[0],0755);
			$srcDir->copyTo($dests[1],0755);
		}else{*/
				
			foreach($srcDir->listFiles() as $f) $this->copyFile($f,$dests);
			foreach($srcDir->listDirs() as $f) $this->recursiveCopyDir($f,$dests,false);
		/*}*/
	}
	
	private function copyFile(&$srcFile,$dests){
		$srcMd5=md5_file($srcFile->getPath());
		$dests=array_map(function(&$d) use(&$srcFile){return $d.$srcFile->getName();},$dests);
		foreach($dests as $dest){
			//$dest.$srcFile->getName();
			if(!file_exists($dest) || $srcMd5 != md5_file($dest)){//debugVar(!file_exists($dest)/*,$srcMd5 != md5_file($dest)*/);
				$srcFile->copyTo($dest);
				$this->enhanced->newDef['changes']['all'][]=array('path'=>$srcFile->getPath());
				//echo $dest.'('.$srcMd5.' - '.md5_file($dest).')'.'<br />';
			}
		}
	}
	
	public function afterEnhance(&$dev,&$prod){
		if(!file_exists($path=($dev->getPath().'daemons/'))) mkdir($path);
		/* if(!file_exists($path=($dev->getPath().'daemons/delayedEnhanceDaemon.php')) || true) copy(CORE.'enhancers/daemon.php',$path); */
		//UExec::exec('php '.escapeshellarg($this->enhanced->getAppDir().'daemon.php').' delayedEnhance default');
		//if(!empty($this->config['includes'])){
		if($this->enhanced->configEmpty('includes')) $this->enhanced->config['includes']=array();
		$this->enhanced->config['includes']['img'][]='ajax';
		$this->enhanced->config['includes']['js'][]='ie-lt9.js';
		/*$this->enhanced->config['includes']['css'][]='PIE.htc';*/
			foreach($this->enhanced->config['includes'] as $type=>$includes){
				if(is_string($includes)){ $includes=explode(',',$includes); $type=''; }
				else $type=$type.DS;
				foreach($includes as $filename){
					$srcFile=CORE_SRC.'includes/'.$type.$filename;
					if(!file_exists($srcFile)) $srcFile=dirname(CORE).'/includes/'.$type.$filename;
					
					$dests=array($dev->getPath().'web/'.$type,$prod->getPath().'web/'.$type);
					
					if(is_dir($srcFile)){
						//if(!file_exists($this->appDir.'src/web/'.$type.$filename)) throw new Exception('You should create the folder : web/'.$type.$filename);
						
						$srcFile=new Folder($srcFile);
						$this->recursiveCopyDir($srcFile,$dests);
					}else{
						$srcFile=new File($srcFile);
						$this->copyFile($srcFile,$dests);
					}
				}
			}
		//}
		//if($this->enhanced->configNotEmpty('plugins')){
			$pathsProcessed=array($this->enhanced->getAppDir().'src/');
			foreach($this->enhanced->config['plugins'] as &$plugin){
				$this->enhanced->setType('plugin',$plugin[1]);
				$pluginPath=$this->enhanced->pluginPath($plugin);
				if(!isset($plugin[2]))
					$this->recursiveDir($pluginPath,new Folder($pluginPath), $dev->getPath(), $prod->getPath(),$pathsProcessed);
				$pathsProcessed[]=$pluginPath;
			}
		//}
		/*DelayedEnhance::get($this->enhanced)->commit();
		UExec::exec('php '.escapeshellarg($this->enhanced->getAppDir().'daemon.php').' delayedEnhance default',false,false);
		*/
		
		/*$webFolder=date('mdH');
		
		if(!empty($this->oldDef['webFolders']) && $this->oldDef['webFolders']!=$webFolder){
			$fullWebFolder='web/'.$this->oldDef['webFolders'];
			if(file_exists($dev->getPath().$fullWebFolder)) UExec::exec('rm '.escapeshellarg($dev->getPath().$fullWebFolder));
			if(file_exists($prod->getPath().$fullWebFolder)) UExec::exec('rm '.escapeshellarg($prod->getPath().$fullWebFolder));
		}
		
		$fullWebFolder='web/'.$webFolder.'/';
		if(!file_exists($dev->getPath().$fullWebFolder))
			UExec::exec('cd '.escapeshellarg($dev->getPath().'web/').' && ln -s . '.$webFolder);
		if(!file_exists($prod->getPath().$fullWebFolder))
			UExec::exec('cd '.escapeshellarg($prod->getPath().'web/').' && ln -s . '.$webFolder);
		//debugVar('cd '.escapeshellarg($dev->getPath().'web/').' && ln -s . '.$webFolder);
		$this->newDef['webFolders']=$webFolder;*/
	}
	
	/**
	 * @param Folder $dir
	 */
	public function recursiveDir($srcDir,Folder $dir,$devDir,$prodDir,$override=true,$class=false){
		$dirs=$dir->listDirs(false);
		$devFolder=new Folder($devDir); $prodFolder=new Folder($prodDir);
		
		$this->enhanced->newDef['enhancedFolders'][$dir->getPath()]=array('dev'=>$devDir,'prod'=>$prodDir);
		
		$defaultClass=$class;
		foreach($dirs as $d){
			$dPath=$d->getPath();
			//$t=microtime(true);
			if(is_link($dPath)) continue;
			$dirname=$d->getName();
			if($dirname[0]==='.' || $dPath===$srcDir.'web/tinymce/' || $dPath===$srcDir.'db/' || $dPath===$srcDir.'sql/') continue;
			
			
			if($dPath===$srcDir.'web/files/' || $dPath===$srcDir.'web/img/icons/'){
				//$this->recursiveCopyDir($d,array($devDir,$prodDir));
				//symlink($devDir,$dPath);
				//symlink($prodDir,$dPath);
				UExec::exec('ln -s '.escapeshellarg($dPath).' '.escapeshellarg($devDir.$dirname));
				UExec::exec('ln -s '.escapeshellarg($dPath).' '.escapeshellarg($prodDir.$dirname));
				continue;
			}
			
			$newDevDir=$devDir.$dirname.DS; $newProdDir=$prodDir.$dirname.DS;
			$newOverride=$override===true ? true : array_map(function($override) use($dirname){return $override.$dirname.DS;},$override);
			$allowUnderscoredFiles=false;
			
			if($defaultClass===false){
				$class='PhpFile';
				
				if($dPath===$srcDir.'config/'){ $class='ConfigFile'; $allowUnderscoredFiles=true; }
				elseif(startsWith($dPath,$srcDir.'controllers')){ $class='ControllerFile'; }
				elseif($dPath===$srcDir.'jobs/') $class='JobFile';
				elseif($dPath===$srcDir.'daemons/') $class='DaemonFile';
				elseif($dPath===$srcDir.'models/'){ $class='ModelFile'; }
				elseif($dPath===$srcDir.'modules/') $class='ModuleFile';
				elseif(startsWith($dPath,$srcDir.'views')) $class='ViewFile';
				elseif($dPath===$srcDir.'jsapp/') $class='UselessFile'; // ne concerne que les .php
				
			}else $class=$defaultClass;
			
			$folderEnhancer=new DefaultFolderEnhancer($this->enhanced,$d, $newDevDir,$newProdDir);
			$folderEnhancer->process($class,$allowUnderscoredFiles,$newOverride);
			
			$this->recursiveDir($srcDir,$d, $newDevDir,$newProdDir,$newOverride,$class);
		}
	}
	
	
	private function createIndexFile(&$dev,&$prod){
		$entries=$this->enhanced->configNotEmpty('entries') ? $this->enhanced->config('entries') : array();

		file_put_contents($dev->getPath().'env.php','<?php return include dirname(CORE)."/env.php";');
		file_put_contents($prod->getPath().'env.php','<?php return include dirname(CORE)."/env.php";');
		
		$htaccess=
'Options -Indexes
DirectoryIndex disabled
DirectorySlash Off
<IfModule mod_rewrite.c>
	RewriteEngine on
	RewriteRule ^web/(.*)$ web/$1 [NE,L]';

		foreach($entries as $entry)
			$htaccess.='
	RewriteRule ^'.$entry.'/(.*)$ '.$entry.'.php?url=$1 [QSA,NE,NS,L]
	RewriteRule ^'.$entry.'.php/(.*)$ '.$entry.'.php?url=$1 [QSA,NE,NS,L]';
	
	
		/*foreach($entries as $entry)
			$htaccess.='
	RewriteCond %{REQUEST_URI} !'.$entry.'.php';*/
		$htaccess.='
	RewriteRule ^(.*)$ index.php?url=$1 [QSA,NE,NS,L]
 </IfModule>';
		file_put_contents($dev->getPath().'.htaccess',$htaccess);
		file_put_contents($prod->getPath().'.htaccess',$htaccess); // for real production => put that in apache conf
			
		//if(!empty($this->newDef['changes'])){
			$baseDev="<?php
define('DS', DIRECTORY_SEPARATOR);
define('CORE','".dirname(CORE)."/dev/');
define('CLIBS','".dirname(CORE)."/libs/dev/');
define('APP', __DIR__.DS);";
			
			$indexDevContent=$baseDev."
include CORE.'app.php';";

			$indexProdContent="<?"."php
header('HTTP/1.1 503 Service Temporarily Unavailable',true,503);
define('DS', DIRECTORY_SEPARATOR);
define('CORE','".dirname(CORE)."/prod/');
define('APP', __DIR__.DS);
define('APP_DATE',".time()."); define('APP_VERSION',''); define('WEB_FOLDER','');
if(file_exists((".'$filename'."=CORE.'maintenance.php'))) include ".'$filename'.";
else echo '<h1>503 Service Temporarily Unavailable</h1>';";
			
			$entries[]='index';
			foreach($entries as $index){
				file_put_contents($fname=$dev->getPath().$index.'.php',$indexDevContent);
				chmod($fname,0755);
				file_put_contents($fname=$prod->getPath().$index.'.php',$indexProdContent);
				chmod($fname,0755);
			}
					
		//}
	}
}

class ConfigFolder{
	public function getFileEnhancerClassName(){
		return 'ConfigFile';
	}
	
	public function isFileExcluded($fileName){
		return $fileName !== 'modules.php';
	}
}
