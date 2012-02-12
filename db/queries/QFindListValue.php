<?php
class QFindListValue extends QFind{
	public function &execute(){
		$res=$this->_db->doSelectListValue($this->_toSQL());
		return $res;
	}
}