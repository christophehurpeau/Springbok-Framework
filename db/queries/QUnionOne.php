<?php
class QUnionOne extends QUnion{
	public function execute(){
		$this->limit1();
		return $this->_db->doSelectRow($this->_toSQL(),$this);
	}
}