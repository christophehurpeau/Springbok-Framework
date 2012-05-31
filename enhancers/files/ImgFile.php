<?php
class ImgFile extends EnhancerFile{
	public function enhanceContent(){}
	
	public function getEnhancedDevContent(){}
	public function getEnhancedProdContent(){}
	
	private $_smallerTmpImgPath;
	public function writeDevFile($devFile){
		if($this->enhanced->getAppDir() && !$this->isCore())
			DelayedEnhance::get($this->enhanced)->add(substr($this->srcFile()->getPath(),strlen($this->enhanced->getAppDir().'src/')),'Img');
		$this->srcFile()->copyTo($devFile->getPath());
	}
	public function writeProdFile($prodFile){
		$this->srcFile()->copyTo($prodFile->getPath());
	}
	
	public static function afterEnhanceApp(&$enhanced,&$dev,&$prod){
		CssFile::afterEnhanceApp($enhanced,$dev,$prod);
	}
	
	/*
	public static function optimizeImage($srcFile){
		
	}*/
}