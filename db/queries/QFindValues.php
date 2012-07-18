<?php
class QFindValues extends QFind{
	private $tabResKey;
	public function execute(){
		if($this->tabResKey !== null) return $this->_db->doSelectAssocValues($this->_toSQL(),$this->tabResKey);
		return $this->_db->doSelectValues($this->_toSQL());
	}
	
	
	public function callback($callback){
		$this->_db->doSelectValuesCallback($this->_toSQL(),$callback);
	}
	
	public function tabResKey($field='id'){
		$this->tabResKey=$field;
		return $this;
	}
}