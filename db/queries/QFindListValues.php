<?php
include_once __DIR__.DS.'QFindAll.php';
class QFindListValues extends QFindAll{
	public function &execute(){
		$res=$this->_db->doSelectListObjects($this->_toSQL(),$this,$this->queryResultFields);
		
		if($res){
			if($this->calcFoundRows===true) $this->calcFoundRows=(int)$this->_db->doSelectValue('SELECT FOUND_ROWS()');
			$this->_afterQuery_objs($res);
		}elseif($this->calcFoundRows===true) $this->calcFoundRows=0;
		return $res;
	}
	
	public function &calcFoundRows(){
		$this->calcFoundRows=true;
		return $this;
	}
	public function &noCalcFoundRows(){
		$this->calcFoundRows=null;
		return $this;
	}
	
	public function &hasCalcFoundRows(){
		return $this->calcFoundRows;
	}
	
	public function &foundRows(){
		return $this->calcFoundRows;
	}
}
