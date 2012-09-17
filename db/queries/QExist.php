<?php
class QExist extends QFindOne{
	public function execute(){
		$this->limit1();
		if($this->fields[0]===null) $this->fields[0]=array(1);
		$res=$this->_db->doSelectExist($this->_toSQL());
		return $res;
	}
	
	public function with($with,$options=array()){
		$options+=array('fields'=>false,'join'=>true);
		$this->_addWithToQuery($with,$options);
		return $this;
	}
}