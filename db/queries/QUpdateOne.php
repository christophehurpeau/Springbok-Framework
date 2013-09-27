<?php
/**
 * Update only one row
 */
class QUpdateOne extends QUpdate{
	/**
	 * @return int number of affected rows
	 */
	public function execute(){
		$this->limit1();
		$res=$this->_db->doUpdate($this->_toSQL());
		return $res;
	}
}
