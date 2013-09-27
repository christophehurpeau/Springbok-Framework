<?php
/**
 * INSERT [LOW_PRIORITY | DELAYED] [IGNORE]
	[INTO] tbl_name [(col_name,...)]
	VALUES ({expr | DEFAULT},...),(...),...
	[ ON DUPLICATE KEY UPDATE col_name=expr, ... ]
 */
include_once __DIR__.DS.'AQuery.php';
class QInsert extends AQuery{
	private $cols=false,$values,$ignore=false,$onDuplicateKeyUpdate,$createdField,$createdByField;
	protected $keyword='INSERT';
	
	/**
	 * @param string
	 * @param string|null
	 * @param string|null
	 */
	public function __construct($modelName,$createdField=null,$createdByField=null){
		parent::__construct($modelName);
		$this->createdField=$createdField;
		$this->createdByField=$createdByField;
	}
	
	/**
	 * @param string|array
	 * @return QInsert|self
	 */
	public function cols($cols){
		$this->cols=is_string($cols)?explode(',',$cols):$cols;
		return $this;
	}
	
	/**
	 * @param array a list of key=>values
	 * @return QInsert|self
	 */
	public function set($data){
		$this->data($data);
		return $this;
	}
	
	/**
	 * @param array a list of key=>values
	 * @return QInsert|self
	 */
	public function data($data){
		$this->cols=array_keys($data);
		$this->values=array($data);
		return $this;
	}
	
	/**
	 * @param array an array of arrays: list of key=>values
	 * @return QInsert|self
	 */
	public function datas($datas){
		reset($datas);
		$this->cols=array_keys(current($datas));
		$this->values=$datas;
		return $this;
	}
	
	/**
	 * @param array only values
	 * @return QInsert|self
	 */
	public function values($values){
		$this->values=array($values);
		return $this;
	}
	
	/**
	 * @param array array of array values
	 * @return QInsert|self
	 */
	public function mvalues($values){
		$this->values=$values;
		return $this;
	}
	
	/**
	 * @return QInsert|self
	 */
	public function ignore(){
		$this->ignore=true;
		return $this;
	}
	
	/**
	 * @param string|array SQL or array of fields=>values
	 * @return QInsert|self
	 */
	public function orUpdate($onDuplicateKeyUpdate){
		if(is_array($onDuplicateKeyUpdate)){
			$sql='';
			foreach($onDuplicateKeyUpdate as $key=>$value){
				$sql.=$this->_db->formatField($key).'=';
				if($value===NULL) $sql.='NULL';
				elseif(is_int($value) || is_float($value)) $sql.=$value;
				elseif(is_bool($value)) $sql.=($value===true?'""':'NULL');
				else $sql.=$this->_db->escape($value);
				
				$sql.=',';
			}
			$this->onDuplicateKeyUpdate=substr($sql,0,-1);
		}else $this->onDuplicateKeyUpdate=$onDuplicateKeyUpdate;
		return $this;
	}
	
	/**
	 * @return string
	 */
	public function _toSQL(){
		$modelName=$this->modelName;
		$sql=$this->keyword.' '; $hasCreatedField=$this->createdField!==null && $this->cols!==false && !in_array($this->createdField,$this->cols);
		$hasCreatedByField=$this->createdByField!==null && $this->cols!==false && !in_array($this->createdByField,$this->cols);
		if($this->ignore!==false) $sql.='IGNORE ';
		$sql.='INTO '.$modelName::_fullTableName();
		if($this->cols !==false){
			$sql.=' (';
			if(!empty($this->cols)){
				$sql.=implode(',',array_map(array($this->_db,'formatField'),$this->cols));
				if($hasCreatedField||$hasCreatedByField) $sql.=',';
			}
			if($hasCreatedField){ $sql.=$this->_db->formatField($this->createdField); if($hasCreatedByField) $sql.=','; }
			if($hasCreatedByField){ $sql.=$this->_db->formatField($this->createdByField); }
			$sql.=')';
		}
		$sql.= ' VALUES ';
		foreach($this->values as $values){
			if(empty($values)){
				if($hasCreatedField) $sql.='(NOW()),';
				else $sql.='(),';
			}else{
				$sql.='(';
				foreach($values as $key=>$value){
					if($value===NULL) $sql.='NULL';
					elseif(is_int($value) || is_float($value)) $sql.=$value;
					elseif(is_bool($value)) $sql.=($value===true?'""':'NULL');
					elseif(is_array($value)) $sql.=$value[0];
					else $sql.=$this->_db->escape($value);
					
					$sql.=',';
				}
				if($hasCreatedField) $sql.='NOW(),';
				if($hasCreatedByField){ $connected=CSecure::connected(null); $sql.=($connected===null?'NULL,':$this->_db->escape(CSecure::connected()).','); }
				$sql=substr($sql,0,-1).'),';
			}
		}
		$sql=substr($sql,0,-1);
		if($this->onDuplicateKeyUpdate !==null) $sql.=' ON DUPLICATE KEY UPDATE '.$this->onDuplicateKeyUpdate;
		return $sql;
	}
	
	/**
	 * @return int|mixed
	 */
	public function execute(){
		if(empty($this->values)) return false;
		$modelName=$this->modelName;
		$res=$this->_db->doUpdate($this->_toSQL());
		if($res){
			if($modelName::$__modelInfos['isAI'])
				$res=(int)$this->_db->lastInsertID();
			/*if($modelName::$__cacheable)
				CCache::get('models')->delete($modelName);*/
		}
		return $res;
	}
}