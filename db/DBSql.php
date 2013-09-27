<?php
/**
 * Abstract class for DB SQL Class
 * 
 * @see DBMySQL
 * @see DBSQLite
 */
abstract class DBSql extends DB{
	
	public function __destruct(){
		$this->close();
	}
	
	public abstract function getVersion();
	
	public abstract function beginTransaction();
	public abstract function commit();
	public abstract function rollBack();
	
	
	public abstract function escape($string);

	/*#if DEV */
	public function doSelect($methodName,$methodParams){
		$t=microtime(true);
		$result=call_user_func_array(array($this,'_doSelect'.$methodName),$methodParams);
		$t=microtime(true) - $t;
		$this->_logQuery(array('query'=>$methodParams[0],'result'=>$result,'time'=>$t));
		return $result;
	}
	public function doUpdate($query){
		$t=microtime(true);
		$result=$this->_doUpdate($query);
		$t=microtime(true) - $t;
		$this->_logQuery(array('query'=>$query,'result'=>$result,'time'=>$t));
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
	public function doSelectAssocValues($query,$tabResKey){
		return $this->doSelect('AssocValues',func_get_args());
	}
	public function doSelectValue($query,$numCol=0){
		return $this->doSelect('Value',func_get_args());
	}
	public function doSelectExist($query){
		return $this->doSelect('Exist',func_get_args());
	}
	
	public function doSelectListRows($query){
		return $this->doSelect('ListRows',func_get_args());
	}
	public function doSelectListRows_($query){
		return $this->doSelect('ListRows_',func_get_args());
	}
	public function doSelectListValue($query){
		return $this->doSelect('ListValue',func_get_args());
	}
	public function doSelectObjects($query,$queryObj,$fields){
		return $this->doSelect('Objects',array($query,$queryObj,$fields));
	}
	public function doSelectListObjects($query,$queryObj,$fields){
		return $this->doSelect('ListObjects',array($query,$queryObj,$fields));
	}
	public function doSelectAssocObjects($query,$queryObj,$fields,$tabResKey){
		return $this->doSelect('AssocObjects',array($query,$queryObj,$fields,$tabResKey));
	}
	public function doSelectObject($query,$queryObj,$fields){
		return $this->doSelect('Object',array($query,$queryObj,$fields));
	}
	
	public function doSelectRowsCallback($query,$callback){
		return $this->doSelect('RowsCallback',array($query,$callback));
	}
	public function doSelectRowsCallback_($query,$callback){
		return $this->doSelect('RowsCallback_',array($query,$callback));
	}
	public function doSelectObjectsCallback($query,$queryObj,$fields,$callback){
		return $this->doSelect('ObjectsCallback',array($query,$queryObj,$fields,$callback));
	}
	public function doSelectValuesCallback($query,$callback,$numCol=0){
		return $this->doSelect('ValuesCallback',array($query,$callback,$numCol));
	}
	
	
	/*#/if*/
	
	public abstract function /*#if DEV then _*/doUpdate($query);
	
	public abstract function /*#if DEV then _*/doSelectSql($query);
	public abstract function /*#if DEV then _*/doSelectSqlCallback($query,$callback,$callbackFields);
	
	public abstract function /*#if DEV then _*/doSelectRows($query);
	public abstract function /*#if DEV then _*/doSelectRowsCallback($query,$callback);
	public abstract function /*#if DEV then _*/doSelectRows_($query);
	public abstract function /*#if DEV then _*/doSelectRowsCallback_($query,$callback);
	public abstract function /*#if DEV then _*/doSelectRow($query);
	public abstract function /*#if DEV then _*/doSelectRow_($query);
	public abstract function /*#if DEV then _*/doSelectObjects($query,$queryObj,$fields);
	public abstract function /*#if DEV then _*/doSelectObjectsCallback($query,$queryObj,$fields,$callback);
	public abstract function /*#if DEV then _*/doSelectAssocObjects($query,$queryObj,$fields,$tabResKey);
	public abstract function /*#if DEV then _*/doSelectObject($query,$queryObj,$fields);
	public abstract function /*#if DEV then _*/doSelectValues($query);
	public abstract function /*#if DEV then _*/doSelectValuesCallback($query,$callback,$numCol=0);
	public abstract function /*#if DEV then _*/doSelectValue($query);
	public abstract function /*#if DEV then _*/doSelectListRows($query);
	public abstract function /*#if DEV then _*/doSelectListRows_($query);
	public abstract function /*#if DEV then _*/doSelectListValue($query);
	
	/* QUERIES LOG */
	
	/*#if DEV */
	private $_nbQueries=0,$_queries=array();
	
	public function getQueries(){ return $this->_queries; }
	public function getNbQueries(){ return $this->_nbQueries; }
	
	public function resetQueries(){
		$this->_queries=array();
	}
	
	private function _logQuery($qr){
		$this->_nbQueries++;
		if($this->_nbQueries < 1000){
			$qr['backtrace']=array_slice(debug_backtrace(),1);
			$this->_queries[]=$qr;
			CLogger::get('queries-'.date('Y-m-d-H'))->log($qr['query']);
		}else CLogger::get('queries-'.date('Y-m-d-H'))->log($qr['query']);
	}
	private static function _createQueryWithParams($query,$params){
		if(empty($params)) return $query;
		$i=0;
		return preg_replace_callback('/\?/m',function($matches) use(&$i,&$params){ return '"'.$params[$i++].'"'; },$query);
	}
	/*#/if*/
	
	
	
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
		$db=$this;
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
	
	public abstract function truncate($table);
}
