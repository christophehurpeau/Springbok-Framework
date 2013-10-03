<?php
/**
 * Return a pair key-value array.
 */
class QFindList extends QFind{
	/**
	 * @return array
	 */
	public function fetch(){
		return $this->_db->doSelectListValue($this->_toSQL());
	}
}