<?php
class CCache_File extends CCache{
	private $_path;
	public function __construct($config){
		$this->_path=DATA.'cache/'.(isset($config['path'])?$config['path']:'');
		$this->setExpiration($config['expiration']);
	}
	public function read($key){
		if(!file_exists($filename=($this->_path.$key))) return null;
		if($this->_expiration && filemtime($filename) < (time() - $this->_expiration)){
			unlink($filename);
			return null;
		}
		
		$data=UFile::readWithLock($filename,'rb');
		if($data!==false&&$data!==null) return static::data_read($data); //after the lock
		return false;
	}
	public static function data_read($data){
		return unserialize($data);
	}
	public static function data_write($data){
		return serialize($data);
	}
	
	public function write($key,$data){
		return UFile::writeWithLock($this->_path.$key,static::data_write($data));
	}
	
	public function delete($key){
		return UFile::rm($this->_path.$key);
	}
}