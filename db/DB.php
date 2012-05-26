<?php
abstract class DB{
	private static $_INSTANCES=array();
	
	private static $_allConfigs;
	public static function loadConfig(/* DEV */$force=false/* /DEV */){
		/* DEV */ if($force || !App::$enhancing) /* /DEV */ self::$_allConfigs=&Config::$db;
	}
	public static function &langDir(){
		return self::$_allConfigs['_lang'];
	}
	
	/** @return DB */
	public static function &init($configName,$config=false){
		if(!isset(self::$_INSTANCES[$configName])){
			if($config===false){
				/* DEV */
				if(!isset(self::$_allConfigs[$configName])) throw new Exception('DB Config is missing : '.$configName);
				/* /DEV */
				$config=self::$_allConfigs[$configName]+array('prefix'=>'','type'=>'MySQL','host'=>'localhost','port'=>3306);
			}
			$className='DB'.$config['type'];
			self::$_INSTANCES[$configName]=new $className($configName,$config);
		}
		return self::$_INSTANCES[$configName];
	}
	
	/** @return DB */
	public static function &createWithPrefix($configName,$prefix){
		$config=self::$_allConfigs['default']+array('type'=>'MySQL','host'=>'localhost','port'=>3306);
		$config['prefix']=$prefix;
		$className='DB'.$config['type'];
		self::$_INSTANCES[$configName]=new $className($configName,$config);
		return self::$_INSTANCES[$configName];
	}

	public static function get($configName='default'){
		return self::$_INSTANCES[$configName];
	}
	
	public static function getAll(){
		return self::$_INSTANCES;
	}
	
	public static function pingAll(){
		foreach(self::$_INSTANCES as &$instance){
			$instance->ping();
		}
	}

	public static function reset(){
		foreach(self::$_INSTANCES as $instance){
			//$instance->close();
			$instance->connect();
		}
    }
	
	
	/* Class DB */

	protected $_name,$_connect,$_config;
	
	public function __construct($configName,$config){
		/* DEV */if(!isset(self::$_INSTANCES[$configName])) self::$_INSTANCES[$configName]=$this;/* /DEV */
		$this->_name=$configName;
		$this->_config=&$config;
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
	
	public function &_getName(){return $this->_name;}
	public abstract function _getType();

	public function &getConnect(){return $this->_connect;}
	public function &_getPrefix(){return $this->_config['prefix'];}


	//public function getVersion(){return $this->_connect->getAttribute(PDO::ATTR_SERVER_VERSION);}
	
	public function isInSameHost(DB &$db){
		return $this->_config['host'] === $db->_config['host'];
	}
	
	public function getDbName(){
		return $this->formatTable($this->_config['dbname']);
	}
	
	public function getDatabaseName(){
		return $this->_config['dbname'];
	}
	
	//public function lastInsertID($name=null) { return $this->_connect->lastInsertID($name); }
	public abstract function lastInsertID();
	
	
	/* Connection management */
	
	
	//public function close(){ $this->_connect=null; }
	public abstract function close();
	
	/*public function ping(){
		try{
			$statement=$this->_connect->query('SELECT 1');
			$statement=null;
		}catch(PDOException $ex){
			$this->connect();
			return;
		}
		if($this->_connect->errorCode() != '00000') $this->connect();
	}*/
	public abstract function ping();
	
	/* Transaction management */
	
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
		/* DEV *//*
		$t=microtime(true);
		/* /DEV *//*
		if($params===false) $statement=$this->_connect->query($query);
		else $statement=$this->_connect->prepare($query);
		$this->checkStatement($statement,$query,$params);
		if($params!==false){
			$statement->execute($params);
			$this->checkErrors($statement,$query,$params);
		}
		$result=call_user_func_array(array($statement,$methodName),$methodParams);
		/* DEV *//*
		$t=microtime(true) - $t;
		$this->_logQuery(array('query'=>$query,'params'=>$params,'result'=>$result,'time'=>$t));
		/* /DEV *//*
		return $result;
	}
	
	public function doSelectCallback($query,$params,$callback,$methodParams){
		/* DEV *//*
		$t=microtime(true);
		/* /DEV *//*
		if($params===false) $statement=$this->_connect->query($query);
		else $statement=$this->_connect->prepare($query);
		$this->checkStatement($statement,$query,$params);
		if($params!==false){
			$statement->execute($params);
			$this->checkErrors($statement,$query,$params);
		}

		/* DEV *//*
		$t=microtime(true) - $t;
		$this->_logQuery(array('query'=>&$query,'params'=>&$params,'result'=>false,'time'=>&$t));
		/* /DEV *//*
		
		while(($result=call_user_func_array(array($statement,'fetch'),$methodParams))!==false){
			$callback($result);
			unset($result);
		}
	}

	public function &doUpdate($query,$params=false){
		/* DEV *//*
		$t=microtime(true);
		/* /DEV *//*
		if($params===false) $statement=$this->_connect->exec($query);
		else $statement=$this->_connect->prepare($query);
		$this->checkStatement($statement,$query,$params);
		if($params!==false){
			$result=$statement->execute($params);
			$this->checkErrors($statement,$query,$params);
			if($result) $result=$statement->rowCount();
		}else $result=$statement;
		/* DEV *//*
		$t=microtime(true) - $t;
		$this->_logQuery(array('query'=>$query,'params'=>$params,'result'=>$result,'time'=>$t));
		/* /DEV *//*
		return $result;
	}
	
	public function &prepareStatement($query,$callback){
		/* DEV *//*
		$t=microtime(true);
		/* /DEV *//*
		$statement=$this->_connect->prepare($query);
		$this->checkStatement($statement,$query,NULL);
		$result=$callback($statement);
		/* DEV *//*
		$t=microtime(true) - $t;
		$this->_logQuery(array('query'=>$query,'params'=>false,'result'=>$result,'time'=>$t));
		/* /DEV *//*
		return $result;
	}
*/
	
	
	/* DEV */
	public function resetQueries(){}
	public function getQueries(){ return array(); }
	/* /DEV */
	
	public abstract function getDatabases();
	public abstract function getTables();
	
}

DB::loadConfig();