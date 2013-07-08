<?php
class DBMySQLMasterSlave extends DBMySQL{
	private $_connect_slave;
	
	public function connect(){
		/* master (because of lastInserId) */
		$this->_connect=$this->_createConnection($this->_config['master']);
		$this->_connect_slave=$this->_createConnection($this->_config['slave']);
	}
	
	
	protected function _queryMaster($query){
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