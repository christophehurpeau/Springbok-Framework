<?php
abstract class DBAsyncQuery{
	private $_db, $_connect;
	
	public function __contruct($db,$connect){
		$this->_db = $db;
		$this->_connect = $connect;
	}
	
	public abstract function isAvailable();
	public abstract function result();
	public abstract function close();
	
}
