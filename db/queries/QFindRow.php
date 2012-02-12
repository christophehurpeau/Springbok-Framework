<?php
class QFindRow extends QFind{
	public function &execute(){
		$res=$this->_db->doSelectRow($this->_toSQL());
		return $res;
	}
}