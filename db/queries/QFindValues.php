<?php
class QFindValues extends QFind{
	public function &execute(){
		$res=$this->_db->doSelectValues($this->_toSQL());
		return $res;
	}
	
	
	public function callback($callback){
		$this->_db->doSelectValuesCallback($this->_toSQL(),$callback);
	}
}