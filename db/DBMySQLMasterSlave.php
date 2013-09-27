<?php
/**
 * A Master/Slave configuration
 * 
 * All updates are executed on the Master
 * All selects are executed on the Slave
 * 
 * 
 */
class DBMySQLMasterSlave extends DBMySQL{
	private $_connect_slave;
	
	/** @ignore */
	public function connect(){
		/* master (because of lastInserId) */
		//$this->connectMaster();
		$this->connectSlave();
	}
	
	private function connectMaster(){
		$this->_connect=$this->_createConnection($this->_config['master']);
	}
	private function connectSlave(){
		$this->_connect_slave=$this->_createConnection($this->_config['slave']);
	}
	
	/** @ignore */
	public function getVersion(){
		return $this->_connect_slave->server_version;
	}
	/** @ignore */
	public function close(){
		if($this->_connect!==null) $this->_connect->close();
		if($this->_connect_slave!==null) $this->_connect_slave->close();
		$this->_connect=$this->_connect_slave=null;
	}
	
	/** @ignore */
	public function ping(){
		if($this->_connect!==null && !$this->_connect->ping()){
			$this->_connect->close();
			$this->connectMaster();
			return true;
		}
		if(!$this->_connect_slave->ping()){
			$this->_connect_slave->close();
			$this->connectSlave();
		}
		return false;
	}
	
	/** @ignore */
	public function prepare($query){
		if($this->_connect===null) $this->connectMaster();
		return $this->_connect->prepare($query);
	}
	
	/** @ignore */
	public function beginTransaction(){ if($this->_connect===null){ $this->connectMaster(); } parent::beginTransaction(); }
	/** @ignore */
	public function commit(){ if($this->_connect===null){ $this->connectMaster(); } parent::commit(); }
	/** @ignore */
	public function rollBack(){ if($this->_connect===null){ $this->connectMaster(); } parent::rollBack(); }
	
	/** @ignore */
	public function escape($string){
		return '\''.$this->_connect_slave->real_escape_string($string).'\'';
	}
	
	
	
	/** @ignore */
	protected function _queryMaster($query){
		if($this->_connect===null) $this->connectMaster();
		return $this->_internal_query($this->_connect,$query);
	}
	/** @ignore */
	protected function _querySlave($query){
		return $this->_internal_query($this->_connect_slave,$query);
	}
	
	/** @ignore */
	protected function _preparedQuerySlave($query,$fields){
		return $this->_internal_preparedQuery($this->_connect_slave,$query,$fields);
	}
	
	
	/** @ignore */
	public function getHost(){
		return $this->_config['slave']['host'];
	}
	
	/** @ignore */
	public function getDbName(){
		return $this->formatTable($this->_config['slave']['dbname']);
	}
	
	/** @ignore */
	public function getDatabaseName(){
		return $this->_config['slave']['dbname'];
	}
}