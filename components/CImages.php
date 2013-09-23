<?php
/** Component for uploading image files */
class CImages extends CFiles{
	private static $_config;
	protected static $toJpeg=true,$imagesExtensions=array('jpg','png','gif','jpeg');
	
	/** @ignore */
	public static function init(){
		self::$_config=&Config::$images;
		if(!isset(self::$_config['thumbnails_background'])) self::$_config['thumbnails_background']=array(255,255,255);
	}
	
	/**
	 * @return SModel
	 */
	protected static function createImage(){
		return new Image();
	}
	/**
	 * @return SModel
	 */
	protected static function createObject(){
		return static::createImage();
	}
	
	public static function upload($name='image',$image=null){
		return parent::upload($name,$image);
	}
	
	/**
	 * @return string
	 */
	public static function folderPath(){
		return DATA.static::$folderPrefix.'images/';
	}
	
	public static function plupload($image=null,$result=null){
		Controller::noCache();
		$targetDir=DATA.'tmp/plupload/';
		set_time_limit(5 * 60);
		
		// Get parameters
		$chunk = isset($_REQUEST["chunk"]) ? $_REQUEST["chunk"] : 0;
		$chunks = isset($_REQUEST["chunks"]) ? $_REQUEST["chunks"] : 0;
		$fileName = isset($_REQUEST["name"]) ? $_REQUEST["name"] : '';
		
		// Clean the fileName for security reasons
		$fileName = preg_replace('/[^\w\._]+/', '', $fileName);
		
		// Create target dir
		if (!file_exists($targetDir)) mkdir($targetDir);
		
		
		// Make sure the fileName is unique but only if chunking is disabled
		if($chunks < 2 && file_exists($targetDir.DS.$fileName)){
			$ext = strrpos($fileName, '.');
			$fileName_a = substr($fileName, 0, $ext);
			$fileName_b = substr($fileName, $ext);
		
			$count = 1;
			while (file_exists($targetDir.DS.$fileName_a . '_' . $count . $fileName_b))
				$count++;
		
			$fileName = $fileName_a . '_' . $count . $fileName_b;
		}
		
		if(isset($_SERVER["CONTENT_TYPE"])) $contentType = $_SERVER["CONTENT_TYPE"];
		elseif(isset($_SERVER["HTTP_CONTENT_TYPE"])) $contentType = $_SERVER["HTTP_CONTENT_TYPE"];
		
		// Handle non multipart uploads older WebKit versions didn't support multipart in HTML5
		if (strpos($contentType, "multipart") !== false){
			if(isset($_FILES['file']['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name'])){
				// Open temp file
				$out=fopen($targetDir.DS.$fileName, $chunk == 0 ? "wb" : "ab");
				if($out){
					// Read binary input stream and append it to temp file
					$in=fopen($_FILES['file']['tmp_name'], "rb");
		
					if($in){
						while ($buff = fread($in, 4096)) fwrite($out, $buff);
					}else die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}}');
					
					fclose($in);
					fclose($out);
					unlink($_FILES['file']['tmp_name']);
				}else die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}}');
			}else die('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file."}}');
		}else{
			// Open temp file
			$out=fopen($targetDir.DS.$fileName, $chunk == 0 ? "wb" : "ab");
			if($out){
				// Read binary input stream and append it to temp file
				$in=fopen("php://input", "rb");
		
				if($in){
					while ($buff=fread($in, 4096)) fwrite($out, $buff);
				}else die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}}');
		
				fclose($in);
				fclose($out);
			}else die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}}');
		}
		
		// Return JSON-RPC response
		if($chunks==0 || $chunk+1==$chunks){
			if($image===NULL) $image=static::createImage();
			static::_cleanName($image,$_REQUEST['name']);
			
			$idImage=self::add($targetDir.DS.$fileName,$image);
			echo '{"jsonrpc" : "2.0", "result": '.($result===null?'null':$result($image)).', "id" :'.$idImage.'}';
		}else echo '{"jsonrpc" : "2.0", "result": null, "id": null}';
	}

	public static function _cleanName($image,$name){
		$image->name=trim($name);
		$liname=strtolower($image->name);
		foreach(static::$imagesExtensions as $ext){
			if(endsWith($liname,'.'.$ext)){
				$image->name=substr($image->name,0,-strlen($ext)-1);
				break;
			}
		}
	}
	
	public static function importImage($url,$image){
		$tmpfname = tempnam('/tmp','img');
		file_put_contents($tmpfname,file_get_contents($url));
		return self::add($tmpfname,$image);
	}
	
	public static function add($tmpFile,$image){
		if(!($image_params = getimagesize($tmpFile)))
			throw new Exception(_tC('Invalid image'));
		list($width,$height,$type)=$image_params;
		if(!in_array($type,array(IMAGETYPE_GIF,IMAGETYPE_JPEG,IMAGETYPE_PNG)))
			throw new Exception(_tC('Invalid extension'));
		
		switch($type){
			case IMAGETYPE_GIF: $ext='.gif'; break;
			case IMAGETYPE_JPEG: $ext='.jpg'; break;
			case IMAGETYPE_PNG: $ext='.png'; break;
		}
		
		// in case it's interesting
		$image->width=$width;
		$image->height=$height;
		$image->ext=substr($ext,1);
		
		if($image->_pkExists()){
			$id=$image->_getPkValue();
			$image->update();
		}else $id=$image->insert();
		
		$filename=static::folderPath().$id;
		rename($tmpFile,$filename.$ext);
		chmod($filename.$ext,0755);	
		
		if(self::$toJpeg && $type != IMAGETYPE_JPEG){
			switch ($type) {
				case IMAGETYPE_GIF: $rimage=imagecreatefromgif($filename.$ext); break;
				case IMAGETYPE_JPEG: $rimage=imagecreatefromjpeg($filename.$ext); break;
				case IMAGETYPE_PNG: $rimage=imagecreatefrompng($filename.$ext); break;
			}
			if(!imagejpeg($rimage,$filename.'.jpg')){
				$image->delete();
				return false;
			}
			//unlink($filename.$ext);
		}
		
		self::generateThumbnails($id,null);
		
		return $id;
	}

	public static function generateThumbnails($filenameWithoutExt,$thumbnails=null){
		if($thumbnails===null) $thumbnails=self::$_config[static::$folderPrefix.'thumbnails'];
		if($thumbnails){
			$filenameWithoutExt=static::folderPath().$filenameWithoutExt;
			if(!($image_params = getimagesize($filenameWithoutExt.'.jpg')))
				throw new Exception(_tC('Invalid image'));
			list($width,$height)=$image_params;
			$rimage=imagecreatefromjpeg($filenameWithoutExt.'.jpg');
			foreach($thumbnails as $suffix=>$params){
				/* TEST */
				$maxWidth=isset($params['maxWidth']) ? $params['maxWidth'] : -1;
				$maxHeight=isset($params['maxHeight']) ? $params['maxHeight'] :  -1;
				self::createThumbnail($rimage,$filenameWithoutExt.'-'.$suffix.'.jpg',$width,$height,$params['width'],$params['height'],$maxWidth,$maxHeight);
			}
			imagedestroy($rimage);
		}
	}

	public static function createThumbnail($rimage,$filename,$width,$height,$new_width,$new_height,$maxWidth=-1,$maxHeight=-1){
		$dst_x=$dst_y=0;
		if($maxWidth<1 && $maxHeight<1){
			if($width > $new_width || $height > $new_height){
				$originalRatio = (float)($width) / (float)($height);
				if ($new_width < 0 && $new_height > 0){
					$new_width=$adjusted_width= (int)($new_height * $originalRatio);
					$adjusted_height=$new_height;
				}elseif($new_width > 0 && $new_height < 0){
					$new_height=$adjusted_height= (int)($new_width / $originalRatio);
					$adjusted_width=$new_width;
				}else{
					$newRatio = (float)($new_width) / (float)($new_height);
					/*if($newRatio < $originalRatio){
						$new_height = intval($new_width / $originalRatio);
						//$dst_y=floor(($new_height-$good_new_height)/2);
					}else{
						$new_width = intval($new_height * $originalRatio);
						//$dst_x=floor(($new_height-$good_new_width)/2);
					}*/
					
					if($newRatio < $originalRatio){
						$adjusted_height = (int)($new_width / $originalRatio);
						$adjusted_width=$new_width;
						$dst_y=floor(($new_height-$adjusted_height)/2);
					}else{
						$adjusted_width = (int)($new_height * $originalRatio);
						$adjusted_height=$new_height;
						$dst_x=floor(($new_width-$adjusted_width)/2);
					}
				}
			}else{
				$adjusted_width=$width; $adjusted_height=$height;
				$dst_y=floor(($new_height-$height)/2);
				$dst_x=floor(($new_width-$width)/2);
			}
		}else{
			$originalRatio = (float)($width) / (float)($height);
			if($maxHeight<=0 && $width > $maxWidth){
				$new_width=$adjusted_width=$maxWidth;
				$new_height=$adjusted_height= (int)($new_width / $originalRatio) ;
			}elseif($maxWidth<=0 && $height > $maxHeight){
				$new_width=$adjusted_width=(int)($new_height * $originalRatio);
				$new_height=$adjusted_height= $maxHeight ;
			}elseif($width > $maxWidth || $height > $maxHeight){
				$maxContainerRatio = (float)($maxWidth) / (float)($maxHeight);
			   
				if($maxContainerRatio < $originalRatio){
					$new_height=$adjusted_height = (int)($maxWidth / $originalRatio);
					$new_width=$adjusted_width=$maxWidth;
				}else{
					$new_width=$adjusted_width = (int)($maxHeight * $originalRatio);
					$new_height=$adjusted_height= $maxHeight ;
				}
			   
			}
			else{
				$new_width=$adjusted_width=$width;
				$new_height=$adjusted_height= $height ;
			}
		}
		if(!($tmp = imagecreatetruecolor($new_width,$new_height))) return false;
		if($adjusted_width<$new_width || $adjusted_height<$new_height){
			$b=&self::$_config['thumbnails_background'];
			imagefill($tmp,0,0,imagecolorallocate($tmp,$b[0],$b[1],$b[2]));
		}
		if(!imagecopyresampled($tmp,$rimage,$dst_x,$dst_y,0,0, $adjusted_width, $adjusted_height, $width, $height)) return false;
		if(!($new_image = imagejpeg($tmp,$filename,100))) return false;
		imagedestroy($tmp);
		return $new_image;
	}
	
	
	public static function deleteFiles($id){
		$filename=static::folderPath().$id;
		UFile::rm($filename.'.jpg');
		foreach(self::$_config[static::$folderPrefix.'thumbnails'] as $suffix=>$params)
			UFile::rm($filename.'-'.$suffix.'.jpg');
	}
	
	
	public static function crop($srcFileName,$destFileName,$crop_width,$crop_height){
		$images_dir=static::folderPath();
		return self::cropFile($images_dir.$srcFileName,$images_dir.$destFileName,$crop_width,$crop_height);
	}
	
	/* http://www.toknowmore.net/e/1/php/crop-images-with-php.php */
	public static function cropFile($src,$dest,$crop_width,$crop_height){
		if(!($image_params = getimagesize($src)))
			throw new Exception(_tC('Invalid image'));
		list($original_width,$original_height)=$image_params;
		$rimage=imagecreatefromjpeg($src);
		
		$dst_x=$dst_y=0;
		$adjusted_width=$crop_width;$adjusted_height=$crop_height;
		if($original_width > $original_height)
			$dst_x=- ((($adjusted_width=($original_width / ($original_height / $crop_height))) / 2) - ($crop_width/2));
		elseif(($original_width < $original_height ) || ($original_width == $original_height ))
			$dst_y=-((($adjusted_height=($original_height / ($original_width / $crop_width))) / 2) - ($crop_height/2));
		
		if(!($tmp = imagecreatetruecolor($crop_width,$crop_height))) return false;
		if(!imagecopyresampled($tmp,$rimage, $dst_x,$dst_y,0,0,$adjusted_width,$adjusted_height,$original_width,$original_height)) return false;
		if(!($new_image = imagejpeg($tmp,$dest,100))) return false;
		imagedestroy($tmp);
		return $new_image;
	}
	
	public static function convertSvg2Png($path,$newPath){
		$im = new Imagick($path);
		$im->setImageFormat("png32");
		$im->writeImage($newPath);
		$im->clear();
		$im->destroy();
	}
	public static function convert2Jpeg($path,$newPath){
		$im = new Imagick($path);
		$im->setImageFormat("jpeg");
		$im->writeImage($newPath);
		$im->clear();
		$im->destroy();
	}
	
	
	public static function addLogo(){
// On charge d'abord les images
$source = imagecreatefrompng("logo.png"); // Le logo est la source
$destination = imagecreatefromjpeg("couchersoleil.jpg"); // La photo est la destination

// Les fonctions imagesx et imagesy renvoient la largeur et la hauteur d'une image
$largeur_source = imagesx($source);
$hauteur_source = imagesy($source);
$largeur_destination = imagesx($destination);
$hauteur_destination = imagesy($destination);

// On veut placer le logo en bas à droite, on calcule les coordonnées où on doit placer le logo sur la photo
$destination_x = $largeur_destination - $largeur_source;
$destination_y =  $hauteur_destination - $hauteur_source;

// On met le logo (source) dans l'image de destination (la photo)
imagecopymerge($destination, $source, $destination_x, $destination_y, 0, 0, $largeur_source, $hauteur_source, 60);

// On affiche l'image de destination qui a été fusionnée avec le logo
imagejpeg($destination);
	}
}
CImages::init();
