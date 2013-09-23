<?php
/** APC Cache */
abstract class CCache_APC extends CCache{
	public function __construct($config){
		$this->setExpiration($config['expiration']);
	}

	public function read($key){
		
	}
	public function write($key,$data){
		return apc_store($key,self::serialize($data),$this->_expiration);
	}
	public function delete($key){
		
	}

}