<?php
class QFindListRows extends QFind{
	protected static $FORCE_ALIAS=true;
	public function execute(){
		$res=$this->_db->doSelectListRows($this->_toSQL());
		return $res;
	}
}