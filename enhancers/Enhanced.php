<?php
class Enhanced{
	const LATEST_CORE = 4;
	
	private $appDir,$type,$name,$logger;
	public $config,$oldDef=array(),$newDef=array(),$warnings=array(),$errors=array();
	
	public function __construct($type,&$dirname){
		$this->type=$type;
		if(!substr($dirname,-(strlen(DS))) != DS) $dirname.=DS;
		$this->appDir=$dirname;
		if(file_exists($configname=$dirname.'src/config/enhance.php'))
			$this->config=include $configname;
		else $this->config = array( 'config'=>array() );
	}
	
	public function getMinCoreVersion(){
		return self::LATEST_CORE;
	}
	
	public function initLogger(){
		$this->logger=$this->createLogger();
	}
	public function createLogger(){
		$logger=new stdClass;
		$logger->log=function($message){ display($message); };
		return $logger;
	}
	
	public function isApp(){return $this->type==='app';}
	public function isPlugin(){return $this->type==='plugin';}
	public function isCore(){return $this->type==='core';}
	public function isUnknown(){return $this->type==='?';}
	public function getName(){ return $this->name; }
	public function getLogger(){ return $this->logger; }

	public function setType($type,$name=''){ $this->type=$type; $this->name=$name; }
	/*
	public function getTmpDir(){
		return $this->getAppDir().'tmp/';
	}*/
	
	private $fileDef;
	public function loadFileDef($force){
		if(file_exists(($this->filedef=$this->appDir.'enhance_def.json'))){
			$this->oldDef=json_decode(file_get_contents($this->filedef),true);
			$coreDef=json_decode(file_get_contents(dirname(CORE).DS.'enhance_def.json'),true);
			if($force || empty($this->oldDef) || empty($coreDef['LAST_CHANGE_IN_ENHANCERS']) || $coreDef['LAST_CHANGE_IN_ENHANCERS'] > $this->oldDef['ENHANCED_TIME'])
				$this->oldDef=array();
			elseif($this->oldDef['ENHANCED_TIME']+4 > time()) return false;
		}//else debugVar('ENHANCE DEF DOES NOT EXISTS ! ('.$filedef.')');
		return true;
	}
	
	public function writeFileDef($force){
		if(!empty($this->newDef)){
			if(!$force) $this->newDef['CORE_VERSION']=Springbok::VERSION;
			$this->newDef['ENHANCED_TIME']=time();
			$this->newDef['ENHANCED_DATE']=date('Y-m-d H:i:s');
			file_put_contents($this->filedef,json_encode($this->newDef));
		}
	}
	
	
	public function initNewDefContent(){
		if(!empty($this->oldDef['enhancedFiles'])) $this->newDef['enhancedFiles']=$this->oldDef['enhancedFiles'];
	}
	public function isOldDefEmpty(){ return empty($this->oldDef); }
	
	public function hasOldEnhancedFiles(){ return !empty($this->oldDef['enhancedFiles']); }
	public function getOldEnhancedFiles(){ return $this->oldDef['enhancedFiles']; }
	public function removeOldEnhancedFile($enhancedFile){ unset($this->oldDef['enhancedFiles'][$enhancedFile]); }

	public function hasOldEnhancedFolders(){ return !empty($this->oldDef['enhancedFolders']); }
	public function getOldEnhancedFolders(){ return $this->oldDef['enhancedFolders']; }
	public function removeOldEnhancedFolder($enhancedFolder){ unset($this->oldDef['enhancedFolders'][$enhancedFolder]); }

	public function addDeleteChange($enhancedFile){ $this->newDef['changes']['deleted'][]=$enhancedFile; }
	
	public function getChanges(){ return empty($this->newDef['changes']) ? false : $this->newDef['changes']; }



	public function getAppDir(){ return $this->appDir; }
	public function hasOldDef(){ return !empty($this->oldDef); }
	public function hasChanges($type){ return !empty($this->newDef['changes'][$type]); }
	public function getConfig(){ return $this->config; }
	
	public function &config($attr){ return $this->config[$attr]; }
	public function configExist($attr){ return isset($this->config[$attr]); }
	public function configNotEmpty($attr){ return !empty($this->config[$attr]); }
	public function configEmpty($attr){ return empty($this->config[$attr]); }
	public function configSet($attr,$value){ return $this->config[$attr]=&$value; }
	public function configAdd($attr,$value){ return $this->config[$attr][]=&$value; }
	
	public function addWarning($file,$value){ $this->warnings[$file][]=$value; }
	public function addErrors($file,$value){ $this->errors[$file]=&$value; }
	
	public function hasWarnings(){ return !empty($this->warnings); }
	public function hasErrors(){ return !empty($this->errors); }
	public function getWarnings(){ return $this->warnings; }
	public function getErrors(){ return $this->errors; }
	
	public function md5EnhanceConfig(){ return ''; }
}
