<?php
/**
 * Create a DELETE Query with only one row deletable
 */
class QDeleteOne extends QDelete{
	/**
	 * @return number of affected rows
	 */
	public function execute(){
		$this->limit1();
		return $this->_db->doUpdate($this->_toSQL());
	}
}
