<?php
class QDeleteAll extends QDelete{
	public function execute(){
		$res=$this->_db->doUpdate($this->_toSQL());
		return $res;
	}
}
