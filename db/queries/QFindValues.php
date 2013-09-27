<?php
/**
 * return an array of values
 */
class QFindValues extends QFind{
	private $tabResKey;
	
	/**
	 * @return array
	 */
	public function execute(){
		if($this->tabResKey !== null) return $this->_db->doSelectAssocValues($this->_toSQL(),$this->tabResKey);
		return $this->_db->doSelectValues($this->_toSQL());
	}
	
	/**
	 * @return void
	 */
	public function callback($callback){
		$this->_db->doSelectValuesCallback($this->_toSQL(),$callback);
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