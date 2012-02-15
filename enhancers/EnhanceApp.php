<?php
include_once __DIR__.DS.'AEnhance.php';
include_once __DIR__.DS.'DefaultFolderEnhancer.php';

class EnhanceApp extends AEnhance{
	public function init(){
		ConfigFile::$baseConfigArray=EnhancerFile::$APP_CONFIG=include $this->appDir.'src/config/_.php';
		EnhancerFile::$DEV_CONFIG=include $this->appDir.'src/config/_'.ENV.'.php';
		EnhancerFile::$APP_DIR=$this->appDir;
		
		foreach(array('project_name','projectName') as $attr){
			if(!isset(EnhancerFile::$APP_CONFIG[$attr])) throw new Exception('Missing attr config : '.$attr);
		}
	}
	
	public function initDev(&$dev){
		parent::initDev($dev);
		
		global $enhancers;
		foreach($enhancers as $className)
			$className::initFolder($dev,$this->config);
		
		$d=new Folder($dev->getPath().'logs',0777);
		$d=new Folder($dev->getPath().'tmp',0777);
	}
	
	public function initProd(&$prod){	
		parent::initProd($prod);
		
		global $enhancers;
		foreach($enhancers as $className)
			$className::initFolder($prod,$this->config);
		
		$d=new Folder($prod->getPath().'logs',0777);
		$d=new Folder($prod->getPath().'tmp',0777);
	}
	
	public function afterInit(&$dev,&$prod){
		$this->createIndexFile($dev,$prod);
	}
	
	
	private function recursiveCopyDir(&$srcDir,$dests,$recursiveMkdir=true){
		$dests=array_map(function(&$d) use(&$srcDir){return $d.$srcDir->getName().'/';},$dests);
		if(!file_exists($dests[0])) mkdir($dests[0],0775,$recursiveMkdir);
		if(!file_exists($dests[1])) mkdir($dests[1],0775,$recursiveMkdir);
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
			$dest.$srcFile->getName();
			if(!file_exists($dest) || $srcMd5 != md5_file($dest)){//debugVar(!file_exists($dest)/*,$srcMd5 != md5_file($dest)*/);
				$srcFile->copyTo($dest);
				$this->newDef['changes']['all'][]=$srcFile->getPath();
				//echo $dest.'('.$srcMd5.' - '.md5_file($dest).')'.'<br />';
			}
		}
	}
	
	public function afterEnhance(&$dev,&$prod){
		//if(!empty($this->config['includes'])){
		if(empty($this->config['includes'])) $this->config['includes']=array();
		$this->config['includes']['img'][]='ajax';
		$this->config['includes']['js'][]='ie-lt8.js';
			foreach($this->config['includes'] as $type=>$includes){
				if(is_string($includes)){ $includes=explode(',',$includes); $type=''; }
				else $type=$type.DS;
				foreach($includes as $filename){
					$srcFile=CORE.'includes/'.$type.$filename;
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
		if(!empty($this->config['plugins'])){
			foreach($this->config['plugins'] as &$plugin){
				$pluginPath=EnhancerFile::$DEV_CONFIG['pluginsPaths'][$plugin[0]].$plugin[1];
				if(!isset($plugin[2]))
				
					$this->recursiveDir($pluginPath.'/',new Folder($pluginPath), $dev->getPath(), $prod->getPath(),true,false,false);
			}
		}
		
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
	public function recursiveDir($srcDir,Folder $dir,$devDir,$prodDir,$exclude=false,$class=false){
		$dirs=$dir->listDirs(false);
		$devFolder=new Folder($devDir); $prodFolder=new Folder($prodDir);
		
		/*if($exclude!==true){
			foreach(array_diff_key($devFolder->listDirs(false),$dirs) as $d) if(!$exclude || !in_array($d->getName(),$exclude)) $d->delete();
			foreach(array_diff_key($prodFolder->listDirs(false),$dirs) as $d) if(!$exclude || !in_array($d->getName(),$exclude)) $d->delete();
		}*/
		
		$this->newDef['enhancedFolders'][$dir->getPath()]=array('dev'=>$devDir,'prod'=>$prodDir);
		
		$defaultClass=$class;
		foreach($dirs as $d){
			$dPath=$d->getPath();
			//$t=microtime(true);
			if(is_link($dPath)) continue;
			$dirname=$d->getName();
			if($dirname[0]==='.' || $dPath===$srcDir.'web/tinymce/') continue;
			
			
			if($dPath===$srcDir.'web/files/'){
				$this->recursiveCopyDir($d,array($devDir,$prodDir));
				continue;
			}
			
//			if($d->getPath()===$srcDir.'cache/' || $d->getPath()===$srcDir.'tmp/') continue;
			
			$newDevDir=$devDir.$dirname.DS; $newProdDir=$prodDir.$dirname.DS;
			$excludeFiles=false; $excludeChild=$exclude===true?true:false;
			
			if(startsWith($dPath,$srcDir.'logs/')||startsWith($dPath,$srcDir.'tmp/')) $excludeChild=$excludeFiles=true;
			if($defaultClass===false){
				$class='PhpFile';
				
				if($dPath===$srcDir.'config/'){ $class='ConfigFile'; $excludeFiles=array('modules.php');}
				elseif(startsWith($dPath,$srcDir.'controllers')){ $class='ControllerFile'; $excludeChild=array('methods'); }
				elseif($dPath===$srcDir.'jobs/') $class='JobFile';
				elseif($dPath===$srcDir.'daemons/') $class='DaemonFile';
				elseif($dPath===$srcDir.'models/'){ $class='ModelFile'; $excludeChild=array('infos'); }
				elseif($dPath===$srcDir.'modules/') $class='ModuleFile';
				elseif(startsWith($dPath,$srcDir.'views')) $class='ViewFile';
				elseif($dPath===$srcDir.'web/img/') $excludeFiles=array('img-sprite.png');
				elseif($dPath===$srcDir.'jsapp/') $class='UselessFile'; // ne concerne que les .php
				
				if($class !== 'PhpFile')
					$class::startEnhanceApp();
			}else $class=$defaultClass;
			
			$folderEnhancer=new DefaultFolderEnhancer($this->config,$d, $newDevDir,$newProdDir,$this->oldDef,$this->newDef);
			$folderEnhancer->process($class,$excludeFiles);
			
			$this->recursiveDir($srcDir,$d, $newDevDir,$newProdDir,$excludeChild,$class);
			
			if($class !== 'PhpFile'){
				$class::endEnhanceApp();
			}
			
			//debugVar($d->getPath() .' : '.(microtime(true) - $t));
		}
	}
	
	
	private function createIndexFile(&$dev,&$prod){
		$entrances=empty($this->config['entrances']) ? array() : $this->config['entrances'];

		$htaccess='<IfModule mod_rewrite.c>
	Options -Indexes
	DirectoryIndex disabled
	DirectorySlash Off
	RewriteEngine on
	RewriteRule ^web/(.*)$ web/$1 [NE,L]';

		foreach($entrances as $entrance)
			$htaccess.='
	RewriteRule ^'.$entrance.'/(.*)$ '.$entrance.'.php?url=$1 [QSA,NE,L]
	RewriteRule ^'.$entrance.'.php/(.*)$ '.$entrance.'.php?url=$1 [QSA,NE,L]';
	
	
		/*foreach($entrances as $entrance)
			$htaccess.='
	RewriteCond %{REQUEST_URI} !'.$entrance.'.php';*/
		$htaccess.='
	RewriteRule ^(.*)$ index.php?url=$1 [QSA,NE,NS,L]
 </IfModule>';
		file_put_contents($dev->getPath().'.htaccess',$htaccess);
		file_put_contents($prod->getPath().'.htaccess',$htaccess); // for real production => put that in apache conf
			
		//if(!empty($this->newDef['changes'])){
			$baseDev="<?php
define('DS', DIRECTORY_SEPARATOR);
define('CORE','".dirname(CORE).DS."dev".DS."');
define('APP', __DIR__.DS);";
			
			$indexDevContent=$baseDev."
include CORE.'app.php';";

			$indexProdContent="<?"."php
header('HTTP/1.1 503 Service Temporarily Unavailable',true,503);
define('DS', DIRECTORY_SEPARATOR);
define('CORE','".dirname(CORE).DS."prod".DS."');
define('APP', __DIR__.DS);
define('APP_DATE',".time()."); define('WEB_FOLDER','');
if(file_exists((".'$filename'."=CORE.'maintenance.php'))) include ".'$filename'.";
else echo '<h1>503 Service Temporarily Unavailable</h1>';";
			
			$entrances[]='index';
			foreach($entrances as $index){
				file_put_contents($dev->getPath().$index.'.php',$indexDevContent);
				file_put_contents($prod->getPath().$index.'.php',$indexProdContent);
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
