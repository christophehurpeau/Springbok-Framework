<?php
class CImages{
	private static $_config;
	public static function init(){
		self::$_config=&Config::$images;
		if(!isset(self::$_config['thumbnails_background'])) self::$_config['thumbnails_background']=array(255,255,255);
	}
	
	/**
	 * @throw Exception
	 */
	public static function upload($name,$image=NULL,$toJpeg=true,$folderPrefix=''){
		$errorMessage=self::fileErrorMessage($_FILES[$name]['error']);
		if($errorMessage===true){
			$tmpFile=tempnam('/tmp','img');
			move_uploaded_file($_FILES[$name]['tmp_name'], $tmpFile);
			if($image===NULL) $image=static::createImage();
			$image->name=$_FILES[$name]['name'];
			self::add($tmpFile,$image,true,$folderPrefix);
			return $image;
		}else throw new Exception($errorMessage);
		return false;
	}
	
	public static function uploadM($name,$toJpeg=true,$folderPrefix=''){
		$images=$errors=array();
		foreach($_FILES[$name]['error'] as $key => $error){
			$errorMessage=self::fileErrorMessage($error);
			if($errorMessage===true){
				$tmpFile=tempnam('/tmp','img');
				move_uploaded_file($_FILES[$name]['tmp_name'][$key], $tmpFile);
				$image=static::createImage();
				$image->name=$_FILES[$name]['name'][$key];
				try{
					self::add($tmpFile,$image,$toJpeg,$folderPrefix);
					$images[]=$image;
				}catch(Exception $ex){
					$errors[$_FILES[$name]['name'][$key]]=$ex->getMessage();
				}
			}else $errors[$_FILES[$name]['name'][$key]]=$errorMessage;
		}
		return array($images,$errors);
	}

	private static function fileErrorMessage($error){
		if($error == UPLOAD_ERR_OK) return true;
		elseif($error===UPLOAD_ERR_INI_SIZE) return _tC('The uploaded file exceeds the maximum size allowed by the configuration.');
		elseif($error===UPLOAD_ERR_FORM_SIZE) return _tC('The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.');
		elseif($error===UPLOAD_ERR_PARTIAL) return _tC('The uploaded file was only partially uploaded.');
	}
	
	protected static function createImage(){
		return new Image();
	}
	
	
	public static function plupload($image=null,$toJpeg=true,$folderPrefix='',$result=null){
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
			$image->name=trim($_REQUEST['name']);
			if(in_array(strtolower(substr($image->name,-4)),array('.jpg','.png','.gif'))) $image->name=substr($image->name,0,-4);
			elseif(strtolower(substr($image->name,-5))==='.jpeg') $image->name=substr($image->name,0,-5);
			
			$idImage=self::add($targetDir.DS.$fileName,$image,$toJpeg,$folderPrefix);
			echo '{"jsonrpc" : "2.0", "result": '.($result===null?'null':$result($image)).', "id" :'.$idImage.'}';
		}else echo '{"jsonrpc" : "2.0", "result": null, "id": null}';
	}
	
	public static function importImage($url,$image,$toJpeg=true,$folderPrefix=''){
		$tmpfname = tempnam('/tmp','img');
		file_put_contents($tmpfname,file_get_contents($url));
		return self::add($tmpfname,$image,$toJpeg,$folderPrefix);
	}
	
	public static function add($tmpFile,&$image,$toJpeg=true,$folderPrefix=''){
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
		
		
		if($image->_pkExists()){
			$id=$image->_getPkValue();
			$image->update();
		}else $id=$image->insert();
		
		$filename=DATA.$folderPrefix.'images/'.$id;
		rename($tmpFile,$filename.$ext);	
		
		if($toJpeg && $type != IMAGETYPE_JPEG){
			switch ($type) {
				case IMAGETYPE_GIF: $rimage=imagecreatefromgif($filename.$ext); break;
				case IMAGETYPE_JPEG: $rimage=imagecreatefromjpeg($filename.$ext); break;
				case IMAGETYPE_PNG: $rimage=imagecreatefrompng($filename.$ext); break;
			}
			if(!imagejpeg($rimage,$filename.'.jpg')){
				$image->delete();
				return false;
			}
			unlink($filename.$ext);
		}
		
		self::generateThumbnails($id,null,$folderPrefix);
		
		return $id;
	}

	public static function generateThumbnails($filenameWithoutExt,$thumbnails=null,$folderPrefix=''){
		if($thumbnails===null) $thumbnails=self::$_config[$folderPrefix.'thumbnails'];
		if($thumbnails){
			$filenameWithoutExt=DATA.$folderPrefix.'images/'.$filenameWithoutExt;
			if(!($image_params = getimagesize($filenameWithoutExt.'.jpg')))
				throw new Exception(_tC('Invalid image'));
			list($width,$height)=$image_params;
			$rimage=imagecreatefromjpeg($filenameWithoutExt.'.jpg');
			foreach($thumbnails as $suffix=>$params){
				self::createThumbnail($rimage,$filenameWithoutExt.'-'.$suffix.'.jpg',$width,$height,$params['width'],$params['height']);
			}
			imagedestroy($rimage);
		}
	}

	public static function createThumbnail($rimage,$filename,$width,$height,$new_width,$new_height){
		$dst_x=$dst_y=0;
		if($width > $new_width || $height > $new_height){
			$originalRatio = floatval($width) / floatval($height);
			if ($new_width < 0 && $new_height > 0){
				$new_width=$adjusted_width= (int)($new_height * $originalRatio);
				$adjusted_height=$new_height;
			}elseif($new_width > 0 && $new_height < 0){
				$new_height=$adjusted_height= (int)($new_width / $originalRatio);
				$adjusted_width=$new_width;
			}else{
				$newRatio = floatval($new_width) / floatval($new_height);
				/*if($newRatio < $originalRatio){
					$new_height = intval($new_width / $originalRatio);
					//$dst_y=floor(($new_height-$good_new_height)/2);
				}else{
					$new_width = intval($new_height * $originalRatio);
					//$dst_x=floor(($new_height-$good_new_width)/2);
				}*/
				
				if($newRatio < $originalRatio){
					$adjusted_height = intval($new_width / $originalRatio);
					$adjusted_width=$new_width;
					$dst_y=floor(($new_height-$adjusted_height)/2);
				}else{
					$adjusted_width = intval($new_height * $originalRatio);
					$adjusted_height=$new_height;
					$dst_x=floor(($new_width-$adjusted_width)/2);
				}
			}
		}else{
			$adjusted_width=$width; $adjusted_height=$height;
			$dst_y=floor(($new_height-$height)/2);
			$dst_x=floor(($new_width-$width)/2);
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
	
	
	public static function deleteFiles($id,$folderPrefix=''){
		$filename=DATA.$folderPrefix.'images/'.$id;
		if(file_exists($cfilename=$filename.'.jpg')) unlink($cfilename);
		foreach(self::$_config['thumbnails'] as $suffix=>$params)
			if(file_exists($cfilename=$filename.'-'.$suffix.'.jpg')) unlink($cfilename);
	}
	
	
	public static function crop($srcFileName,$destFileName,$crop_width,$crop_height,$folderPrefix=''){
		$images_dir=DATA.$folderPrefix.'images/';
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
