<?php
/** XCache */
abstract class CCache_XCache extends CCache{
	public function __construct($config){
		$this->setExpiration($config['expiration']);
	}

	
	public function read($key){
		
	}
	public function write($key,$data){
		return xcache_set($key,self::serializeWithTime($data));
	}
	public function delete($key){
		
	}
}