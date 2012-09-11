<?php
class StylusFile extends EnhancerFile{
	public static $CACHE_PATH='styl_8.0';
	
	public function enhanceContent(){
		//$this->_srcContent=$this->hardConfig($this->_srcContent);
	}

	public function getEnhancedDevContent(){}
	public function getEnhancedProdContent(){}
	
	public function writeDevFile($devFile){
		$this->callStylus($this->_srcContent,$devFile->getPath(),true);
		return true;
	}
	public function writeProdFile($prodFile){
		$this->getDevFile()->copyTo($prodFile->getPath());
		return true;
	}

	public function callStylus($content,$destination,$debug){
		$dest=$destination?$destination:tempnam($this->enhanced->getTmpDir(),'styldest');
		$res=shell_exec('cd / && echo '.escapeshellarg($content).' | stylus --include-css -c -I '.escapeshellarg(dirname($this->srcFile()->getPath()))
																		.' -I '.escapeshellarg(CORE.'includes/styl')
				.' | cleancss'.($destination?' > '.escapeshellarg($dest):''));
		if(!empty($res)){
			throw new Exception("Error in stylus conversion to css : ".$this->fileName()."\n".$res);
		}
		chmod($dest,0777);
		
		if(!$destination) return $res;
	}
}