<?php
class FileUploadException extends Exception{}
class CFiles{
	protected static $folderPrefix;
	
	/**
	 * @throw Exception
	 */
	public static function upload($name='file',$file=null){
		$errorMessage=self::fileErrorMessage($_FILES[$name]['error']);
		if($errorMessage===true){
			$tmpFile=tempnam('/tmp','uploadFile');
			move_uploaded_file($_FILES[$name]['tmp_name'], $tmpFile);
			if($file===null) $file=static::createObject();
			static::_cleanName($file,$_FILES[$name]['name']);
			static::add($tmpFile,$file);
			return $file;
		}else throw new FileUploadException($errorMessage);
		return false;
	}
	
	public static function uploadAndDetect($name='file',$classIfImage,$file=null){
		if(!empty($_FILES[$name]['name'])){
			$ext=UFile::extension($_FILES[$name]['name']);
			if(in_array($ext,$classIfImage::$imagesExtensions)) return $classIfImage::upload($name,$file);
		}
		return static::upload($name,$file);
	}
	
	public static function uploadM($name){
		$files=$errors=array();
		foreach($_FILES[$name]['error'] as $key=>$error){
			$errorMessage=self::fileErrorMessage($error);
			if($errorMessage===true){
				$tmpFile=tempnam('/tmp','uploadFile');
				move_uploaded_file($_FILES[$name]['tmp_name'][$key], $tmpFile);
				$file=static::createObject();
				static::_cleanName($file,$_FILES[$name]['name'][$key]);
				try{
					static::add($tmpFile,$file);
					$files[]=$file;
				}catch(FileUploadException $ex){
					$errors[$_FILES[$name]['name'][$key]]=$ex->getMessage();
				}
			}else $errors[$_FILES[$name]['name'][$key]]=$errorMessage;
		}
		return array($files,$errors);
	}
	
	
	public static function _cleanName($file,$name){
		$file->name=trim($name);
	}

	private static function fileErrorMessage($error){
		if($error == UPLOAD_ERR_OK) return true;
		elseif($error===UPLOAD_ERR_INI_SIZE) return _tC('The uploaded file exceeds the maximum size allowed by the configuration.');
		elseif($error===UPLOAD_ERR_FORM_SIZE) return _tC('The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.');
		elseif($error===UPLOAD_ERR_PARTIAL) return _tC('The uploaded file was only partially uploaded.');
	}
	
	protected static function createObject(){
		throw new Exception('You must inherit CFiles class and override createObject');
	}
	
	
	public static function folderPath(){
		return DATA.static::$folderPrefix.'files/';
	}
	public static function add($tmpFile,$file){
		$file->ext=UFile::extension($file->name);
		
		if($file->_pkExists()){
			$id=$file->_getPkValue();
			$file->update();
		}else $id=$file->insert();
		
		$filename=static::folderPath().$id;
		rename($tmpFile,$fullFilename=($filename.'.'.$file->ext));
		chmod($fullFilename,0755);
		
		return $id;
	}
	
	
	public static function deleteFile($file){
		$filename=static::folderPath().$file->id.'.'.$file->ext;
		UFile::rm($filename);
	}
}