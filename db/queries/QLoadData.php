<?php
/**
 * LOAD DATA [LOW_PRIORITY | CONCURRENT] [LOCAL] INFILE 'file_name.txt'
    [REPLACE | IGNORE]
    INTO TABLE tbl_name
    [CHARACTER SET charset_name]
    [FIELDS
        [TERMINATED BY '\t']
        [[OPTIONALLY] ENCLOSED BY '']
        [ESCAPED BY '\\' ]
    ]
    [LINES 
        [STARTING BY '']    
        [TERMINATED BY '\n']
    ]
    [IGNORE number LINES]
    [(col_name,...)]
 */
include_once __DIR__.DS.'AQuery.php';
class QLoadData extends AQuery{
	protected $fileName,$replace=false,$ignore=false,$characterSet,$fieldsTerminatedBy,$fieldsEnclosedBy,$fieldsEscapedBy,$linesStatingBy,$linesTerminatedBy,$ignoreLines;
	
	public function fileName($fileName){
		$this->fileName=$fileName;
		return $this;
	}
	public function replace(){
		$this->replace=true;
		return $this;
	}
	public function characterSet($characterSet){
		$this->characterSet=$characterSet;
		return $this;
	}
	public function fieldsTerminatedBy($terminatedBy){
		$this->fieldsTerminatedBy=$terminatedBy;
		return $this;
	}
	public function fieldsEnclosedBy($enclosedBy){
		$this->fieldsEnclosedBy=$enclosedBy;
		return $this;
	}
	public function fieldsEscapedBy($escapedBy){
		$this->escapedBy=$escapedBy;
		return $this;
	}
	public function linesStatingBy($statingBy){
		$this->linesStatingBy=$statingBy;
		return $this;
	}
	public function linesTerminatedBy($terminatedBy){
		$this->linesTerminatedBy=$terminatedBy;
		return $this;
	}
	
	public function ignoreFirstLine(){
		$this->ignoreLines=1;
		return $this;
	}
	
	public function _toSQL(){
		$modelName=$this->modelName;
		$sql='LOAD DATA '/* LOCAL */.'INFILE '.$this->_db->escape($this->fileName).' ';
		if($this->replace) $sql.='REPLACE ';
		elseif($this->ignore) $sql.='IGNORE ';
		$sql.='INTO TABLE '.$modelName::_fullTableName();
		if(isset($this->characterSet)) $sql.=' CHARACTER SET '.$this->characterSet;
		if(isset($this->fieldsTerminatedBy) || isset($this->fieldsEnclosedBy) || isset($this->fieldsEscapedBy)){
			$sql.=' FIELDS';
			if(isset($this->fieldsTerminatedBy)){ $sql.=' TERMINATED BY \''.$this->fieldsTerminatedBy.'\''; }
			if(isset($this->fieldsEnclosedBy)){ $sql.=' ENCLOSED BY \''.$this->fieldsEnclosedBy.'\''; }
			if(isset($this->fieldsEscapedBy)){ $sql.=' ESCAPED BY \''.$this->fieldsEscapedBy.'\''; }
		}
		if(isset($this->linesStatingBy) || isset($this->linesTerminatedBy)){
			$sql.=' LINES';
			if(isset($this->linesStatingBy)){ $sql.=' STARTING BY \''.$this->linesStatingBy.'\''; }
			if(isset($this->linesTerminatedBy)){ $sql.=' TERMINATED BY \''.$this->linesTerminatedBy.'\''; }
		}
		if(isset($this->ignoreLines))
			$sql.=' IGNORE '.$this->ignoreLines.' LINES';
		return $sql;
	}

	public function execute(){
		$modelName=$this->modelName;
		$res=$this->_db->doUpdate($this->_toSQL());
		return $res;
	}
}