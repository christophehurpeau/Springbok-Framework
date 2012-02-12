<?php
include_once __DIR__.DS.'AQuery.php';
/**
 * INSERT [LOW_PRIORITY | DELAYED] [IGNORE]
    [INTO] tbl_name [(col_name,...)]
    SELECT ...
 */
class QInsertSelect extends AQuery{
	private $start='INSERT',$query,$cols=false,$createdField;

	public function __construct($modelName,$createdField=null){
		parent::__construct($modelName);
		$this->createdField=$createdField;
	}
	
	public function &cols($cols){
		$this->cols=&$cols;
		return $this;
	}
	
	public function &query($query){
		$this->query=&$query;
		return $this;
	}
	
	public function &ignore(){
		$this->start='INSERT IGNORE';
		return $this;
	}
	public function &replace(){
		$this->start='REPLACE';
		return $this;
	}
	
	public function _toSQL(){
		$modelName=$this->modelName;
		$sql=$this->start.' INTO '.$modelName::_fullTableName();
		if(!empty($this->cols)) $sql.=' ('.implode(',',array_map(array($this->_db,'formatField'),is_string($this->cols)?explode(',',$this->cols):$this->cols)).')';
		return $sql.' '.$this->query->_toSQL($this->_db);
	}

	public function &execute(){
		$modelName=$this->modelName;
		$res=$this->_db->doUpdate($this->_toSQL());
		return $res;
	}
}