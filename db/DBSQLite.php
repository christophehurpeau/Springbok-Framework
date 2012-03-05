<?php
class DBSQLite extends DB{
	public function _getType(){return 'SQLite';}
	
	public function __construct($configName,$config){
		if(!isset($config['flags'])) $config['flags']=SQLITE3_OPEN_READWRITE/* DEV */ | SQLITE3_OPEN_CREATE/* /DEV */;
		parent::__construct($configName,$config);
	}
	
	public function connect(){
		$this->_connect=new SQLite3($this->_config['file'],$this->_config['flags']);
		if(!$this->_connect) throw new DBException('Unable to connect',null);
	}
	
	public function getVersion(){return $this->_conect->version();}
	
	public function lastInsertID($name=null) {
		return $this->_connect->lastInsertRowID();
	}
	public function close(){
		$this->_connect->close();
		$this->_connect=null;
	}
	public function ping(){
		return true;
	}
	
	
	public function beginTransaction(){  }
	public function commit(){  }
	public function rollBack(){  }
	
	public function escape($string){
		return '\''.$this->_connect->escapeString($string).'\'';
	}
	
	private function _query(&$query){
		return $this->_connect->query($query);
	}
	
	public function /* DEV */_/* /DEV */doUpdate($query){
		return $this->_query($query);
	}
	
	private function &getFieldsSQL(&$r){
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
	
	public function /* DEV */_/* /DEV */doSelectSql($query){
		$r=$this->_query($query); $res=array(); $fields=$this->getFieldsSQL($r);
		while($row=$r->fetchArray(SQLITE3_ASSOC)) $res[]=$row;
		$r->close();
		return array('res'=>&$res,'fields'=>&$fields);
	}
	
	public function /* DEV */_/* /DEV */doSelectSqlCallback($query,$callback,$callbackFields){
		$r=$this->_query($query);
		$callbackFields($this->getFieldsSQL($r));
		while($row=$r->fetchArray(SQLITE3_ASSOC)) $callback($row);
		$res=array(); $r->close();
	}
	
	
	public function &/* DEV */_/* /DEV */doSelectRows($query){
		$r=$this->_query($query); $res=array();
		while($row=$r->fetchArray(SQLITE3_ASSOC)) $res[]=$row;
		return $res;
	}
	public function /* DEV */_/* /DEV */doSelectRowsCallback($query,$callback){
		$r=$this->_query($query);
		while($row=$r->fetchArray(SQLITE3_ASSOC)) $callback($row);
	}
	public function &/* DEV */_/* /DEV */doSelectRows_($query){
		$r=$this->_query($query); $res=array();
		while($row=$r->fetchArray(SQLITE3_NUM)) $res[]=$row;
		return $res;
	}
	public function /* DEV */_/* /DEV */doSelectRowsCallback_($query,$callback){
		$r=$this->_query($query);
		while($row=$r->fetchArray(SQLITE3_NUM)) $callback($row);
	}
	
	public function &/* DEV */_/* /DEV */doSelectRow($query){
		// TODO TESTER querySingle($query,true)
		$r=$this->_query($query);
		return $r->fetchArray(SQLITE3_ASSOC);
	}
	public function &/* DEV */_/* /DEV */doSelectRow_($query){
		$r=$this->_query($query);
		$res=$r->fetchArray(SQLITE3_NUM);
		return $res;
	}
	public function &/* DEV */_/* /DEV */doSelectValues($query,$numCol=0){
		$r=$this->_query($query); $res=array();
		while($row=$r->fetchArray(SQLITE3_NUM)) $res[]=$row[$numCol];
		return $res;
	}
	public function /* DEV */_/* /DEV */doSelectValuesCallback($query,$callback,$numCol=0){
		$r=$this->_query($query);
		while($row=$r->fetchArray(SQLITE3_NUM)) $callback($row[$numCol]);
	}
	public function &/* DEV */_/* /DEV */doSelectValue($query){
		//return $this->_connect->querySingle($query);
		$r=$this->_query($query);
		if($row=$r->fetchArray(SQLITE3_NUM)) return $row[0];
		$res=false; return $res;
	}
	public function &/* DEV */_/* /DEV */doSelectExist($query){
		$r=$this->_query($query);
		if($row=$r->fetchArray(SQLITE3_NUM)) $res=true; else $res=false;
		return $res;
	}
	public function &/* DEV */_/* /DEV */doSelectListRows($query){
		$r=$this->_query($query); $res=array();
		while($row=$r->fetchArray(SQLITE3_ASSOC)) $res[current($row)]=$row;
		return $res;
	}
	public function &/* DEV */_/* /DEV */doSelectListRows_($query){
		$r=$this->_query($query); $res=array();
		while($row=$r->fetchArray(SQLITE3_NUM)) $res[$row[0]]=$row;
		return $res;
	}
	public function &/* DEV */_/* /DEV */doSelectListValue($query){
		$r=$this->_query($query); $res=array();
		while($row=$r->fetchArray(SQLITE3_NUM)) $res[$row[0]]=$row[1];
		return $res;
	}
	
	public function &/* DEV */_/* /DEV */doSelectObjects(&$query,&$queryObj,&$fields){
		$rows=$this->doSelectRows_($query); $res=array();
		foreach($rows as $row) $res[]=$queryObj->_createObject($row);
		return $res;
	}
	public function /* DEV */_/* /DEV */doSelectObjectsCallback(&$query,&$queryObj,&$fields,&$callback){
		$rows=$this->doSelectRows_($query);
		foreach($rows as $row) $callback($queryObj->_createObject($row));
	}
	public function /* DEV */_/* /DEV */doSelectListObjects(&$query,&$queryObj,&$fields){
		$rows=$this->doSelectRows_($query); $res=array();
		foreach($rows as $row) $res[$row[0]]=$queryObj->_createObject($row);
		return $res;
	}
	
	public function &/* DEV */_/* /DEV */doSelectAssocObjects(&$query,&$queryObj,&$fields,&$tabResKey){
		$rows=$this->doSelectRows_($query); $res=array();
		foreach($rows as $row){
			$obj=&$queryObj->_createObject($row);
			$res[$obj->{$this->tabResKey}]=$obj;
		}
		return $res;
	}
	
	public function &/* DEV */_/* /DEV */doSelectObject(&$query,&$queryObj,&$fields){
		$row=$this->doSelectRow_($query);
		if($row) $res=$queryObj->_createObject($row); else $res=false;
		return $res;
	}
	
	public function getDatabases(){
		return $this->doSelectValues('');
	}
	public function getTables(){
		return $this->doSelectValues("SELECT name FROM sqlite_master WHERE type='table' AND name!='sqlite_sequence'");
	}
	
	
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
	
	public function tableExist($tableName){
		return $this->doSelectValue("SELECT 1 FROM  sqlite_master WHERE type='table' AND name=".$this->escape($tableName));
	}
}