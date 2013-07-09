<?php
class DBMySQLMasterSlave extends DBMySQL{
	private $_connect_slave;
	
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
	
	public function getVersion(){
		return $this->_connect_slave->server_version;
	}
	public function close(){
		if($this->_connect!==null) $this->_connect->close();
		if($this->_connect_slave!==null) $this->_connect_slave->close();
		$this->_connect=$this->_connect_slave=null;
	}
	
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
	
	public function prepare($query){
		if($this->_connect===null) $this->connectMaster();
		return $this->_connect->prepare($query);
	}
	
	public function beginTransaction(){ if($this->_connect===null){ $this->connectMaster(); } parent::beginTransaction(); }
	public function commit(){ if($this->_connect===null){ $this->connectMaster(); } parent::commit(); }
	public function rollBack(){ if($this->_connect===null){ $this->connectMaster(); } parent::rollBack(); }
	
	public function escape($string){
		return '\''.$this->_connect_slave->real_escape_string($string).'\'';
	}
	
	
	
	protected function _queryMaster($query){
		if($this->_connect===null) $this->connectMaster();
		return $this->_internal_query($this->_connect,$query);
	}
	protected function _querySlave($query){
		return $this->_internal_query($this->_connect_slave,$query);
	}
	
	protected function _preparedQuerySlave($query,$fields){
		return $this->_internal_preparedQuery($this->_connect_slave,$query,$fields);
	}
	
	
	public function getHost(){
		return $this->_config['slave']['host'];
	}
	
	public function getDbName(){
		return $this->formatTable($this->_config['slave']['dbname']);
	}
	
	public function getDatabaseName(){
		return $this->_config['slave']['dbname'];
	}
}