<?php
class QFindListRows extends QFind{
	public function &execute(){
		$res=$this->_db->doSelectListValues($this->_toSQL());
		return $res;
	}
}