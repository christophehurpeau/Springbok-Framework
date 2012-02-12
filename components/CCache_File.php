<?php
class CCache_File extends CCache{
	private $_path;
	public function __construct(&$config){
		$this->_path=DATA.'cache/'.(isset($config['path'])?$config['path']:'');
		$this->setExpiration($config['expiration']);
	}
	public function read($key){
		if(!file_exists($filename=($this->_path.$key))) return null;
		if($this->_expiration && filemtime($filename) < (time() - $this->_expiration)){
			unlink($filename);
			return null;
		}
		
		$fp=fopen($filename,'rb');
		flock($fp, LOCK_SH);
		if(($filesize=filesize($filename)) > 0) $data=fread($fp,$filesize);
		else $data=false;
		flock($fp, LOCK_UN);
		fclose($fp);
		if($data!==false) return static::data_read($data); //after the lock
		return false;
	}
	public static function data_read(&$data){
		return unserialize($data);
	}
	public static function data_write(&$data){
		return serialize($data);
	}
	
	public function write($key,&$data){
		$fp=fopen($this->_path.$key,'wb');
		$dataw=static::data_write($data);//before the lock
		if(!flock($fp, LOCK_EX)) return false;
		fwrite($fp,$dataw);
		flock($fp,LOCK_UN);
		fclose($fp);
		return true;
	}
	
	public function delete($key){
		if(!file_exists($filename=($this->_path.$key))) return NULL;
		return unlink($filename);
	}
}