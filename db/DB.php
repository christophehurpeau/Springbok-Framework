<?php
/**
 * DB Class
 * 
 * Contains all your DB instances
 * 
 * <code>
 * $db = DB::init('default'); //Create or return if already exists
 * $db->doUpdate('UPDATE `posts` SET `status` = '.Post::DRAFT);
 * </code>
 * 
 */
abstract class DB{
	private static $_INSTANCES=array();
	
	private static $_allConfigs;
	public static function loadConfig(/*#if DEV */$force=false/*#/if*/){
		/*#if DEV */ if($force || !App::$enhancing) /*#/if*/ self::$_allConfigs=Config::$db;
	}
	public static function langDir(){
		return self::$_allConfigs['_lang'];
	}
	
	/**
	 * return a DB instance.
	 * 
	 * @param string
	 * @param array config, if you don't want to use your config file.
	 * @return DB
	 */
	public static function init($configName,$config=false){
		if(isset(self::$_INSTANCES[$configName])) return self::$_INSTANCES[$configName];
		if($config===false){
			/*#if DEV */
			if(!isset(self::$_allConfigs[$configName])) throw new Exception('DB Config is missing : '.$configName);
			/*#/if*/
			$config=self::$_allConfigs[$configName]+array('prefix'=>'','type'=>'MySQL','host'=>'localhost','port'=>3306);
		}
		$className='DB'.$config['type'];
		return self::$_INSTANCES[$configName]=new $className($configName,$config);
	}
	
	/**
	 * @ignore
	 * @return DB
	 */
	public static function createWithPrefix($configName,$prefix){
		$config=self::$_allConfigs['default']+array('type'=>'MySQL','host'=>'localhost','port'=>3306);
		$config['prefix']=$prefix;
		$className='DB'.$config['type'];
		self::$_INSTANCES[$configName]=new $className($configName,$config);
		return self::$_INSTANCES[$configName];
	}
	
	/**
	 * return an existing DB instance.
	 * 
	 * @param string
	 * @return DB
	 */
	public static function get($configName='default'){
		return self::$_INSTANCES[$configName];
	}
	
	/**
	 * Return all DB instances
	 * @return array
	 */
	public static function getAll(){
		return self::$_INSTANCES;
	}
	
	/**
	 * Ping all DB instance
	 * 
	 * @return void
	 */
	public static function pingAll(){
		foreach(self::$_INSTANCES as &$instance){
			$instance->ping();
		}
	}
	
	/** @ignore */
	public static function setTestEnvironment(){
		foreach(self::$_allConfigs as $configName=>&$config){
			if($configName==='_lang') continue;
			/*#if DEV */
			if(empty($config['dbname-test'])) $config['dbname-test']=$config['dbname'].'-test';
			if($config['dbname']===$config['dbname-test']) throw new Exception('DB Config for "'.$configName.'" has the same dbname for test');
			/*#/if*/
			if(!isset($config['dbname-origin'])) $config['dbname-origin']=$config['dbname'];
			$config['dbname']=$config['dbname-test'];
		}
		foreach(self::$_INSTANCES as $instance) $instance->switchToTestEnvironment();
		class_exists('UFile',true);
		$schemaProcessing=new DBSchemaProcessing(new Folder(APP.'models'),new Folder(APP.'triggers'),true);
		
		foreach(self::$_INSTANCES as $instance)
			if($instance instanceof DBMySQL) $instance->doUpdate('SET FOREIGN_KEY_CHECKS=0');
		
		foreach(SModel::$__loadedModels as $loadedModel)
			$loadedModel::truncate();
			
		foreach(self::$_INSTANCES as $instance)
			if($instance instanceof DBMySQL) $instance->doUpdate('SET FOREIGN_KEY_CHECKS=1');
	}
	
	/**
	 * Reset and reconnect all DB instances
	 * 
	 * @return void
	 */
	public static function reset(){
		foreach(self::$_INSTANCES as $instance){
			//$instance->close();
			$instance->connect();
		}
	}
	
	/**
	 * Reset all queries log in all DB instances
	 * 
	 * @return void
	 */
	public static function resetAllQueries(){
		foreach(self::$_INSTANCES as $instance) $instance->resetQueries();
	}
	
	
	/* Class DB */

	protected $_name,$_connect,$_config;
	
	/** @ignore */
	public function __construct($configName,$config){
		/*#if DEV */if(!isset(self::$_INSTANCES[$configName])) self::$_INSTANCES[$configName]=$this;/*#/if*/
		$this->_name=$configName;
		$this->_config=$config;
		$this->connect();
	}
	/*
	private function connect(){
		$this->_connect=new PDO($this->_config['dsn'],$this->_config['user'],$this->_config['password'],array(PDO::MYSQL_ATTR_INIT_COMMAND =>'SET NAMES utf8'));
		//$this->_connect->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
		//$this->_connect->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY,true);
	}
	*/
	/* Getters */
	
	/**
	 * Return the name of the DB
	 * 
	 * @return string
	 */
	public function _getName(){
		return $this->_name;
	}
	
	public abstract function _getType();
	
	/**
	 * Return the real DB connection
	 * 
	 * @return mixed
	 */
	public function getConnect(){
		return $this->_connect;
	}
	
	/**
	 * Return the table/collection prefix
	 * 
	 * @return string
	 */
	public function _getPrefix(){
		return $this->_config['prefix'];
	}


	//public function getVersion(){return $this->_connect->getAttribute(PDO::ATTR_SERVER_VERSION);}
	
	/**
	 * Return if two DB instance are in the same host.
	 * Used in queries for relations
	 * 
	 * @param DB
	 * @return bool
	 */
	public function isInSameHost($db){
		return $this->getHost() === $db->getHost();
	}
	
	/**
	 * Return the current host
	 * 
	 * @return string
	 */
	public function getHost(){
		return $this->_config['host'];
	}
	
	/**
	 * Return the formatted current db name
	 * 
	 * @return string
	 */
	public function getDbName(){
		return $this->formatTable($this->_config['dbname']);
	}
	
	/**
	 * Return the current host, not formatted
	 * 
	 * @return string
	 */
	public function getDatabaseName(){
		return $this->_config['dbname'];
	}
	
	/** @ignore */
	public function switchToTestEnvironment(){
		if($this->_name==='_lang') return;
		/*#if DEV */
		if(empty($this->_config['dbname-test'])) $this->_config['dbname-test']=$this->_config['dbname'].'-test';
		if($this->_config['dbname']===$this->_config['dbname-test']) throw new Exception('DB Config for "'.$this->_name.'" has the same dbname for test');
		/*#/if*/
		$this->_config['dbname']=$this->_config['dbname-test'];
		$this->connect();
	}
	
	//public function lastInsertID($name=null) { return $this->_connect->lastInsertID($name); }
	
	/**
	 * Return the last insterted ID in autoincremented tables
	 * 
	 * @return int
	 */
	public abstract function lastInsertID();
	
	
	/* Connection management */
	
	/**
	 * Close the DB
	 * 
	 * @return void
	 */
	public abstract function close();
	
	/**
	 * Ping the DB
	 * 
	 * @return void
	 */
	public abstract function ping();
	
	/* Transaction management */
	
	/**
	 * Create a new transaction in a closure
	 * 
	 * @param function
	 * @return void
	 */
	public function transaction($callback){
		$this->beginTransaction();
		call_user_func($callback,$this);
		$this->commit();
	}
	/*
	public function beginTransaction(){ $this->_connect->beginTransaction(); }
	public function commit(){ $this->_connect->commit(); }
	public function rollBack(){ $this->_connect->rollBack(); }
	*/
	/* Errors */
	/*
	private function checkStatement(&$statement,&$query,&$params){
		if($statement===false) throw new PDOException('Statement prepare return false : '.
			print_r($this->_connect->errorInfo(),true)."\n".'Query: '.$query."\nQuery with Params: ".self::_createQueryWithParams($query,$params));
	}
	
	private function checkErrors(&$statement,&$query,&$params/*,$autoReconnect=true*//*){
		if($statement->errorCode() !== '00000'){
			/*$errorInfos=$statement->errorInfo();
			if($autoReconnect && in_array($errorInfos[2],array(
					2013 // Lost connection to MySQL server during query
				)))*//*
			throw new PDOException('Statement errorCode !== \'00000\' : '
				.print_r($statement->errorInfo(),true)."\n".'Query:'.$query."\nQuery with Params: ".self::_createQueryWithParams($query,$params));
		}
	}
	
	
	
	/* QUERIES */
	/*
	public function &doSelect($query,$params,$methodName,$methodParams=array()){
		/*#if DEV *//*
		$t=microtime(true);
		/*#/if*//*
		if($params===false) $statement=$this->_connect->query($query);
		else $statement=$this->_connect->prepare($query);
		$this->checkStatement($statement,$query,$params);
		if($params!==false){
			$statement->execute($params);
			$this->checkErrors($statement,$query,$params);
		}
		$result=call_user_func_array(array($statement,$methodName),$methodParams);
		/*#if DEV *//*
		$t=microtime(true) - $t;
		$this->_logQuery(array('query'=>$query,'params'=>$params,'result'=>$result,'time'=>$t));
		/*#/if*//*
		return $result;
	}
	
	public function doSelectCallback($query,$params,$callback,$methodParams){
		/*#if DEV *//*
		$t=microtime(true);
		/*#/if*//*
		if($params===false) $statement=$this->_connect->query($query);
		else $statement=$this->_connect->prepare($query);
		$this->checkStatement($statement,$query,$params);
		if($params!==false){
			$statement->execute($params);
			$this->checkErrors($statement,$query,$params);
		}

		/*#if DEV *//*
		$t=microtime(true) - $t;
		$this->_logQuery(array('query'=>&$query,'params'=>&$params,'result'=>false,'time'=>&$t));
		/*#/if*//*
		
		while(($result=call_user_func_array(array($statement,'fetch'),$methodParams))!==false){
			$callback($result);
			unset($result);
		}
	}

	public function &doUpdate($query,$params=false){
		/*#if DEV *//*
		$t=microtime(true);
		/*#/if*//*
		if($params===false) $statement=$this->_connect->exec($query);
		else $statement=$this->_connect->prepare($query);
		$this->checkStatement($statement,$query,$params);
		if($params!==false){
			$result=$statement->execute($params);
			$this->checkErrors($statement,$query,$params);
			if($result) $result=$statement->rowCount();
		}else $result=$statement;
		/*#if DEV *//*
		$t=microtime(true) - $t;
		$this->_logQuery(array('query'=>$query,'params'=>$params,'result'=>$result,'time'=>$t));
		/*#/if*//*
		return $result;
	}
	
	public function &prepareStatement($query,$callback){
		/*#if DEV *//*
		$t=microtime(true);
		/*#/if*//*
		$statement=$this->_connect->prepare($query);
		$this->checkStatement($statement,$query,NULL);
		$result=$callback($statement);
		/*#if DEV *//*
		$t=microtime(true) - $t;
		$this->_logQuery(array('query'=>$query,'params'=>false,'result'=>$result,'time'=>$t));
		/*#/if*//*
		return $result;
	}
*/
	
	
	/*#if DEV */
	public function resetQueries(){}
	public function getQueries(){ return array(); }
	public function getNbQueries(){ return 0; }
	/*#/if*/
	
	public abstract function getDatabases();
	public abstract function getTables();
	
}

DB::loadConfig();