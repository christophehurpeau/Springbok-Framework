<?php
abstract class EnhancerFile{
	private $srcFile,$fileName,$_isCore,$_isProd,$_config,$_isInLibDir,$_devFile;
	public $currentDestFile;
	
	/**
	 * @var AEnhance
	 */
	protected $enhanced;
	protected $_srcContent,$errors,$md5;
	
	protected static $preprocessor;
	
	public function __construct($enhanced,$filename,$isCore=false,$isInLibDir=false){
		$this->srcFile=new File($filename);
		$this->enhanced=$enhanced;
		$this->_isCore=$isCore; $this->fileName=$this->srcFile->getName();
		$this->_isInLibDir=$isInLibDir;
		$this->read();
	}
	
	protected function read(){ $this->loadContent($this->srcFile->read()); }
	
	protected function loadContent($srcContent){ $this->_srcContent=$srcContent; }
	
	public function getMd5Content(){
		return $this->md5=md5($this->_srcContent);
	}
	
	public function hasErrors(){ return !empty($this->errors); }
	public function getErrors(){ return $this->errors; }
	
	public function addWarning($warning){
		$this->enhanced->addWarning($this->srcFile->getPath(),$warning);
	}
	
	public function processEhancing($devFile,$prodFile,$justDev=null){
		if(is_string($devFile)) $devFile=new File($devFile);
		
		//echo($devFile->getPath().' ==> '.md5($devFile->getPath()).'_'.$this->md5.'<br/>'); ob_flush();
		if(($cacheActive=(!$this->enhanced->isCore() && static::$CACHE_PATH!==false))
					 && file_exists(($cachefile=$this->enhanced->getTmpDir().static::$CACHE_PATH.'/'.md5($this->srcFile->getPath()).'_'.$this->md5).'_dev')){
			$this->copyFromCache($cachefile,$devFile,$prodFile,$justDev);
		}else{
			$this->_devFile=$this->currentDestFile=$devFile; $this->_isProd=false;
			
			$this->enhanceContent();
		
			//$t=microtime(true);
			if($this->writeDevFile($devFile)!==false && $cacheActive) $this->copyDevToCache($devFile,$cachefile);
			if(!$justDev && $prodFile!==false){
				if(is_string($prodFile)) $prodFile=new File($prodFile);
				$this->currentDestFile=$prodFile; $this->_isProd=true;
				if($this->writeProdFile($prodFile) !==false && $cacheActive) copy($prodFile->getPath(),$cachefile.'_prod');
			}
		}
	}
	
	protected function copyFromCache($cachefile,$devFile,$prodFile,$justDev){
		//echo('copy : '.$cachefile.'<br/>');ob_flush();
		copy($cachefile.'_dev',$devFile->getPath());
		if(!$justDev && $prodFile!==false) copy($cachefile.'_prod',is_string($prodFile) ? $prodFile : $prodFile->getPath());
	}
	
	protected function copyDevToCache($devFile,$cachefile){
		copy($devFile->getPath(),$cachefile.'_dev');
	}
	
	protected function isJustDev(){return false;} 
	protected function getDevFile(){return $this->_devFile;} 
	
	
	public abstract function enhanceContent();
	
	public function writeDevFile($devFile){
		$content=$this->getEnhancedDevContent();
		$this->checkContent($content);
		if($content!==false)
			if(!file_exists($devFile->getPath()) || md5_file($devFile->getPath())!=md5($content))
				$devFile->write($content);
		return true;
	}
	public function writeProdFile($prodFile){
		$content=$this->getEnhancedProdContent();
		$this->checkContent($content);
		if($content!==false)
			if(!file_exists($prodFile->getPath()) || md5_file($prodFile->getPath())!=md5($content))
				$prodFile->write($content);
		return true;
	}
	
	public abstract function getEnhancedDevContent();
	public abstract function getEnhancedProdContent();
	public function checkContent($content){}
	/*
	public function hardConfig($content){
		$enhanced=$this->enhanced;
		$content=preg_replace_callback('#/\*\s+IF\(([A-Za-z0-9_\-\.]+)\)\s+\*\\\\?/\s*(.*)\s*\\\\?/\*\s+/IF\s+\*\\\\?/#Us',function($m) use($enhanced){
			return $enhanced->config['config'][$m[1]] ? $m[2] : '';
		},$content);
		$content=preg_replace_callback('#/\*\s+IF2\(([A-Za-z0-9_\-\.]+)\)\s+\*\\\\?/\s*(.*)\s*\\\\?/\*\s+/IF2\s+\*\\\\?/#Us',function($m) use($enhanced){
			return $enhanced->config['config'][$m[1]] ? $m[2] : '';
		},$content);
		$content=preg_replace_callback('#/\*\s+IF\!\(([A-Za-z0-9_\-\.]+)\)\s+\*\\\\?/\s*(.*)\s*\\\\?/\*\s+/IF\s+\*\\\\?/#Us',function($m) use($enhanced){
			return !$enhanced->config['config'][$m[1]] ? $m[2] : '';
		},$content);
		$content=preg_replace_callback('#/\*\s+IF2\!\(([A-Za-z0-9_\-\.]+)\)\s+\*\\\\?/\s*(.*)\s*\\\\?/\*\s+/IF2\s+\*\\\\?/#Us',function($m) use($enhanced){
			return !$enhanced->config['config'][$m[1]] ? $m[2] : '';
		},$content);
		$content=preg_replace_callback('#/\*\s+VALUE\(([A-Za-z0-9_\-\.]+)\)\s+\*\\\\?/#Us',function($m) use($enhanced){
			return $enhanced->config['config'][$m[1]];
		},$content);
		return $content;
	}*/

	public function preprocessor($data,$isBrowser=false){
		if(preg_match('#/\*\s+(IF2?\!?|VALUE|HIDE|EVAL|RM|HIDE|REMOVE|NONE|NODE|BROWSER)\(#',$data,$m)) // ! NONE != NODE
			$this->throwException('Use the new Preprocessor now (found '.$m[1].')');
		try{
			return static::$preprocessor->process($this->enhanced->config['config'],$data,$isBrowser,null,array('DEV'=>true,'PROD'=>true));
		}catch(Exception $e){
			$this->throwException($e->getMessage());
		}
	}
	public function preprocessor_devprod($data,$isDev){
		try{
			return static::$preprocessor->process(array('DEV'=>!!$isDev,'PROD'=>!$isDev),$data,null);
		}catch(Exception $e){
			$this->throwException($e->getMessage());
		}
	}
	
	
	public function throwException($message){
		throw new Exception('Enhancing '.(function_exists('replaceAppAndCoreInFile')?replaceAppAndCoreInFile($this->srcFile->getPath()):$this->srcFile->getPath())." :\n".$message);
	}
	
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