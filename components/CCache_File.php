<?php
/** File Cache */
class CCache_File extends CCache{
	private $_path;
	public function __construct($config){
		$this->_path=DATA.'cache/'.(isset($config['path'])?$config['path']:'');
		$this->setExpiration($config['expiration']);
	}
	
	/**
	 * @param string
	 * @return mixed unserialized data or null if not present of false if data is empty
	 */
	public function read($key){
		if(!file_exists($filename=($this->_path.$key))) return null;
		if($this->_expiration && filemtime($filename) < (time() - $this->_expiration)){
			UFile::rm($filename);
			return null;
		}
		
		$data=UFile::readWithLock($filename,'rb');
		if($data!==false&&$data!==null) return static::data_read($data); //after the lock
		return false;
	}
	
	/**
	 * @param string
	 * @return mixed
	 */
	public static function data_read($data){
		return unserialize($data);
	}
	/**
	 * @param mixed
	 * @return string
	 */
	public static function data_write($data){
		return serialize($data);
	}
	
	/**
	 * @param string
	 * @param string
	 * @see UFile::writeWithLock
	 */
	public function write($key,$data){
		return UFile::writeWithLock($this->_path.$key,static::data_write($data));
	}
	
	/**
	 * @param string
	 */
	public function delete($key){
		return UFile::rm($this->_path.$key);
	}
}