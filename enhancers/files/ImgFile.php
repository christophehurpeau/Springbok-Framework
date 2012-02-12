<?php
class ImgFile extends EnhancerFile{
	public function enhanceContent(){}
	
	public function getEnhancedDevContent(){}
	public function getEnhancedProdContent(){}
	
	private $_smallerTmpImgPath;
	public function writeDevFile($devFile){
		if(!empty(self::$APP_DIR) && !$this->isCore()){
			if(!file_exists($tmpFolder=self::$APP_DIR.'tmp/imgs/')) mkdir($tmpFolder,0755,true);
			$filename=$devFile->getName();
			$ext=$devFile->getExt();
			$this->_smallerTmpImgPath=$srcPath=$this->srcFile()->getPath();
			$minSize=$this->srcFile()->getSize();
			
			//pngcrush image.png -rem alla -reduce -brute result.png
			//mogrify -strip(ImageMagick)
			
			$optimizedImgPaths=array();
			
			if($ext==='png'){
				UExec::exec('pngcrush -rem alla -reduce -brute '.escapeshellarg($srcPath).' '.escapeshellarg($optimizedImgPaths[]=$tmpFolder.'pngcrush_'.$filename));
				copy($srcPath,$optimizedImgPaths[]=$tmpFolder.'optipng_'.$filename);
				UExec::exec('optipng -o7'.escapeshellarg($tmpFolder.'optipng_'.$filename));
			}elseif($ext==='jpg' || $ext==='jpeg'){
				copy($srcPath,$optimizedImgPaths[]=$tmpFolder.'jpegoptim_'.$filename);
				UExec::exec('jpegoptim --strip-all '.escapeshellarg($tmpFolder.'jpegoptim_'.$filename));
				UExec::exec('jpegtran -copy none -optimize -perfect '.escapeshellarg($srcPath).' > '.escapeshellarg($optimizedImgPaths[]=$tmpFolder.'jpegtran_'.$filename));
			}
			
			foreach($optimizedImgPaths as $oIp){
				if(($curSize=filesize($oIp)) < $minSize){
					$this->_smallerTmpImgPath=$oIp;
					$minSize=$curSize;
				}
			}
			//debugVar($filename,$this->_smallerTmpImgPath);
			
			copy($this->_smallerTmpImgPath,$devFile->getPath());
		}else $this->srcFile()->copyTo($devFile->getPath());
	}
	public function writeProdFile($prodFile){
		if(!empty(self::$APP_DIR) && !$this->isCore()){
			copy($this->_smallerTmpImgPath,$prodFile->getPath());
		}else $this->srcFile()->copyTo($prodFile->getPath());
	}
	
	public static function afterEnhanceApp($hasOldDef,&$newDef,&$appDir,&$dev,&$prod){
		CssFile::afterEnhanceApp($hasOldDef, $newDef, $appDir, $dev,$prod);
	}
	
	/*
	public static function optimizeImage($srcFile){
		
	}*/
}
