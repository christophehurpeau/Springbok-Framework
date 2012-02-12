<?php
abstract class AFolderEnhancer{
	private $config,$dir,$devDir,$prodDir,$oldDef,$newDef;
	
	public function __construct($config,&$dir,&$devDir,&$prodDir,&$oldDef,&$newDef){
		$this->config=&$config; $this->dir=&$dir;$this->devDir=&$devDir;$this->prodDir=&$prodDir;
		$this->oldDef=&$oldDef;$this->newDef=&$newDef;
	}
	
	
	public static function registerFileEnhancers(){}
	public static function findEnhancer(&$filename,&$ext){
		foreach(static::$fileEnhancers as &$fileEnhancer){
			if(!((is_string($fileEnhancer['ext']) && $ext==$fileEnhancer['ext']) || (is_array($fileEnhancer['ext']) && in_array($ext,$fileEnhancer['ext'])))) continue;
			$justDev=$fileEnhancer['_justdev'] ? substr($filename,0,1)=='_' : false;
			$copy=$fileEnhancer['copy']?true:false;
			$destFilename=false;
			if($fileEnhancer['destExt']!==false && $fileEnhancer['destExt']!==$ext) $destFilename=substr($filename,0,-strlen($ext)).$fileEnhancer['destExt'];
			return array($fileEnhancer['class'],$justDev,$destFilename,$copy);
		}
		return false;
	}
	public static function registerEnhancer($class,$ext,$_justdev=false,$destExt=false,$copy=false){
		static::$fileEnhancers[]=array('class'=>&$class,'ext'=>&$ext,'_justdev'=>$_justdev,'destExt'=>$destExt,'copy'=>$copy);
	}
	
	
	public function process($class='PhpFile',$exclude=false){
		$dir=&$this->dir;$devDir=&$this->devDir;$prodDir=&$this->prodDir;
		
		if(substr($dir->getName(),0,1)==='.') return;
		$devFolder=new Folder($devDir,0775);
		$prodFolder=new Folder($prodDir,0775);
		
		$files=$dir->listFiles(false);
/*
		if($exclude!==true){
			foreach(array_diff_key($devFolder->listFiles(false),$files) as $f){
				if($exclude && in_array($f->getName(),$exclude)) continue;
				$f->delete();
				if($class !== 'PhpFile') $class::fileDeleted($f);
			}
			foreach(array_diff_key($prodFolder->listFiles(false),$files) as $f) if(!$exclude || !in_array($f->getName(),$exclude)) $f->delete();
		}*/

		foreach($files as $file){
			$filename=$file->getName();
			$ext=$file->getExt();
			
			$found=$this->findEnhancer($filename,$ext);
			if($found===false){
				$justDev=$destFilename=false;
				$copy=$ext!=='php';
			}else list($class,$justDev,$destFilename,$copy)=$found;
			if($destFilename===false) $destFilename=$filename;
			
			if($copy){
				$srcMD5=md5_file($file->getPath());
				
				if(!(file_exists($devDir.$destFilename) && file_exists($prodDir.$destFilename)
						&& isset($this->oldDef['files'][$file->getPath()])
						&& $this->oldDef['files'][$file->getPath()]==$srcMD5)){
					//debugVar('file changed :',$file->getPath(),file_exists($devDir.$filename),file_exists($prodDir.$filename),isset($this->oldDef['files'][$file->getPath()]),!isset($this->oldDef['files'][$file->getPath()])?null:$this->oldDef['files'][$file->getPath()]==$srcMD5);
					$this->newDef['changes']['all'][]=$file->getPath();
					copy($file->getPath(),$devDir.$destFilename);
					copy($file->getPath(),$prodDir.$destFilename);
					$this->newDef['enhancedFiles'][$file->getPath()]=array('class'=>false,'dev'=>$devDir.$destFilename,'prod'=>$prodDir.$destFilename);
				}
				$this->newDef['files'][$file->getPath()]=$srcMD5;
				continue;
			}
			
			
			
			
			/*
			if($ext==='css' || $ext==='sbcss'){
				if(substr($filename,0,1)=='_') $justDev=true;
				$class='CssFile';
				if($ext==='sbcss') $destFilename=substr($filename,0,-6).'.css';
			}elseif($ext==='scss'){
				if(substr($filename,0,1)=='_') $justDev=true;
				$class='ScssFile';
			}elseif(in_array($ext,array('jpg','jpeg','png','gif'))){
				if(substr($filename,0,1)=='_') $justDev=true;
				$class='ImgFile';
			}elseif($ext==='js'){
				if(substr($filename,0,1)=='_') $justDev=true;
				$class='JsFile';
			}elseif($ext!=='php'){
				
			}
			*/
			if($class==='ConfigFile' && ($filename==='enhance.php'||$filename==='_.php'||startsWith($filename,'routes-langs'))) continue;
			
			if($class==='ControllerFile'){
				if(($entrance=basename(dirname($file->getPath()))) != 'controllers') $key=$entrance.DS;
				else $key='';
				$this->controllers[$key][]=substr($filename,0,-4);
			}
			
			$nf=new $class($this->config,$file->getPath());
			$srcMD5=$nf->getMd5Content();
			$in=false;
			//$t=microtime(true);
			if(!(file_exists($devDir.$destFilename) && ($justDev || file_exists($prodDir.$destFilename))
					&& isset($this->oldDef['files'][$file->getPath()])
					&& $this->oldDef['files'][$file->getPath()]==$srcMD5)){
				//debugVar('file changed :',$file->getPath(),file_exists($devDir.$destFilename),file_exists($prodDir.$destFilename),isset($this->oldDef['files'][$file->getPath()]),!isset($this->oldDef['files'][$file->getPath()])?null:$this->oldDef['files'][$file->getPath()]==$srcMD5);
				$nf->processEhancing($devDir.$destFilename,$prodDir.$destFilename,$justDev);
				$this->newDef['changes']['all'][]=$file->getPath();
				$this->newDef['changes'][substr($class,0,-4)][]=$file->getPath();
				
				$this->newDef['enhancedFiles'][$file->getPath()]=array('class'=>$class,'dev'=>$devDir.$destFilename,'prod'=>$justDev?false:$prodDir.$destFilename);
			}
			$this->newDef['files'][$file->getPath()]=$srcMD5;
			/*$t=(microtime(true) - $t);
			if($t > 1) debugVar($file->getPath() .' : '.$t,$in,
				!file_exists($devDir.$destFilename) || !($justDev || file_exists($prodDir.$destFilename))
					|| !isset($this->oldDef['files'][$file->getPath()])
					||$this->oldDef['files'][$file->getPath()]!=$srcMD5,
					file_exists($devDir.$destFilename) && ($justDev || file_exists($prodDir.$destFilename)),
					isset($this->oldDef['files'][$file->getPath()]),
					isset($this->oldDef['files'][$file->getPath()]) && $this->oldDef['files'][$file->getPath()]==$srcMD5
			);*/
		}
	}
}