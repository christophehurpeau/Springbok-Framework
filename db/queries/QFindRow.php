<?php
/**
 * return one row or false
 * 
 * Be careful ! fech_row returns an array of strings even for ints and floats 
 * 
 * @see http://php.net/manual/en/mysqli-result.fetch-row.php
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