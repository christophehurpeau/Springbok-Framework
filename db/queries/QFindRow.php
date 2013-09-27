<?php
/**
 * return one row or false
 */
class QFindRow extends QFind{
	protected static $FORCE_ALIAS=true;
	
	/**
	 * @return array
	 */
	public function execute(){
		return $this->_db->doSelectRow($this->_toSQL());
	}
}