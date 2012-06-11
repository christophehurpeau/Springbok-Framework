<?php
class SSqlModelChild extends SSqlModel{
	public function insert(){
		$this->parent_id=$this->insertParent();
		return parent::insert();
	}
	
	public function insertParent(){
		$parent=new self::$__parent;
		$parent->_copyData($this->data);
		return $parent->insert();
	}
}