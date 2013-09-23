<?php
class FileUploadException extends Exception{}
/** Component for uploading files */
class CFiles{
	protected static $folderPrefix;
	
	/**
	 * Upload a file from a HTML form : $_FILES 
	 * 
	 * @param string
	 * @param SModel
	 * @throw Exception
	 * @return SModel
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
	
	/**
	 * Upload a file, detect if it is an image. If it is, store in an other model
	 * 
	 * @param string
	 * @param string class name of the image model
	 * @param SModel
	 */
	public static function uploadAndDetect($name='file',$classIfImage,$file=null){
		if(!empty($_FILES[$name]['name'])){
			$ext=UFile::extension($_FILES[$name]['name']);
			if(in_array($ext,$classIfImage::$imagesExtensions)) return $classIfImage::upload($name,$file);
		}
		return static::upload($name,$file);
	}
	
	/**
	 * Multiple uploads
	 */
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
	
	/**
	 * Return the folder where files are stored
	 * 
	 * @return string
	 */
	public static function folderPath(){
		return DATA.static::$folderPrefix.'files/';
	}
	/**
	 * Insert or update the model, move the temporary file.
	 * 
	 * @param string path of the file
	 * @param SModel model of the file
	 * @return int
	 */
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
	
	/**
	 * Remove a file
	 * 
	 * @param SModel
	 * @return void
	 */
	public static function deleteFile($file){
		$filename=static::folderPath().$file->id.'.'.$file->ext;
		UFile::rm($filename);
	}
}