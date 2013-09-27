<?php
/**
 * Create a DELETE Query with multiple rows deletable
 * 
 */
class QDeleteAll extends QDelete{
	/**
	 * @return int number of affected rows
	 */
	public function execute(){
		return $this->_db->doUpdate($this->_toSQL());
	}
}
