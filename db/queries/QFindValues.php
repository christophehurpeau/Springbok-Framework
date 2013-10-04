<?php
/**
 * return an array of values
 */
class QFindValues extends QFind{
	private $tabResKey;
	
	/**
	 * @return array
	 */
	public function fetch(){
		if($this->tabResKey !== null) return $this->_db->doSelectAssocValues($this->_toSQL(),$this->tabResKey);
		return $this->_db->doSelectValues($this->_toSQL());
	}
	
	/**
	 * @return void
	 */
	public function forEachValue($callback){
		$this->_db->doSelectValuesCallback($this->_toSQL(),$callback);
	}
	
	/** @deprecated */
	public function callback($callback){
		return $this->forEachValues($callback);
	}
	
	/**
	 * @param string
	 * @return QFindValues|self
	 */
	public function tabResKey($field='id'){
		$this->tabResKey=$field;
		return $this;
	}
}