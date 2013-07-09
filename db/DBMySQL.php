<?php
/* http://dev.mysql.com/doc/refman/5.5/en/numeric-types.html */
mysqli_report(MYSQLI_REPORT_STRICT);
class DBMySQL extends DBSql{
	public function _getType(){return 'MySQL';}
	
	public function connect(){
		$this->_connect=$this->_createConnection($this->_config);
	}
	
	public function _createConnection($config){
		/*#if DEV */
		try{
		/*#/if*/
			$connect=new MySQLi($config['host'],$config['user'],$config['password'],$config['dbname'],$config['port']);
		/*#if DEV */
		}catch(mysqli_sql_exception $e){
			if($e->getMessage()==="Unknown database '{$this->_config['dbname']}'"){
				$connect=new MySQLi($config['host'],$config['user'],$config['password'],'mysql',$config['port']);
				$connect->query('CREATE DATABASE '.$this->formatTable($config['dbname']));
				$connect=new MySQLi($config['host'],$config['user'],$config['password'],$config['dbname'],$config['port']);
			}else throw $e;
		}
		/*#/if*/
		//if($connect->connect_errno) throw new DBException('Unable to connect: '.$connect->connect_error); => WARNING 
		$connect->query('SET NAMES utf8');
		return $connect;
	}
	
	public function getVersion(){
		return $this->_connect->server_version;
	}
	public function lastInsertID($name=null){
		return (int)$this->_connect->insert_id;
	}
	
	public function close(){
		$this->_connect->close();
		$this->_connect=null;
	}
	
	public function ping(){
		if(!$this->_connect->ping()){
			$this->close();
			$this->connect();
			return true;
		}
		return false;
	}
	
	private $_transactionBegun=0;
	public function beginTransaction(){ if($this->_transactionBegun++ === 0) $this->_connect->autocommit(false); }
	public function commit(){ if(--$this->_transactionBegun === 0){ $this->_connect->commit(); $this->_connect->autocommit(true); } }
	public function rollBack(){ $this->_connect->rollback(); $this->_connect->autocommit(true); }
	
	public function escape($string){
		return '\''.$this->_connect->real_escape_string($string).'\'';
	}
	
	protected function _internal_query($connect,$query,$internalCalling=0){
		$r=$connect->query($query);
		/* if(!DBSchemaProcessing::$isProcessing) serviceUnavailable(_tC('The server is currently overloaded')); */
		if($connect->errno){
			if($internalCalling < 5){
				if($connect->errno==1213){
					CLogger::get('mysql-deadlocks')->log($query);
					return $this->_internal_query($connect,$query,$internalCalling+1);
				}elseif($connect->errno==1205){
					CLogger::get('mysql-lockwait')->log($query);
					return $this->_internal_query($connect,$query,$internalCalling+1);
				}elseif($internalCalling===0 && $this->_connect->errno==2006){
					if($this->ping())
						return $this->_internal_query($connect,$query,$internalCalling+1);
				}
			}
			throw new DBException('Query error ['.$internalCalling.'] ('.$connect->errno.'): '.$connect->error,$query);
		}
		/*#if DEV*/
		if($connect->warning_count){
			$warnings=array();
			$w=$connect->get_warnings();
			do{
				$warnings[]=$w->errno.': '.$w->message;
			}while($w->next());
			throw new DBException('Query warnings: '.print_r($warnings,true),$query);
		}
		/*#/if*/ 
		return $r;
	}
	
	protected function _queryMaster($query){
		return $this->_internal_query($this->_connect,$query);
	}
	protected function _querySlave($query){
		return $this->_internal_query($this->_connect,$query);
	}
	
	protected function _internal_preparedQuery($connect,$query,$fields,$internalCalling=0){
		if(($stmt=$connect->prepare($query))===false){
			if($internalCalling===0 && $connect->errno==2006){
				if($this->ping())
					return $this->_internal_preparedQuery($connect,$query,$fields,$internalCalling+1);
			}
			throw new DBException('Prepare statement failed ('.$connect->errno.'): '.$connect->error,$query);
		}
		if($stmt->execute()===false){
			throw new DBException('Execute statement failed ('.$connect->errno.'): '.$connect->error,$query);
		}
		//$stmt->store_results();
		$r = call_user_func_array(array($stmt,'bind_result'),$fields);
		//$stmt->free_result();
		if($r===false) throw new DBException('Unable to bind result',$query);
		return $stmt;
	}
	
	protected function _preparedQuerySlave($query,$fields){
		return $this->_internal_preparedQuery($this->_connect,$query,$fields);
	}
	
	public function /*#if DEV then _*/doUpdate($query){
		$r=$this->_queryMaster($query);
		if($r) return $this->_connect->affected_rows;
		return false;
	}
	
	public function doMultiQueries($queries){
		$results=array();
		if($this->_connect->multi_query($queries)){
			do{
				if($result=$this->_connect->store_result()){
					$r=array();
					while($row=$result->fetch_row()) $r[]=$row;
					$result->free();
					$results[]=$r;
				}else $results[]=$this->_connect->affected_rows;
			}while($this->_connect->more_results() && $this->_connect->next_result());
		}
		return $results;
	}
	
	
	/* SELECT SQL */
	private function getFieldsSQL($r){
		$fields=$r->fetch_fields();
		foreach($fields as &$field){
			switch($field->type){
				case MYSQLI_TYPE_TINY: case MYSQLI_TYPE_SHORT: case MYSQLI_TYPE_LONG:
				case MYSQLI_TYPE_TIMESTAMP:
				case MYSQLI_TYPE_LONGLONG: case MYSQLI_TYPE_INT24:
					$type='int'; break;
				case MYSQLI_TYPE_DECIMAL:
				case MYSQLI_TYPE_NEWDECIMAL:
				case MYSQLI_TYPE_FLOAT: case MYSQLI_TYPE_DOUBLE:
					$type='float'; break;
				case MYSQLI_TYPE_VAR_STRING: case MYSQLI_TYPE_STRING: case MYSQLI_TYPE_CHAR:
				case MYSQLI_TYPE_BIT:
				case MYSQLI_TYPE_DATE: case MYSQLI_TYPE_TIME: case MYSQLI_TYPE_DATETIME:
				case MYSQLI_TYPE_YEAR: case MYSQLI_TYPE_NEWDATE:
				case MYSQLI_TYPE_ENUM: case MYSQLI_TYPE_SET:
				case MYSQLI_TYPE_TINY_BLOB: case MYSQLI_TYPE_MEDIUM_BLOB: case MYSQLI_TYPE_LONG_BLOB: case MYSQLI_TYPE_BLOB:
				case MYSQLI_TYPE_GEOMETRY:
					$type='string'; break;
				case MYSQLI_TYPE_NULL: $type=NULL; break;
				//MYSQLI_TYPE_INTERVAL
				default: throw new Exception('Unknown type: '.$field['type']);
			}
			$field=array('name'=>$field->name,'type'=>$type);
		}
		return $fields;
	}
	public function /*#if DEV then _*/doSelectSql($query){
		$r=$this->_querySlave($query); $fields=$this->getFieldsSQL($r); $res=array();
		while($row=$r->fetch_row()) $res[]=$row;
		$r->close(); return array('res'=>&$res,'fields'=>&$fields);
	}
	public function /*#if DEV then _*/doSelectSqlCallback($query,$callback,$callbackFields){
		$r=$this->_querySlave($query);
		$r->store_result();
		$callbackFields($this->getFieldsSQL($r));
		while($row=$r->fetch_row()) $callback($row);
		$r->free_result();
		$r->close();
	}
	
	/* NORMAL SELECT */
	public function /*#if DEV then _*/doSelectRows($query){
		$r=$this->_querySlave($query); $res=array();
		//$res=$r->fetch_all(MYSQLI_ASSOC);
		while($row=$r->fetch_assoc()) $res[]=$row;
		$r->close(); return $res;
	}
	public function /*#if DEV then _*/doSelectRowsCallback($query,$callback){
		$r=$this->_querySlave($query);
		//$r->store_result();
		while($row=$r->fetch_assoc()) $callback($row);
		//$r->free_result();
		$r->close();
	}
	
	
	public function /*#if DEV then _*/doSelectRows_($query){
		$r=$this->_querySlave($query); $res=array();
		//$res=$r->fetch_all(MYSQLI_NUM);
		while($row=$r->fetch_row()) $res[]=$row;
		$r->close(); return $res;
	}
	public function /*#if DEV then _*/doSelectRowsCallback_($query,$callback){
		$r=$this->_querySlave($query);
		//$r->store_result();
		while($row=$r->fetch_row()) $callback($row);
		//$r->free_result();
		$r->close();
	}
	
	
	public function /*#if DEV then _*/doSelectRow($query){
		$r=$this->_querySlave($query);
		$res=$r->fetch_assoc();
		$r->close(); return $res;
	}
	public function /*#if DEV then _*/doSelectRow_($query){
		$r=$this->_querySlave($query);
		$res=$r->fetch_row();
		$r->close(); return $res;
	}
	public function /*#if DEV then _*/doSelectValues($query,$numCol=0){
		/*$r=$this->_querySlave($query); $res=array();
		while($row=$r->fetch_row()) $res[]=$row[$numCol];
		$r->close(); return $res;*/
		$value=false; $res=array();
		$fields=array(&$value);
		$r=$this->_preparedQuerySlave($query,$fields);
		while($r->fetch()) $res[]=$value;
		$r->close(); return $res;
	}
	public function /*#if DEV then _*/doSelectValuesCallback($query,$callback,$numCol=0){
		/*$r=$this->_querySlave($query); $res=array();
		while($row=$r->fetch_row()) $callback($row[$numCol]);
		$r->close();*/
		$value=false; $res=array();
		$fields=array(&$value);
		$r=$this->_preparedQuerySlave($query,$fields);
		$r->store_result();
		while($r->fetch()) $callback($value);
		$r->free_result();
		$r->close();
	}
	public function /*#if DEV then _*/doSelectAssocValues($query,$tabResKey){
		$value=false; $res=array();
		$fields=array(&$value);
		$r=$this->_preparedQuerySlave($query,$fields);
		while($r->fetch()) $res[$value]=$value;
		$r->close(); return $res;
	}
	
	
	
	public function /*#if DEV then _*/doSelectValue($query,$numCol=0){
		$value=false;
		$fields=array(&$value);
		$r=$this->_preparedQuerySlave($query,$fields);
		if(!$r->fetch()) $value=false;
		$r->close(); return $value;
		/*
		$r=$this->_querySlave($query);
		if($row=$r->fetch_row()) $res=$row[$numCol]; else $res=false;
		$r->close(); return $res;*/
	}
	public function /*#if DEV then _*/doSelectExist($query){
		$r=$this->_querySlave($query);
		if($row=$r->fetch_row()) $res=true; else $res=false;
		$r->close(); return $res;
	}
	public function /*#if DEV then _*/doSelectListRows($query){
		$r=$this->_querySlave($query); $res=array();
		while($row=$r->fetch_assoc()) $res[current($row)]=$row;
		$r->close(); return $res;
	}
	public function /*#if DEV then _*/doSelectListRows_($query){
		$r=$this->_querySlave($query); $res=array();
		while($row=$r->fetch_row()) $res[$row[0]]=$row;
		$r->close(); return $res;
	}
	public function /*#if DEV then _*/doSelectListValue($query){
		$key;$value;
		$fields=array(&$key,&$value);
		$r=$this->_preparedQuerySlave($query,$fields); $res=array();
		while($row=$r->fetch()) $res[$key]=$value;
		$r->close(); return $res;
	}
	
	/* SELECT USING PREPARE STATEMENT */
	
	public function /*#if DEV then _*/doSelectObjects($query,$queryObj,$fields){
		$r=$this->_preparedQuerySlave($query,$fields); $res=array();
		while($r->fetch()) $res[]=$queryObj->_createObj();
		$r->close(); return $res;
	}
	public function /*#if DEV then _*/doSelectObjectsCallback($query,$queryObj,$fields,$callback){
		$r=$this->_preparedQuerySlave($query,$fields);
		$r->store_result();
		while($r->fetch()) $callback($queryObj->_createObj());
		$r->free_result();
		$r->close();
	}
	
	public function /*#if DEV then _*/doSelectAssocObjects($query,$queryObj,$fields,$tabResKey){
		$r=$this->_preparedQuerySlave($query,$fields); $res=array();
		while($r->fetch()){
			$obj=$queryObj->_createObj();
			$res[$obj->$tabResKey]=$obj;
		}
		$r->close();
		return $res;
	}
	public function /*#if DEV then _*/doSelectListObjects($query,$queryObj,$fields){
		$r=$this->_preparedQuerySlave($query,$fields); $res=array();
		while($r->fetch()){
			$obj=$queryObj->_createObj();
			$res[$fields[0]]=$obj;
		}
		$r->close();
		return $res;
	}
	
	public function /*#if DEV then _*/doSelectObject($query,$queryObj,$fields){
		$r=$this->_preparedQuerySlave($query,$fields);
		if($r->fetch()) $res=$queryObj->_createObj(); else $res=false;
		$r->close();
		return $res;
	}
	
	
	/*
	
	public function &doSelectRows($query,$types=false,$params=false){return $this->doSelect($query,$params,'fetchAll',array(PDO::FETCH_ASSOC));}
	public function &doSelectRow($query,$types=false,$params=false){return $this->doSelect($query,$params,'fetch',array(PDO::FETCH_ASSOC));}
	public function &doSelectRows_($query,$types=false,$params=false){return $this->doSelect($query,$params,'fetchAll',array(PDO::FETCH_NUM));}
	public function &doSelectRow_($query,$types=false,$params=false){return $this->doSelect($query,$params,'fetch',array(PDO::FETCH_NUM));}
	public function &doSelectValues($query,$types=false,$params=false,$numCol=0){return $this->doSelect($query,$params,'fetchAll',array(PDO::FETCH_COLUMN,$numCol));}
	public function &doSelectValue($query,$types=false,$params=false,$numCol=0){return $this->doSelect($query,$params,'fetch',array(PDO::FETCH_COLUMN,$numCol));}
	public function &doSelectListValues($query,$types=false,$params=false){return $this->doSelect($query,$params,'fetchAll',array(PDO::FETCH_ASSOC | PDO::FETCH_UNIQUE));}
	public function &doSelectListValues_($query,$types=false,$params=false){return $this->doSelect($query,$params,'fetchAll',array(PDO::FETCH_NUM | PDO::FETCH_UNIQUE));}
	public function &doSelectListValue($query,$types=false,$params=false){return $this->doSelect($query,$params,'fetchAll',array(PDO::FETCH_KEY_PAIR));}
	 * 
	 * 
	 * 
	public function &doSelect($query,$params,$methodName,$methodParams=array()){
		/*#if DEV *//*
		$t=microtime(true);
		/*#/if*//*
		if($params===false){
			$result=this->_connect->query($query);
			if(!$result)
				throw new DBException('Query failed');
		}else{
			if(!$statement=$this->_connect->prepare($query))
				throw new DBException('Prepare statement failed');
			if($params) $statement->bind_param(str_repeat('s',count($params)),$params);
			if(!$statement->execute())
				throw new DBException('Execute statement failed');
			$result = $stmt->get_result();
			if(!$result)
				throw new DBException('Unable to get result');
		}
		$result=call_user_func_array(array($result,$methodName),$methodParams);
		/*#if DEV *//*
		$t=microtime(true) - $t;
		$this->_logQuery(array('query'=>$query,'params'=>$params,'result'=>$result,'time'=>$t));
		/*#/if*//*
		return $result;
	}
	*/
	
	public function getDatabases(){
		return $this->doSelectValues('SHOW DATABASES');
	}
	public function getTables(){
		return $this->doSelectValues('SHOW TABLES');
	}
	
	public function truncate($table){
		$this->doUpdate('TRUNCATE TABLE '.$this->formatTable($table));
	}

	public function setAutoIncrement($tableName,$value){
		return $this->doUpdate('ALTER TABLE '.$this->formatTable($tableName).' AUTO_INCREMENT='.((int)$value));
	}
}