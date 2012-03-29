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
			
			$logger=CLogger::get('opti_img');
			$logger->log('IMAGE : '.$filename);
			if($ext==='png'){
				//$t=microtime(true);
				//UExec::exec('optipng -o7 -force -full -quiet '.escapeshellarg($srcPath).' -out '.escapeshellarg($optimizedImgPaths[]=$tmpFolder.'optipng_1_'.$filename));
				UExec::exec('optipng -o7 -i0 -force -full -quiet '.escapeshellarg($srcPath).' -out '.escapeshellarg($optimizedImgPaths[]=$tmpFolder.'optipng_2_'.$filename));
				UExec::exec('optipng -o7 -i1 -force -full -quiet '.escapeshellarg($srcPath).' -out '.escapeshellarg($optimizedImgPaths[]=$tmpFolder.'optipng_3_'.$filename));
				//$logger->log('optipng : '.(microtime(true) - $t));
				//$t=microtime(true);
				UExec::exec('pngcrush -rem alla -reduce -brute '.escapeshellarg($srcPath).' '.escapeshellarg($optimizedImgPaths[]=$tmpFolder.'pngcrush_'.$filename));
				//$logger->log('pngcrush : '.(microtime(true) - $t));
			}elseif($ext==='jpg' || $ext==='jpeg'){
				//$t=microtime(true);
				UExec::exec('jpegtran -copy none -optimize -perfect '.escapeshellarg($srcPath).' > '.escapeshellarg($optimizedImgPaths[]=$tmpFolder.'jpegtran_'.$filename));
				//$logger->log('jpegtran : '.(microtime(true) - $t));
				/*$t=microtime(true);
				copy($srcPath,$optimizedImgPaths[]=$tmpFolder.'jpegoptim_'.$filename);
				UExec::exec('jpegoptim --strip-all '.escapeshellarg($tmpFolder.'jpegoptim_'.$filename));
				$logger->log('jpegoptim : '.(microtime(true) - $t));*/
			}
			
			foreach($optimizedImgPaths as $oIp){
				if(($curSize=filesize($oIp)) < $minSize){
					$this->_smallerTmpImgPath=$oIp;
					$minSize=$curSize;
				}
			}
			//debugVar($filename,$this->_smallerTmpImgPath);
			$logger->log('winner : '.$this->_smallerTmpImgPath);
			
			copy($this->_smallerTmpImgPath,$devFile->getPath());
		}else $this->srcFile()->copyTo($devFile->getPath());
	}
	public function writeProdFile($prodFile){
		if(!empty(self::$APP_DIR) && !$this->isCore()){
			copy($this->_smallerTmpImgPath,$prodFile->getPath());
		}else $this->srcFile()->copyTo($prodFile->getPath());
	}
	
	public static function afterEnhanceApp(&$enhanced,&$dev,&$prod){
		CssFile::afterEnhanceApp($enhanced,$dev,$prod);
	}
	
	/*
	public static function optimizeImage($srcFile){
		
	}*/
}
