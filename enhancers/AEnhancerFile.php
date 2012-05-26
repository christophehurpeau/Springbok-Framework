<?php
abstract class EnhancerFile{
	private $srcFile,$fileName,$_isCore,$_isProd,$_config,$_isInLibDir;
	public $currentDestFile;
	
	protected $enhanced,$_srcContent,$warnings,$errors;
	
	public function __construct(&$enhanced,$filename,$isCore=false,$isInLibDir=false){
		$this->srcFile=new File($filename);
		$this->enhanced=&$enhanced;
		$this->_isCore=$isCore; $this->fileName=$this->srcFile->getName();
		$this->_isInLibDir=$isInLibDir;
		$this->loadContent($this->srcFile->read());
	}
	
	protected function loadContent($srcContent){ $this->_srcContent=$srcContent; }
	
	public function getMd5Content(){
		return md5($this->_srcContent);
	}
	
	public function hasWarnings(){ return !empty($this->warnings); }
	public function hasErrors(){ return !empty($this->errors); }
	public function getWarnings(){ return $this->warnings; }
	public function getErrors(){ return $this->errors; }
	
	public function processEhancing($devFile,$prodFile,$justDev=null){
		//if($justDev===null) throw new Exception('just dev is deprecated');
		//$justDev=$this->isJustDev();
		if(is_string($devFile)) $devFile=new File($devFile);
		$this->currentDestFile=$devFile; $this->_isProd=false;
		
		$this->enhanceContent();
			
		//$t=microtime(true);
		$this->writeDevFile($devFile);
		if(!$justDev && $prodFile!==false){
			if(is_string($prodFile)) $prodFile=new File($prodFile);
			$this->currentDestFile=$prodFile; $this->_isProd=true;
			$this->writeProdFile($prodFile);
		}
		//$t=(microtime(true) - $t);
		//if($t > 1) debugVar('Write time : '.$this->srcFile->getPath() .' : '.$t);
	}
	
	protected function isJustDev(){return false;} 
	
	
	public abstract function enhanceContent();
	
	public function writeDevFile($devFile){
		$devFile->write($this->getEnhancedDevContent());
	}
	public function writeProdFile($prodFile){
		$prodFile->write($this->getEnhancedProdContent());
	}
	
	public abstract function getEnhancedDevContent();
	public abstract function getEnhancedProdContent();
	
	/* getters */
	
	public function srcFile(){
		return $this->srcFile;
	}
	
	protected function fileName(){
		return $this->fileName;
	}
	
	protected function isCore(){
		return $this->_isCore;
	}
	
	protected function isProd(){
		return $this->_isProd;
	}
	
	protected function isInLibDir(){
		return $this->_isInLibDir;
	}


	/* static */
	
	public static function removeWS_B_E($content){
		// remove WS at beginning of file
		$content=preg_replace('/^\s+/','',$content);
    	// remove WS and at end of file
		$content=preg_replace('/\s+$/','',$content);
		return $content;
	}

	public static function reset(){}
	public static function initFolder($folder,$config){}
	public static function afterEnhanceApp(&$enhanced,&$dev,&$prod){}
	public static function initEnhanceApp(){}
	public static function fileDeleted($file){}
}