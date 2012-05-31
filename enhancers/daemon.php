<?php
class DelayedEnhanceDaemon extends Daemon{
	public static function start($instanceName){
		class_exists("UFile");
		$baseApp=dirname(APP).'/';
		file_put_contents($baseApp.'block_delayedEnhanceDaemon','');
		$srcDir=$baseApp.'src/';
		$db=DB::init('_enhancedDelayed',array(
			'type'=>'SQLite',
			'file'=>$baseApp.'delayedEnhance.db',
			'flags'=>SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE
		));
		while(($row=$db->doSelectRow('SELECT * FROM files LIMIT 1'))){
			$db->doUpdate('DELETE FROM files WHERE path='.$db->escape($row['path']));
			$srcFile=new File($srcDir.$row['path']);
			$devFile=new File($baseApp.'dev/'.$row['path']);
			$prodFile=new File($baseApp.'prod/'.$row['path']);
			
			switch($row['type']){
				case 'Img':
					if(!file_exists($tmpFolder=$baseApp.'tmp/imgs/')) mkdir($tmpFolder,0755,true);
					
					$filename=$srcFile->getName();
					$ext=$srcFile->getExt();
					$smallerTmpImgPath=$srcPath=$srcFile->getPath();
					$minSize=$srcFile->getSize();
					
					//pngcrush image.png -rem alla -reduce -brute result.png
					//mogrify -strip(ImageMagick)
					
					$optimizedImgPaths=array();
					
					$logger=CLogger::get('opti_img');
					$logger->log('IMAGE: '.$srcPath.' '.$filename);
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
							$smallerTmpImgPath=$oIp;
							$minSize=$curSize;
						}
					}
					//debugVar($filename,$this->_smallerTmpImgPath);
					$logger->log('winner : '.$smallerTmpImgPath);
					
					copy($smallerTmpImgPath,$devFile->getPath());
					copy($smallerTmpImgPath,$prodFile->getPath());
					
					break;
			}
		}
		unlink($baseApp.'block_delayedEnhanceDaemon');
	}
	
	public static function _exit(){}
	public static function _restart(){}
}
