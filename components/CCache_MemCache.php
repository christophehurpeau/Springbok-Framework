<?php
//$key=$_SERVER['SERVER_NAME'].'.'.$name;
/** MemCache */
abstract class CCache_MemCache extends CCache{
	private $_mcache=NULL;

	public function __construct($config){
		$pool=explode(';',$config['servers']);
		foreach ($pool as $server){
			// Hostname:port
			if(strpos($server,':')) list($host,$port)=explode(':',$server);
			else{ $host=$server; $port=11211;/* Use default port */ }
			
			// Connect to each server
			if (is_null($this->_mcache)) $this->_mcache=memcache_pconnect($host,$port);
			else memcache_add_server($this->_mcache,$host,$port);
		}
		$this->setExpiration($config['expiration']);
	}
	
	public function read($key){
		$val=memcache_get(self::$backend['id'],$key);
		if(is_bool($val)) return false;
		list($time,$data)=unserialize(gzinflate($val));
	}
	public function write($key,$data){
		return memcache_set($this->_mcache,$key,self::serializeWithTime($data));
	}
	public function delete($key){
		
	}
}