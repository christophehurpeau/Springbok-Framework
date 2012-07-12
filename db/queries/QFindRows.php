<?php
class QFindRows extends QFind{
	protected static $FORCE_ALIAS=true;
	public function execute(){
		$res=$this->_db->doSelectRows($this->_toSQL());
		
		if($this->calcFoundRows===true){
			if($res)  $this->calcFoundRows=$this->_db->doSelectValue('SELECT FOUND_ROWS()');
			else $this->calcFoundRows=0;
		}
		return $res;
	}
	
	public function callback($callback){
		$this->_db->doSelectRowsCallback($this->_toSQL(),$callback);
	}
	
	public function calcFoundRows(){
		$this->calcFoundRows=true;
		return $this;
	}
	
	public function hasCalcFoundRows(){
		return $this->calcFoundRows;
	}
	
	public function foundRows(){
		return $this->calcFoundRows;
	}
	
}