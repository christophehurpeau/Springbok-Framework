<?php
class QFindList extends QFind{
	public function &execute(){
		$res=$this->_db->doSelectListValue($this->_toSQL());
		return $res;
	}
}