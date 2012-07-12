<?php
class QFindRow extends QFind{
	protected static $FORCE_ALIAS=true;
	public function execute(){
		return $this->_db->doSelectRow($this->_toSQL());
	}
}