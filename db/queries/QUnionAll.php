<?php
class QUnionAll extends QUnion{
	public function &execute(){
		$res=$this->_db->doSelectRows($this->_toSQL(),$this);
		if($this->calcFoundRows===true) $this->calcFoundRows=(int)$this->_db->doSelectValue('SELECT FOUND_ROWS()');
		return $res;
	}
	
	public function &calcFoundRows(){
		$this->calcFoundRows=true;
		$this->queries[0]->calcFoundRows();
		return $this;
	}
	
	public function &hasCalcFoundRows(){
		return $this->calcFoundRows;
	}
	
	public function &foundRows(){
		return $this->calcFoundRows;
	}
	
}