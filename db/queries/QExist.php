<?php
class QExist extends QFindOne{
	public function &execute(){
		$this->limit1();
		if(!isset($this->fields)) $this->fields=array(1);
		$res=$this->_db->doSelectValue($this->_toSQL());
		return $res;
	}
	
	public function &with($with,$options=array()){ $options+=array('fields'=>false,'forceJoin'=>true); self::_addWith($this->with,$with,$options,$this->modelName); return $this;}
}