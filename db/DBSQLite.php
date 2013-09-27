<?php
/**
 * DB SqlLite
 */
class DBSQLite extends DBSql{
	public static $DEFAULT_FLAGS;
	
	/** @ignore */
	public function _getType(){return 'SQLite';}
	
	/** @ignore */
	public function __construct($configName,$config){
		if(!isset($config['flags'])) $config['flags']=self::$DEFAULT_FLAGS;
		parent::__construct($configName,$config);
	}
	
	/** @ignore */
	public function connect(){
		/*#if DEV */try{ /*#/if*/
		$this->_connect=new SQLite3($this->_config['file'],$this->_config['flags']);
		/*#if DEV */}catch(Exception $e){
			throw new Exception('Unable to connect: '.$this->_config['file'].' '.$e->getMessage());
		}/*#/if*/
		/*if(!$this->_connect) throw new DBException('Unable to connect',null);*/
	}
	
	/** @ignore */
	public function getVersion(){return $this->_conect->version();}
	
	/**
	 * Returns the row ID of the most recent INSERT into the database
	 * 
	 * @return int
	 * @see http://php.net/manual/en/sqlite3.lastinsertrowid.php
	 */
	public function lastInsertID($name=null) {
		return $this->_connect->lastInsertRowID();
	}
	
	/**
	 * Closes the database connection.
	 * 
	 * @return void
	 */
	public function close(){
		if($this->_connect===null) return;
		$this->_connect->close();
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
	 * Begin a new Transaction
	 * 
	 * @return void
	 */
	public function beginTransaction(){  $this->_query('BEGIN TRANSACTION'); }
	
	/**
	 * Commit a previous transaction
	 * 
	 * @return void
	 */
	public function commit(){ $this->_query('COMMIT');  }
	
	/**
	 * Roll back a transaction
	 * 
	 * @return void
	 */
	public function rollBack(){ $this->_query('ROLLBACK'); }
	
	/**
	 * Returns a string that has been properly escaped for safe inclusion in an SQL statement. 
	 * 
	 * @param string
	 * @return string
	 * @see http://www.php.net/manual/en/sqlite3.escapestring.php
	 */
	public function escape($string){
		return '\''.$this->_connect->escapeString($string).'\'';
	}
	
	private function _query($query){
		return $this->_connect->query($query);
	}
	
	/**
	 * Do an update query : UPDATE, DELETE, ...
	 * 
	 * @param string your SQL query
	 * @return int number of affected rows
	 */
	public function /*#if DEV then _*/doUpdate($query){
		return $this->_query($query);
	}
	
	private function getFieldsSQL($r){
		$fields=array();
		$nbFields=$r->numColumns();$i=0;
		while($i<$nbFields){
			switch($r->columnType($i)){
				case SQLITE3_INTEGER: $type='int'; break;
				case SQLITE3_FLOAT: $type='float'; break;
				case SQLITE3_TEXT: $type='string'; break;
				case SQLITE3_BLOB: $type='string'; break;
				case SQLITE3_NULL: $type=NULL; break;
				default: throw new Exception('Unknown type: '.$r->columnType($i));
			}
			$fields=array('name'=>$r->columnName($i),'type'=>$type);
		}
		return $fields;
	}
	
	/**
	 * Execute a SELECT query
	 * 
	 * @param string
	 * @return array ['res'=>,'fields'=>]
	 */
	public function /*#if DEV then _*/doSelectSql($query){
		$r=$this->_query($query); $res=array(); $fields=$this->getFieldsSQL($r);
		while($row=$r->fetchArray(SQLITE3_ASSOC)) $res[]=$row;
		$r->close();
		return array('res'=>$res,'fields'=>$fields);
	}
	
	/**
	 * Execute a SELECT query and execute the callback for each rows
	 * 
	 * @param string
	 * @return void
	 */
	public function /*#if DEV then _*/doSelectSqlCallback($query,$callback,$callbackFields){
		$r=$this->_query($query);
		$callbackFields($this->getFieldsSQL($r));
		while($row=$r->fetchArray(SQLITE3_ASSOC)) $callback($row);
		$res=array(); $r->close();
	}
	
	/**
	 * Execute a query and return all rows or an empty array
	 * 
	 * @param string
	 * @return array
	 */
	public function /*#if DEV then _*/doSelectRows($query){
		$r=$this->_query($query); $res=array();
		while($row=$r->fetchArray(SQLITE3_ASSOC)) $res[]=$row;
		return $res;
	}
	
	/**
	 * Execute a query and execute the callback for each rows
	 * 
	 * @param string
	 * @return array
	 */
	public function /*#if DEV then _*/doSelectRowsCallback($query,$callback){
		$r=$this->_query($query);
		while($row=$r->fetchArray(SQLITE3_ASSOC)) $callback($row);
	}
	
	/**
	 * Execute a query and return all rows or an empty array
	 * 
	 * @param string
	 * @return array
	 */
	public function /*#if DEV then _*/doSelectRows_($query){
		$r=$this->_query($query); $res=array();
		while($row=$r->fetchArray(SQLITE3_NUM)) $res[]=$row;
		return $res;
	}
	
	/**
	 * Execute a query and execute the callback for each rows
	 * 
	 * @param string
	 * @return array
	 */
	public function /*#if DEV then _*/doSelectRowsCallback_($query,$callback){
		$r=$this->_query($query);
		while($row=$r->fetchArray(SQLITE3_NUM)) $callback($row);
	}
	
	
	/**
	 * Execute a query and return the first row
	 * 
	 * @param string
	 * @return array [ fieldName => row ]
	 */
	public function /*#if DEV then _*/doSelectRow($query){
		// TODO TESTER querySingle($query,true)
		$r=$this->_query($query);
		$res=$r->fetchArray(SQLITE3_ASSOC);
		return $res;
	}
	/**
	 * Execute a query and return the first row
	 * 
	 * @param string
	 * @return array [ numField => row ]
	 */
	public function /*#if DEV then _*/doSelectRow_($query){
		$r=$this->_query($query);
		$res=$r->fetchArray(SQLITE3_NUM);
		return $res;
	}
	
	/**
	 * Execute a query and return a list of values
	 * 
	 * @param string
	 * @param int
	 * @return array
	 */
	public function /*#if DEV then _*/doSelectValues($query,$numCol=0){
		$r=$this->_query($query); $res=array();
		while($row=$r->fetchArray(SQLITE3_NUM)) $res[]=$row[$numCol];
		return $res;
	}
	
	/**
	 * Execute a query and execute a callback for each values
	 * 
	 * @param string
	 * @param int
	 * @return array
	 */
	public function /*#if DEV then _*/doSelectValuesCallback($query,$callback,$numCol=0){
		$r=$this->_query($query);
		while($row=$r->fetchArray(SQLITE3_NUM)) $callback($row[$numCol]);
	}
	
	/**
	 * Execute a query and return a list of value=>value
	 * 
	 * @param string
	 * @param int
	 * @return array
	 */
	public function /*#if DEV then _*/doSelectAssocValues($query,$tabResKey){
		$r=$this->_query($query); $res=array();
		while($row=$r->fetchArray(SQLITE3_NUM)) $res[$row[0]]=$row[0];
		return $res;
	}
	
	/**
	 * Execute a query and return a value
	 * 
	 * @param string
	 * @return mixed
	 */
	public function /*#if DEV then _*/doSelectValue($query){
		//return $this->_connect->querySingle($query);
		$r=$this->_query($query);
		if($row=$r->fetchArray(SQLITE3_NUM)) return $row[0];
		$res=false; return $res;
	}
	
	/**
	 * Execute a query and return if the query returned a result
	 * 
	 * @param string
	 * @return bool
	 */
	public function /*#if DEV then _*/doSelectExist($query){
		$r=$this->_query($query);
		if($row=$r->fetchArray(SQLITE3_NUM)) $res=true; else $res=false;
		return $res;
	}
	
	/**
	 * Execute a query and return a list of key=>row
	 * 
	 * @param string
	 * @return array
	 */
	public function /*#if DEV then _*/doSelectListRows($query){
		$r=$this->_query($query); $res=array();
		while($row=$r->fetchArray(SQLITE3_ASSOC)) $res[current($row)]=$row;
		return $res;
	}
	
	/**
	 * Execute a query and return a list of key=>row
	 * 
	 * @param string
	 * @return array
	 */
	public function /*#if DEV then _*/doSelectListRows_($query){
		$r=$this->_query($query); $res=array();
		while($row=$r->fetchArray(SQLITE3_NUM)) $res[$row[0]]=$row;
		return $res;
	}
	
	/**
	 * Execute a query and return a list of key=>value
	 * 
	 * @param string
	 * @return array
	 */
	public function /*#if DEV then _*/doSelectListValue($query){
		$r=$this->_query($query); $res=array();
		while($row=$r->fetchArray(SQLITE3_NUM)) $res[$row[0]]=$row[1];
		return $res;
	}
	
	/** @ignore */
	public function /*#if DEV then _*/doSelectObjects($query,$queryObj,$fields){
		$rows=$this->doSelectRows_($query); $res=array();
		foreach($rows as $row) $res[]=$queryObj->_createObject($row);
		return $res;
	}
	/** @ignore */
	public function /*#if DEV then _*/doSelectObjectsCallback($query,$queryObj,$fields,$callback){
		$rows=$this->doSelectRows_($query);
		foreach($rows as $row) $callback($queryObj->_createObject($row));
	}
	/** @ignore */
	public function /*#if DEV then _*/doSelectListObjects($query,$queryObj,$fields){
		$rows=$this->doSelectRows_($query); $res=array();
		foreach($rows as $row) $res[$row[0]]=$queryObj->_createObject($row);
		return $res;
	}
	
	/** @ignore */
	public function /*#if DEV then _*/doSelectAssocObjects($query,$queryObj,$fields,$tabResKey){
		$rows=$this->doSelectRows_($query); $res=array();
		foreach($rows as $row){
			$obj=$queryObj->_createObject($row);
			$res[$obj->{$this->tabResKey}]=$obj;
		}
		return $res;
	}
	
	/** @ignore */
	public function /*#if DEV then _*/doSelectObject($query,$queryObj,$fields){
		$row=$this->doSelectRow_($query);
		if($row) return $queryObj->_createObject($row); 
		return false;
	}
	
	/**
	 * @return array
	 */
	public function getDatabases(){
		return $this->doSelectValues('');
	}
	
	/**
	 * @return array
	 */
	public function getTables(){
		return $this->doSelectValues("SELECT name FROM sqlite_master WHERE type='table' AND name!='sqlite_sequence'");
	}
	
	
	/**
	 * @return void
	 */
	public function truncate($tableName){
		$this->doUpdate('DELETE FROM '.$this->formatTable($tableName));
	}

	protected function insertOrReplaceMultiple($keyword,$tableName,$nbValues,$data,$cols=NULL){
		$query=$keyword.' INTO '.$this->formatTable($tableName);
		if(!empty($cols)) $query.=' (`'.implode('`,`',$cols).'`)';
		$query.=' VALUES (?'.str_repeat(',?',$nbValues-1).')';
		
		return $this->prepareStatement($query,function(&$statement) use(&$data){
			foreach($data as &$values){
				if(is_object($values)) $values=array_values($values->_getData());
				$statement->execute($values);
			}
		});
	}
	
	/**
	 * @return mixed
	 */
	public function tableExist($tableName){
		return $this->doSelectValue("SELECT 1 FROM  sqlite_master WHERE type='table' AND name=".$this->escape($tableName));
	}
}
DBSQLite::$DEFAULT_FLAGS=SQLITE3_OPEN_READWRITE/*#if DEV */ | SQLITE3_OPEN_CREATE/*#/if*/;