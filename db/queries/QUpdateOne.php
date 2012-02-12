<?php
class QUpdateOne extends QUpdate{
	public function &execute(){
		$this->limit1();
		$res=$this->_db->doUpdate($this->_toSQL());
		return $res;
	}
}
