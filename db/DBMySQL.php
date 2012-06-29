<?php
/* http://dev.mysql.com/doc/refman/5.6/en/integer-types.html */
mysqli_report(MYSQLI_REPORT_STRICT);
class DBMySQL extends DBSql{
	public function _getType(){return 'MySQL';}
	
	public function connect(){
		$this->_connect=new MySQLi($this->_config['host'],$this->_config['user'],$this->_config['password'],$this->_config['dbname'],$this->_config['port']);
		//if($this->_connect->connect_errno) throw new DBException('Unable to connect: '.$this->_connect->connect_error); => WARNING 
		$this->_connect->query('SET NAMES utf8');
	}
	public function getVersion(){return $this->_connect->server_version;}
	public function lastInsertID($name=null) {
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
	
	public function beginTransaction(){ $this->_connect->autocommit(false); }
	public function commit(){ $this->_connect->commit(); $this->_connect->autocommit(true); }
	public function rollBack(){ $this->_connect->rollback(); $this->_connect->autocommit(true); }
	
	public function escape($string){
		return '\''.$this->_connect->real_escape_string($string).'\'';
	}
	
	
	private function _query($query,$internalCalling=0){
		$r=$this->_connect->query($query);
		/* if(!DBSchemaProcessing::$isProcessing) serviceUnavailable(_tC('The server is currently overloaded')); */
		if($this->_connect->errno){
			if($internalCalling < 5){
				if($this->_connect->errno==1213){
					CLogger::get('mysql-deadlocks')->log($query);
					return $this->_query($query,$internalCalling+1);
				}elseif($this->_connect->errno==1205){
					CLogger::get('mysql-lockwait')->log($query);
					return $this->_query($query,$internalCalling+1);
				}elseif($internalCalling===0 && $this->_connect->errno==2006){
					if($this->ping())
						return $this->_query($query,$internalCalling+1);
				}
			}
			throw new DBException('Query error ['.$internalCalling.'] ('.$this->_connect->errno.'): '.$this->_connect->error,$query);
		}
		return $r;
	}
	private function _query_($query,$fields,$internalCalling=0){
		if(($stmt=$this->_connect->prepare($query))===false){
			if($internalCalling===0 && $this->_connect->errno==2006){
				if($this->ping())
					return $this->_query_($query,$fields,$internalCalling+1);
			}
			throw new DBException('Prepare statement failed ('.$this->_connect->errno.'): '.$this->_connect->error,$query);
		}
		if($stmt->execute()===false){
			throw new DBException('Execute statement failed ('.$this->_connect->errno.'): '.$this->_connect->error,$query);
		}
		//$stmt->store_results();
		$r = call_user_func_array(array(&$stmt,'bind_result'),$fields);
		//$stmt->free_result();
		if($r===false) throw new DBException('Unable to bind result',$query);
		return $stmt;
	}
	
	public function /* DEV */_/* /DEV */doUpdate($query){
		$r=$this->_query($query);
		if($r) return $this->_connect->affected_rows;
		return false;
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
	public function /* DEV */_/* /DEV */doSelectSql($query){
		$r=$this->_query($query); $fields=$this->getFieldsSQL($r); $res=array();
		while($row=$r->fetch_row()) $res[]=$row;
		$r->close(); return array('res'=>&$res,'fields'=>&$fields);
	}
	public function /* DEV */_/* /DEV */doSelectSqlCallback($query,$callback,$callbackFields){
		$r=$this->_query($query);
		$callbackFields($this->getFieldsSQL($r));
		while($row=$r->fetch_row()) $callback($row);
		$r->close();
	}
	
	/* NORMAL SELECT */
	public function /* DEV */_/* /DEV */doSelectRows($query){
		$r=$this->_query($query); $res=array();
		//$res=$r->fetch_all(MYSQLI_ASSOC);
		while($row=$r->fetch_assoc()) $res[]=$row;
		$r->close(); return $res;
	}
	public function /* DEV */_/* /DEV */doSelectRowsCallback($query,$callback){
		$r=$this->_query($query);
		while($row=$r->fetch_assoc()) $callback($row);
		$r->close();
	}
	
	
	public function /* DEV */_/* /DEV */doSelectRows_($query){
		$r=$this->_query($query); $res=array();
		//$res=$r->fetch_all(MYSQLI_NUM);
		while($row=$r->fetch_row()) $res[]=$row;
		$r->close(); return $res;
	}
	public function /* DEV */_/* /DEV */doSelectRowsCallback_($query,$callback){
		$r=$this->_query($query);
		while($row=$r->fetch_row()) $callback($row);
		$r->close();
	}
	
	
	public function /* DEV */_/* /DEV */doSelectRow($query){
		$r=$this->_query($query);
		$res=$r->fetch_assoc();
		$r->close(); return $res;
	}
	public function /* DEV */_/* /DEV */doSelectRow_($query){
		$r=$this->_query($query);
		$res=$r->fetch_row();
		$r->close(); return $res;
	}
	public function /* DEV */_/* /DEV */doSelectValues($query,$numCol=0){
		/*$r=$this->_query($query); $res=array();
		while($row=$r->fetch_row()) $res[]=$row[$numCol];
		$r->close(); return $res;*/
		$value=false; $res=array();
		$fields=array(&$value);
		$r=$this->_query_($query,$fields);
		while($r->fetch()) $res[]=$value;
		$r->close(); return $res;
	}
	public function /* DEV */_/* /DEV */doSelectValuesCallback($query,$callback,$numCol=0){
		/*$r=$this->_query($query); $res=array();
		while($row=$r->fetch_row()) $callback($row[$numCol]);
		$r->close();*/
		$value=false; $res=array();
		$fields=array(&$value);
		$r=$this->_query_($query,$fields);
		while($r->fetch()) $callback($value);
		$r->close();
	}
	public function /* DEV */_/* /DEV */doSelectValue($query,$numCol=0){
		$value=false;
		$fields=array(&$value);
		$r=$this->_query_($query,$fields);
		if(!$r->fetch()) $value=false;
		$r->close(); return $value;
		/*
		$r=$this->_query($query);
		if($row=$r->fetch_row()) $res=$row[$numCol]; else $res=false;
		$r->close(); return $res;*/
	}
	public function /* DEV */_/* /DEV */doSelectExist($query){
		$r=$this->_query($query);
		if($row=$r->fetch_row()) $res=true; else $res=false;
		$r->close(); return $res;
	}
	public function /* DEV */_/* /DEV */doSelectListRows($query){
		$r=$this->_query($query); $res=array();
		while($row=$r->fetch_assoc()) $res[current($row)]=$row;
		$r->close(); return $res;
	}
	public function /* DEV */_/* /DEV */doSelectListRows_($query){
		$r=$this->_query($query); $res=array();
		while($row=$r->fetch_row()) $res[$row[0]]=$row;
		$r->close(); return $res;
	}
	public function /* DEV */_/* /DEV */doSelectListValue($query){
		$key;$value;
		$fields=array(&$key,&$value);
		$r=$this->_query_($query,$fields); $res=array();
		while($row=$r->fetch()) $res[$key]=$value;
		$r->close(); return $res;
	}
	
	/* SELECT USING PREPARE STATEMENT */
	
	public function /* DEV */_/* /DEV */doSelectObjects($query,$queryObj,$fields){
		$r=$this->_query_($query,$fields); $res=array();
		while($r->fetch()) $res[]=$queryObj->_createObj();
		$r->close(); return $res;
	}
	public function /* DEV */_/* /DEV */doSelectObjectsCallback($query,$queryObj,$fields,$callback){
		$r=$this->_query_($query,$fields);
		$currentConnect=$this->_connect; $this->connect();//open new connect
		while($r->fetch()) $callback($queryObj->_createObj());
		$r->close();
	}
	
	public function /* DEV */_/* /DEV */doSelectAssocObjects($query,$queryObj,$fields,$tabResKey){
		$r=$this->_query_($query,$fields); $res=array();
		while($r->fetch()){
			$obj=$queryObj->_createObj();
			$res[$obj->$tabResKey]=$obj;
		}
		$r->close();
		return $res;
	}
	public function /* DEV */_/* /DEV */doSelectListObjects($query,$queryObj,$fields){
		$r=$this->_query_($query,$fields); $res=array();
		while($r->fetch()){
			$obj=$queryObj->_createObj();
			$res[$fields[0]]=$obj;
		}
		$r->close();
		return $res;
	}
	
	public function /* DEV */_/* /DEV */doSelectObject($query,$queryObj,$fields){
		$r=$this->_query_($query,$fields);
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
		/* DEV *//*
		$t=microtime(true);
		/* /DEV *//*
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
		/* DEV *//*
		$t=microtime(true) - $t;
		$this->_logQuery(array('query'=>$query,'params'=>$params,'result'=>$result,'time'=>$t));
		/* /DEV *//*
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