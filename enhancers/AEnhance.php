<?php
include __DIR__.DS.'AEnhancerFile.php';
include __DIR__.DS.'Enhanced.php';
if(!class_exists('UFile',false)) include CORE.'utils/UFile.php';
include CORE.'utils/UPhp.php';
include CORE.'utils/UInflector.php';
include CORE.'utils/UArray.php';

include __DIR__.'/files/Preprocessor.php';
global $enhancers;
$enhancers=array('PhpFile','ConfigFile','ControllerFile','JobFile','DaemonFile','ModelFile','ModuleFile','LocaleFile','ViewFile','MergeTxtFile',
		'ImgFile','CssFile','ScssFile','StylusFile','JsFile','UselessFile','JsAppFile');

foreach($enhancers as $enhancer) include __DIR__.'/files/'.$enhancer.'.php';


abstract class AEnhance{
	protected $enhanced;
	
	public function __construct($dirname){
		$this->enhanced=new Enhanced('?',$dirname);
	}
	
	// force=true if called from enhance.php
	public function process($force=false){
		global $enhancers;
		
		//$t=microtime(true);
		$blockEnhanceFile=$this->enhanced->getAppDir().'block_enhance';
		if(file_exists($blockEnhanceFile)){
			die('An enhancing is already in progress....');
		}
		//debug('wait took : '.(microtime(true) - $t).' s');
		
		if(!$this->enhanced->loadFileDef($force)) return false;

		file_put_contents($blockEnhanceFile,'');
		
		foreach($enhancers as $className)
			$className::reset();
		
		
		//$t=microtime(true);
		$this->init();
		//debug('init took : '.(microtime(true) - $t).' s');
		
		//$t=microtime(true);
		$this->initDev($dev);
		$this->initProd($prod);
		$this->afterInit($dev,$prod);
		$this->enhanced->initLogger();
		//debug('initDev,initProd,afterInit took : '.(microtime(true) - $t).' s');
		
		//$t=microtime(true);
		foreach($enhancers as $className)
			$className::initEnhanceApp();
		//debug('initEnhanceApp took : '.(microtime(true) - $t).' s');
		
		//$t=microtime(true);
		$this->removeOldFiles($dev,$prod);
		$this->removeOldFolders();
		//debug('removeOldFiles & removeOldFolders took : '.(microtime(true) - $t).' s');
		
		$this->enhanced->initNewDefContent();
		
		//$t=microtime(true);
		$this->recursiveDir($this->enhanced->getAppDir().'src/',new Folder($this->enhanced->getAppDir().'src'),$dev->getPath(),$prod->getPath());
		//debug('recursiveDir took : '.(microtime(true) - $t).' s');
		//debugVar($this->newDef);
		
		
		//$t=microtime(true);
		$this->afterEnhance($dev,$prod);
		//debug('afterEnhance took : '.(microtime(true) - $t).' s');
		
		//$t=microtime(true);
		foreach($enhancers as $className)
			$className::afterEnhanceApp($this->enhanced,$dev,$prod);
		//debug('afterEnhanceApp took : '.(microtime(true) - $t).' s');
		
		
		//$t=microtime(true);
		$this->enhanced->writeFileDef($force);
		//debug('export took : '.(microtime(true) - $t).' s');
		
		if(file_exists($blockEnhanceFile)) unlink($blockEnhanceFile);
		return $this->enhanced;
	}


	public function removeOldFiles(){
		if(!$this->enhanced->hasOldEnhancedFiles()) return;
		//$t=microtime(true);
		foreach($this->enhanced->getOldEnhancedFiles() as $enhancedFile=>$devAndProd){
			if(!file_exists($enhancedFile)){
				$this->enhanced->addDeleteChange($enhancedFile);
				if($devAndProd['dev'] && file_exists($devAndProd['dev'])) unlink($devAndProd['dev']);
				if($devAndProd['prod'] && file_exists($devAndProd['prod'])) unlink($devAndProd['prod']);
				if($devAndProd['class'] && $devAndProd['class'] !== 'PhpFile') $devAndProd['class']::fileDeleted(new File($enhancedFile),$devAndProd);
				$this->enhanced->removeOldEnhancedFile($enhancedFile);
			}
			/*foreach(array_diff_key($devFolder->listFiles(false),$files) as $f){
				if($exclude && in_array($f->getName(),$exclude)) continue;
				$f->delete();
				if($class !== 'PhpFile') $class::fileDeleted($f);
			}
			foreach(array_diff_key($prodFolder->listFiles(false),$files) as $f) if(!$exclude || !in_array($f->getName(),$exclude)) $f->delete();*/
		}
		//debug('remove old files took : '.(microtime(true) - $t).' ms');
	}

	public function removeOldFolders(){
		if(!$this->enhanced->hasOldEnhancedFolders()) return;
		foreach($this->enhanced->getOldEnhancedFolders() as $enhancedFolder=>$devAndProd){
			if(!file_exists($enhancedFolder)){
				$this->enhanced->addDeleteChange($enhancedFolder);
				if($devAndProd['dev'] && file_exists($devAndProd['dev'])) UExec::exec('cd / && rm -Rf '.escapeshellarg($devAndProd['dev']));
				if($devAndProd['prod'] && file_exists($devAndProd['prod'])) UExec::exec('cd / && rm -Rf '.escapeshellarg($devAndProd['prod']));
				$this->enhanced->removeOldEnhancedFolder($enhancedFolder);
			}
		}
	}
	
	public function init(){}
	public function initDev(&$dev){
		$dev=new Folder($this->enhanced->getAppDir().'dev');
		if($dev->exists() && $this->enhanced->isOldDefEmpty()) $dev->delete();
		$dev->mkdir(0775);
	}
	public function initProd(&$prod){
		$prod=new Folder($this->enhanced->getAppDir().'prod');
		if($prod->exists() && $this->enhanced->isOldDefEmpty()) $prod->delete();
		$prod->mkdir(0775);
	}
	public function afterInit(&$dev,&$prod){}
	public function afterEnhance(&$dev,&$prod){}
	
	
//	public abstract function addFolderEnhancers();
	
	
	public function onError(){
		if(file_exists(($filename=$this->enhanced->getAppDir().'enhance_def.json')))
			unlink($filename);
		if(file_exists(($filename=$this->enhanced->getAppDir().'block_enhance')))
			unlink($filename);
	}
	
	
	public function hasWarnings(){
		return $this->enhanced->hasWarnings();
	}
	
	public function getWarnings(){
		return $this->enhanced->getWarnings();
	}
}