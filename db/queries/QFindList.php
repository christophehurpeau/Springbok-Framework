<?php
/**
 * Return a pair key-value array.
 */
class QFindList extends QFind{
	/**
	 * @return array
	 */
	public function execute(){
		$res=$this->_db->doSelectListValue($this->_toSQL());
		return $res;
	}
}