<?php
include __DIR__.DS.'AEnhancerFile.php';
include CORE.'utils/UFile.php';
include CORE.'utils/UPhp.php';
include CORE.'utils/UInflector.php';
include CORE.'utils/UArray.php';

global $enhancers;
$enhancers=array('PhpFile','ConfigFile','ControllerFile','JobFile','DaemonFile','ModelFile','ModuleFile','ViewFile','ImgFile','CssFile','JsFile','UselessFile','JsAppFile');

foreach($enhancers as $enhancer) include __DIR__.'/files/'.$enhancer.'.php';


abstract class AEnhance{
	/** @var Dir */
	protected $appDir,$oldDef=array(),$newDef=array(),$config,$controllers,$controllersDeleted;
	
	public function __construct($dirname){
		if(!substr($dirname,-(strlen(DS))) != DS) $dirname.=DS;
		$this->appDir=&$dirname;
		if(file_exists($configname=$dirname.'src/config/enhance.php'))
			$this->config=include $configname;
	}

	// force=true if called from enhance.php
	public function process($force=false){
		global $enhancers;
		
		//$t=microtime(true);
		$tmpDev=$this->appDir.'tmp_dev'.DS;
		if(file_exists($tmpDev)){
			usleep(100);
			if(file_exists($tmpDev)){
				usleep(100);
				if(file_exists($tmpDev)){
					usleep(500);
					if(file_exists($tmpDev)) die('tmp_dev already exists');
				}
			}
		}
		//debug('wait took : '.(microtime(true) - $t).' s');
		
		if(file_exists(($filedef=$this->appDir.'enhance_def.json'))){
			$this->oldDef=json_decode(file_get_contents($filedef),true);
			$coreDef=json_decode(file_get_contents(dirname(CORE).DS.'enhance_def.json'),true);
			if($force || empty($this->oldDef) || empty($coreDef['LAST_CHANGE_IN_ENHANCERS']) || $coreDef['LAST_CHANGE_IN_ENHANCERS'] > $this->oldDef['ENHANCED_TIME']) $this->oldDef=array();
			elseif($this->oldDef['ENHANCED_TIME']+4 > time()) return false;
		}//else debugVar('ENHANCE DEF DOES NOT EXISTS ! ('.$filedef.')');

		mkdir($tmpDev,0700);
		
		foreach($enhancers as $className)
			$className::reset();
		
		
		//$t=microtime(true);
		$this->init();
		//debug('init took : '.(microtime(true) - $t).' s');
		
		//$t=microtime(true);
		$this->initDev($dev);
		$this->initProd($prod);
		$this->afterInit($dev,$prod);
		//debug('initDev,initProd,afterInit took : '.(microtime(true) - $t).' s');
		
		//$t=microtime(true);
		foreach($enhancers as $className)
			$className::initEnhanceApp();
		//debug('initEnhanceApp took : '.(microtime(true) - $t).' s');
		
		//$t=microtime(true);
		$this->removeOldFiles();
		$this->removeOldFolders();
		//debug('removeOldFiles & removeOldFolders took : '.(microtime(true) - $t).' s');
		
		if(!empty($this->oldDef['enhancedFiles'])) $this->newDef['enhancedFiles']=$this->oldDef['enhancedFiles'];
		
		//$t=microtime(true);
		$this->recursiveDir($this->appDir.'src/',new Folder($this->appDir.'src'), $dev->getPath(), $prod->getPath(),array('logs','tmp'));
		//debug('recursiveDir took : '.(microtime(true) - $t).' s');
		//debugVar($this->newDef);
		
		
		//$t=microtime(true);
		$this->afterEnhance($dev,$prod);
		//debug('afterEnhance took : '.(microtime(true) - $t).' s');
		
		//$t=microtime(true);
		foreach($enhancers as $className)
			$className::afterEnhanceApp(!empty($this->oldDef),$this->newDef,$this->appDir,$dev,$prod);
		//debug('afterEnhanceApp took : '.(microtime(true) - $t).' s');
		
		
		//$t=microtime(true);
		if(!empty($this->newDef)){
			if(!$force) $this->newDef['CORE_VERSION']=Springbok::VERSION;
			$this->newDef['ENHANCED_TIME']=time();
			$this->newDef['ENHANCED_DATE']=date('Y-m-d H:i:s');
			file_put_contents($filedef,json_encode($this->newDef));
		}
		//debug('export took : '.(microtime(true) - $t).' s');
		
		$f=new Folder($tmpDev); if($f->exists()) $f->delete();
		return empty($this->newDef['changes']) ? false : $this->newDef['changes'];
	}


	public function removeOldFiles(){
		if(empty($this->oldDef['enhancedFiles'])) return;
		//$t=microtime(true);
		foreach($this->oldDef['enhancedFiles'] as $enhancedFile=>$devAndProd){
			if(!file_exists($enhancedFile)){
				$this->newDef['changes']['deleted'][]=$enhancedFile;
				if($devAndProd['dev'] && file_exists($devAndProd['dev'])) unlink($devAndProd['dev']);
				if($devAndProd['prod'] && file_exists($devAndProd['prod'])) unlink($devAndProd['prod']);
				if($devAndProd['class'] && $devAndProd['class'] !== 'PhpFile') $devAndProd['class']::fileDeleted(new File($enhancedFile));
				unset($this->oldDef['enhancedFiles'][$enhancedFile]);
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
		if(empty($this->oldDef['enhancedFolders'])) return;
		foreach($this->oldDef['enhancedFolders'] as $enhancedFolder=>$devAndProd){
			if(!file_exists($enhancedFolder)){
				$this->newDef['changes']['deleted'][]=$enhancedFolder;
				if($devAndProd['dev'] && file_exists($devAndProd['dev'])) UExec::exec('rm -Rf '.escapeshellarg($devAndProd['dev']));
				if($devAndProd['prod'] && file_exists($devAndProd['prod'])) UExec::exec('rm -Rf '.escapeshellarg($devAndProd['prod']));
				unset($this->oldDef['enhancedFolders'][$enhancedFolder]);
			}
		}
	}
	
	public function init(){}
	public function initDev(&$dev){
		$dev=new Folder($this->appDir.'dev');
		if($dev->exists() && empty($this->oldDef)) $dev->delete();
		$dev->mkdir(0775);
	}
	public function initProd(&$prod){
		$prod=new Folder($this->appDir.'prod');
		if($prod->exists() && empty($this->oldDef)) $prod->delete();
		$prod->mkdir(0775);
	}
	public function afterInit(&$dev,&$prod){}
	public function afterEnhance(&$dev,&$prod){}
	
	
//	public abstract function addFolderEnhancers();
	
	
	public function onError(){
		if(file_exists(($filename=$this->appDir.'enhance_def.php')))
			unlink($filename);
		if(file_exists(($filename=$this->appDir.'tmp_dev'.DS)))
			{$f=new Folder($filename); $f->delete();}
	}
	
	
	
}