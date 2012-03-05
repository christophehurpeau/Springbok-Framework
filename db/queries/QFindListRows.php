<?php
class QFindListRows extends QFind{
	public function &execute(){
		$res=$this->_db->doSelectListRows($this->_toSQL());
		return $res;
	}
}