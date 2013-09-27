<?php
/**
 * DB Mongo class
 */
class DBMongo extends DB{
	/** @ignore */
	public function _getType(){return 'Mongo';}
	
	private $_db;
	
	/** @ignore */
	public function connect(){
		/*#if DEV */ if(!class_exists('MongoClient',false)) throw new Exception('Please install MongoDB extension : http://www.mongodb.org/display/DOCS/PHP+Language+Center or update it : sudo pecl upgrade-all'); /*#/if*/
		$this->_connect=new MongoClient($this->_config['server'],array('connect'=>true));
		$this->_db=$this->_connect->selectDB($this->_config['dbname']);
	}
	
	/** @ignore */
	public function getVersion(){return '?'; }
	
	/** @ignore */
	public function lastInsertID($name=null){}
	
	/**
	 * @return array
	 * @see http://php.net/manual/en/mongodb.lasterror.php
	 */
	public function lastError(){
		return $this->_db->lastError();
	}
	
	/**
	 * Close the DB
	 * 
	 * @return void
	 */
	public function close(){
		$this->_connect=null;
	}
	
	/**
	 * Does nothing
	 * 
	 * @return true
	 */
	public function ping(){
		return true;
	}
	
	/**
	 * Return the MongoDB instance
	 * 
	 * @return MongoDB
	 * @see http://www.php.net/manual/en/class.mongodb.php
	 */
	public function db(){
		return $this->_db;
	}
	
	/**
	 * Returns an associative array containing three fields.
	 * The first field is databases, which in turn contains an array.
	 * Each element of the array is an associative array corresponding to a database, giving th database's name, size, and if it's empty.
	 * The other two fields are totalSize (in bytes) and ok, which is 1 if this method ran successfully. 
	 * 
	 * @return array
	 * @see http://www.php.net/manual/en/mongoclient.listdbs.php
	 */
	public function getDatabases(){
		return $this->_connect->listDBs();
	}
	
	/**
	 * Gets a list of all the collections in the database and returns them as an array of MongoCollection objects.
	 * 
	 * @return array
	 */
	public function getTables(){
		return $this->_db->listCollections();
	}
	
	
	/**
	 * Select a collection.
	 * 
	 * @return MongoCollection
	 * @see http://www.php.net/manual/en/mongodb.selectcollection.php
	 */
	public function collection($name){
		return $this->_db->selectCollection($name);
	}
}