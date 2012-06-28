<?php
class QFindValue extends QFind{
	public function execute(){
		$this->limit1();
		$res=$this->_db->doSelectValue($this->_toSQL());
		return $res;
	}
	
	public function with($with,$options=array()){ $options+=array('fields'=>false,'forceJoin'=>true); $this->_addWithToQuery($with,$options); return $this;}
}