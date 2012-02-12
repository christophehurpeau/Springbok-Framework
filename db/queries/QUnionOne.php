<?php
class QUnionOne extends QUnion{
	public function &execute(){
		$this->limit1();
		$res=$this->_db->doSelectRow($this->_toSQL(),$this);
		return $res;
	}
}