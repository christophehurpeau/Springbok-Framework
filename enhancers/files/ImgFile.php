<?php
class ImgFile extends EnhancerFile{
	public function enhanceContent(){}
	
	public function getEnhancedDevContent(){}
	public function getEnhancedProdContent(){}
	
	private $_smallerTmpImgPath;
	public function writeDevFile($devFile){
		$filename=$this->fileName();
		if($filename[0]!=='_'){
			if($this->enhanced->getAppDir() && !$this->isCore())
				if($this->fileName() !== 'img-sprite.png')
					DelayedEnhance::get($this->enhanced)->add(substr($this->srcFile()->getPath(),strlen($this->enhanced->getAppDir().'src/')),'Img');
			$this->srcFile()->copyTo($devFile->getPath());
		}
	}
	public function writeProdFile($prodFile){
		$filename=$this->fileName();
		if($filename[0]!=='_')
			$this->srcFile()->copyTo($prodFile->getPath());
	}
	
	public static function afterEnhanceApp(&$enhanced,&$dev,&$prod){
		CssFile::afterEnhanceApp($enhanced,$dev,$prod);
	}
	
	/*
	public static function optimizeImage($srcFile){
		
	}*/
}