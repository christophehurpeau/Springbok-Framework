<?php
include_once __DIR__.DS.'QFindAll.php';
class QFindListValues extends QFindAll{
	public function &execute(){
		$rows=$this->_db->doSelectListValues_($this->_toSQL());
		//debugVar($rows);
		$res=array();
		if($rows){
			foreach($rows as $key=>&$row)
				$res[$key]=&$this->_createObject($row);
			if($this->calcFoundRows) $this->calcFoundRows=$this->_db->doSelectValue('SELECT FOUND_ROWS()');
			$this->_afterQuery_objs($res);
		}elseif($this->calcFoundRows) $this->calcFoundRows=0;
		return $res;
	}
}
