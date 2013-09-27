<?php
/**
 * Return an array of rows
 */
class QFindListRows extends QFind{
	protected static $FORCE_ALIAS=true;
	
	/**
	 * @return array
	 */
	public function execute(){
		$res=$this->_db->doSelectListRows($this->_toSQL());
		return $res;
	}
}