<?php
abstract class DB{
	private static $_INSTANCES=array();
	
	private static $_allConfigs;
	public static function loadConfig(){
		self::$_allConfigs=&Config::$db;
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
	
	public function __destruct(){
		$this->close();
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
	public abstract function getVersion();
	
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
	public abstract function beginTransaction();
	public abstract function commit();
	public abstract function rollBack();
	
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
	public abstract function escape($string);

	/* DEV */
	public function &doSelect($methodName,$methodParams){
		$t=microtime(true);
		$result=call_user_func_array(array(&$this,'_doSelect'.$methodName),$methodParams);
		$t=microtime(true) - $t;
		$this->_logQuery(array('query'=>&$methodParams[0],'result'=>&$result,'time'=>&$t));
		return $result;
	}
	public function doUpdate($query){
		$t=microtime(true);
		$result=$this->_doUpdate($query);
		$t=microtime(true) - $t;
		$this->_logQuery(array('query'=>&$query,'result'=>&$result,'time'=>&$t));
		return $result;
	}
	
	public function prepare($query){
		return $this->_connect->prepare($query);
	}

	public function doSelectSql($query){
		return $this->doSelect('Sql',func_get_args());
	}
	public function doSelectSqlCallback($query,$callback,$callbackFields){
		return $this->doSelect('SqlCallback',func_get_args());
	}
	
	public function doSelectRows($query){
		return $this->doSelect('Rows',func_get_args());
	}
	public function doSelectRows_($query){
		return $this->doSelect('Rows_',func_get_args());
	}
	public function doSelectRow($query){
		return $this->doSelect('Row',func_get_args());
	}
	public function doSelectRow_($query){
		return $this->doSelect('Row_',func_get_args());
	}
	public function doSelectValues($query,$numCol=0){
		return $this->doSelect('Values',func_get_args());
	}
	public function doSelectValue($query,$numCol=0){
		return $this->doSelect('Value',func_get_args());
	}
	public function doSelectListValues($query){
		return $this->doSelect('ListValues',func_get_args());
	}
	public function doSelectListValues_($query){
		return $this->doSelect('ListValues_',func_get_args());
	}
	public function doSelectListValue($query){
		return $this->doSelect('ListValue',func_get_args());
	}
	public function doSelectObjects(&$query,&$queryObj,&$fields){
		return $this->doSelect('Objects',array(&$query,&$queryObj,&$fields));
	}
	public function doSelectListObjects(&$query,&$queryObj,&$fields){
		return $this->doSelect('ListObjects',array(&$query,&$queryObj,&$fields));
	}
	public function doSelectAssocObjects(&$query,&$queryObj,&$fields,&$tabResKey){
		return $this->doSelect('AssocObjects',array(&$query,&$queryObj,&$fields,&$tabResKey));
	}
	public function doSelectObject(&$query,&$queryObj,&$fields){
		return $this->doSelect('Object',array(&$query,&$queryObj,&$fields));
	}
	
	public function doSelectRowsCallback($query,$callback){
		return $this->doSelect('RowsCallback',array(&$query,&$callback));
	}
	public function doSelectRowsCallback_($query,$callback){
		return $this->doSelect('RowsCallback_',array(&$query,&$callback));
	}
	public function doSelectObjectsCallback(&$query,&$queryObj,&$fields,&$callback){
		return $this->doSelect('ObjectsCallback',array(&$query,&$queryObj,&$fields,&$callback));
	}
	public function doSelectValuesCallback($query,$callback,$numCol=0){
		return $this->doSelect('ValuesCallback',array(&$query,&$callback,&$numCol));
	}
	
	
	/* /DEV */
	
	public abstract function /* DEV */_/* /DEV */doUpdate($query);
	
	public abstract function /* DEV */_/* /DEV */doSelectSql($query);
	public abstract function /* DEV */_/* /DEV */doSelectSqlCallback($query,$callback,$callbackFields);
	
	public abstract function &/* DEV */_/* /DEV */doSelectRows($query);
	public abstract function /* DEV */_/* /DEV */doSelectRowsCallback($query,$callback);
	public abstract function &/* DEV */_/* /DEV */doSelectRows_($query);
	public abstract function /* DEV */_/* /DEV */doSelectRowsCallback_($query,$callback);
	public abstract function &/* DEV */_/* /DEV */doSelectRow($query);
	public abstract function &/* DEV */_/* /DEV */doSelectRow_($query);
	public abstract function &/* DEV */_/* /DEV */doSelectObjects(&$query,&$queryObj,&$fields);
	public abstract function /* DEV */_/* /DEV */doSelectObjectsCallback(&$query,&$queryObj,&$fields,&$callback);
	public abstract function &/* DEV */_/* /DEV */doSelectAssocObjects(&$query,&$queryObj,&$fields,&$tabResKey);
	public abstract function &/* DEV */_/* /DEV */doSelectObject(&$query,&$queryObj,&$fields);
	public abstract function &/* DEV */_/* /DEV */doSelectValues($query);
	public abstract function /* DEV */_/* /DEV */doSelectValuesCallback($query,$callback,$numCol=0);
	public abstract function &/* DEV */_/* /DEV */doSelectValue($query);
	public abstract function &/* DEV */_/* /DEV */doSelectListValues($query);
	public abstract function &/* DEV */_/* /DEV */doSelectListValues_($query);
	public abstract function &/* DEV */_/* /DEV */doSelectListValue($query);
	
	/* QUERIES LOG */
	
	/* DEV */
	private $_queries=array();
	
	public function getQueries(){
		return $this->_queries;
	}
	
	public function resetQueries(){
		$this->_queries=array();
	}
	
	private function _logQuery($qr){
		if(count($this->_queries) < 5000){
			$qr['backtrace']=array_slice(debug_backtrace(),1);
			$this->_queries[]=$qr;
			CLogger::get('queries-'.date('Y-m-d-H'))->log($qr['query']);
		}else CLogger::get('queries-'.date('Y-m-d-H'))->log($qr['query']);
	}
	private static function _createQueryWithParams(&$query,&$params){
		if(empty($params)) return $query;
		$i=0;
		return preg_replace_callback('/\?/m',function($matches) use(&$i,&$params){ return '"'.$params[$i++].'"'; },$query);
	}
	/* /DEV */
	
	
	
	/* */
	
	public function insertMultiple($tableName,$nbValues,$data,$cols=NULL){
		$this->insertOrReplaceMultiple('INSERT',$tableName,$nbValues,$data,$cols);
	}
	public function insertIgnoreMultiple($tableName,$nbValues,$data,$cols=NULL){
		$this->insertOrReplaceMultiple('INSERT IGNORE',$tableName,$nbValues,$data,$cols);
	}
	public function replaceMultiple($tableName,$nbValues,$data,$cols=NULL){
		$this->insertOrReplaceMultiple('REPLACE',$tableName,$nbValues,$data,$cols);
	}
	
	protected function insertOrReplaceMultiple($keyword,$tableName,$nbValues,$data,$cols=NULL){
		$db=&$this;
		$query=$keyword.' INTO '.$this->formatTable($tableName);
		if(!empty($cols)) $query.=' (`'.implode('`,`',$cols).'`)';
		$values=array();
		foreach($data as &$values2){
			$cvalues=array();
			foreach($values2 as $v) $cvalues[]=$v===null?'NULL':(is_int($v) || is_float($v) ? $v : (is_bool($v)?($v===true?'""':'NULL'):$this->escape($v)));
			$values[]=implode(',',$cvalues);
		}
		$query.=' VALUES ('.implode('),(',$values).')';
		return $this->doUpdate($query);
	}
	
	
	public function formatField($field){return '`'.$field.'`';}
	public function formatTable($table){return '`'.$table.'`';}
	public function formatColumn($column){return '`'.$column.'`';}
	
	
	public abstract function getDatabases();
	public abstract function getTables();
	public abstract function truncate($table);
	
}

DB::loadConfig();