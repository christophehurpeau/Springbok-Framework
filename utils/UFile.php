<?php
class UFile{
	public static function getContents($path){
		try{
			return file_get_contents($path);
		}catch(ErrorException $e){}
		return false;
	}
	
	public static function rm($path){
		try{
			return unlink($path);
		}catch(ErrorException $e){}
		return false;
	}
	
	public static function open($path,$mode='r'){
		return new UFileOpened($path,$mode);
	}
	
	public static function readWithLock($path,$mode='rb'){
		$file=self::open($path,$mode);
		if(false===$file->lockShared()){ $file->close(); return false; }
		$data=$file->read();
		$file->unlock();
		$file->close();
		return $data;
	}
	
	public static function writeWithLock($path,$data/*,$mode='wb'*/){
		/*$file=self::open($path,$mode);
		if(false===$file->lockExclusive()) return false;
		$data=$file->write($data);
		$file->unlock();
		$file->close();
		return true;*/
		return file_put_contents($path,$data,LOCK_EX);
	}
}

class UFileOpened{
	private $_path,$_file;

	public function __construct($path,$mode){
		$this->_file=fopen($this->_path=$path,$mode);
	}
	
	public function close(){
		try{
			return fclose($this->_file);
		}catch(ErrorException $e){}
	}
		
	public function write($string){
		return fwrite($this->_file,$string);
	}
	
	public function read(){
		//if(($filesize=filesize($this->_path)) > 0) return fread($this->_file,$filesize);
		//return null;
		return stream_get_contents($this->_file);
	}
	
	public function lockShared(){
		return flock($this->_file,LOCK_SH);
	}
	
	public function lockExclusive(){
		return flock($this->_file,LOCK_SH);
	}

	public function unlock(){
		return flock($this->_file,LOCK_UN);
	}



	public function log($message=''){
		return fwrite($this->_file,date('m-d H:i:s')."\t".$message."\n");
	}


}

abstract class AFile{
	protected $name;
	
	public function __construct($file){
		$this->name=$file;
	}
	
	private $_name;
	public function getName(){
		if($this->_name===null) $this->_name=basename($this->name);
		return $this->_name;
	}
	
	public function getPath(){
		return $this->name;
	}
	
	public abstract function delete();
	
	public function exists(){
		return file_exists($this->name);
	}
	
	public abstract function copyTo($dest);
	public abstract function moveTo($dest);
	
	public function isDir(){
		return is_dir($this->name);
	}
	
	public function isFile(){
		return is_file($this->name);
	}
}


class Folder extends AFile{
	public function __construct($dirname,$create=false){
		if(substr($dirname,-(strlen(DS))) != DS) $dirname.=DS;
		parent::__construct($dirname);
		/* DEV */
		if($this->isFile()) throw new UnexpectedValueException($dirname.' is a file');
		/* /DEV */
		if($create) $this->mkdir($create===true?0770:$create);
	}
	
	public function mkdir($chmod=0770){
		return $this->exists() ? true : mkdir($this->name,$chmod);
	}
	
	public function mkdirs($chmod=0770){
		return $this->exists() ? true : mkdir($this->name,$chmod,true);
	}

	public function delete(){//if(function_exists('debugVar')) debugVar('delete : '.$this->name.'<br />');
		if(!$this->exists()) return true;
		//foreach($this->listAll() as $file) $file->delete();
		//return rmdir($this->name);
		$res=UExec::exec('cd / && rm -Rf '.escapeshellarg($this->name));
		if($res) die($res);
	}
	
	public function copyTo($dest,$chmod=0770){
		$f=new Folder($dest); $f->mkdirs($chmod);
		/*foreach($this->listAll() as $file) $file->copyTo($f->getPath().$file->getName());*/
		$res=UExec::exec('cp -R '.escapeshellarg($this->name).' '.escapeshellarg($dest));
		if($res) die($res);
	}
	public function moveTo($dest,$chmod=0770){
		$f=new Folder($dest); $f->mkdirs($chmod);
		/*foreach($this->listAll() as $file) $file->copyTo($f->getPath().$file->getName());*/
		$res=UExec::exec('mv '.escapeshellarg($this->name).' '.escapeshellarg($dest));
		if($res) die($res);
	}
	
	/** @return array[]AFile */
	public function listAll(){
		/*foreach (new DirectoryIterator('../moodle') as $fileInfo) {
		if($fileInfo->isDot()) continue;
	    echo $fileInfo->getFilename() . "<br>\n";
		}*/
		if($dir=opendir($this->name)){
			$files=array();
			while (false !== ($file = readdir($dir)))
				if($file !== '.' && $file !== '..') $files[$filename=$this->name.$file]=is_dir($filename) ? new Folder($filename) : new File($filename);
			closedir($dir);
			ksort($files);
			return $files;
		}
		return false;
	}
	
	/** @return array[]File */
	public function listFiles($completepath=true){
		if($dir=opendir($this->name)){
			$files=array();
			while (false !== ($file = readdir($dir)))
				if($file !== '.' && $file !== '..' && !is_dir($filename=$this->name.$file)) $files[$completepath?$filename:$file]=new File($filename);
			closedir($dir);
			ksort($files);
			return $files;
		}
		return false;
	}
	
	/** @return array[]Dir */
	public function listDirs($completepath=true){
		if($dir=opendir($this->name)){
			$dirs=array();
			while (false !== ($file = readdir($dir)))
				if($file !== '.' && $file !== '..' && is_dir($filename=$this->name.$file)) $dirs[$completepath?$filename:$file]=new Folder($filename);
			closedir($dir);
			ksort($dirs);
			return $dirs;
		}
		return false;
	}
	
	public function listFilesPath(){
		if($dir=opendir($this->name)){
			$files=array();
			while (false !== ($file = readdir($dir)))
				if($file !== '.' && $file !== '..' && !is_dir($filename=$this->name.$file)) $files[$filename]=$file;
			closedir($dir);
			ksort($files);
			return $files;
		}
		return false;
	}
	
	public function listAllPath(){
		if($dir=opendir($this->name)){
			$files=array();
			while (false !== ($file = readdir($dir)))
				if($file !== '.' && $file !== '..') $files[$filename]=$file;
			closedir($dir);
			ksort($files);
			return $files;
		}
		return false;
	}
	
	public function listAllFiles(){
		if($dir=opendir($this->name)){
			$files=array();
			while (false !== ($file = readdir($dir)))
				if($file === '.' || $file === '..') continue;
				if(is_dir($filename=$this->name.$file)){
					$subFolder=new Folder($filename);
					$files=$files + $subFolder->listAllFiles();
				}else $files[$filename]=$file;
			closedir($dir);
			ksort($files);
			return $files;
		}
		return false;
	}
}

class File extends AFile{
	public function __construct($filename){
		parent::__construct($filename);
		if($this->isDir()) throw new UnexpectedValueException($filename.' is a directory');
	}
	
	public function copyTo($dest){
		return copy($this->name, $dest);
	}
	
	public function moveTo($dest){
		return rename($this->name, $dest);
	}

	public function mkdirs(){
		return file_exists($dirname=dirname($this->name)) ? true : mkdir($dirname,0755,true);
	}

	public function delete(){
		return unlink($this->name);
	}
	
	public function &getExt(){
		$ext=strrpos($this->getName(),'.');
		if($ext!==false) $ext=substr($this->getName(),$ext+1);
		return $ext;
	}
	
	public function getSize(){
		return filesize($this->name);
	}
	
	private $_ressource=false;
	
	public function read(){
		return file_get_contents($this->name);
	}
	
	public function open($mode){
		$this->_ressource=fopen($this->name, $mode);
	}
	
	public function close(){
		fclose($this->_ressource);
	}
	
	public function __destruct(){
		if($this->_ressource) $this->close();
	}
	
	public function write($data){
		if($this->_ressource) return fwrite($this->_ressource, $data);
		return file_put_contents($this->name,$data);
	}
	
	public function append($data){
		if($this->_ressource) return fwrite($this->_ressource, $data);
		return file_put_contents($this->name, FILE_APPEND);
	}
}