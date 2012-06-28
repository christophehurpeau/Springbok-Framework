<?php
class QFindRow extends QFind{
	public function execute(){
		return $this->_db->doSelectRow($this->_toSQL());
	}
}