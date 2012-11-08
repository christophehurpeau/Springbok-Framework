<?php
class DBMongo extends DB{
	public function _getType(){return 'Mongo';}
	
	private $_db;
	
	public function connect(){
		/* DEV */ if(!class_exists('Mongo',false)) throw new Exception('Please install MongoDB extension : http://www.mongodb.org/display/DOCS/PHP+Language+Center'); /* /DEV */
		$this->_connect=new Mongo('mongodb://'.$this->_config['host'].':'.$this->_config['port'],array('connect'=>true));
		$this->_db=$this->_connect->selectDB($this->_config['dbname']);
	}
	
	public function getVersion(){return '?'; }
	
	public function lastInsertID($name=null){}
	
	public function close(){
		$this->_connect->close();
		$this->_connect=null;
	}
	
	public function ping(){
		return true;
	}
	
	public function db(){
		return $this->_db;
	}
	
	
	public function getDatabases(){
		return $this->_connect->listDBs();
	}
	public function getTables(){
		return $this->_db->listCollections();
	}
	
	
	public function collection($name){
		return $this->_db->selectCollection($name);
	}
}