<?php
class QCount extends QFindOne{
	private $countField='1';
	public function setCountField($field,$isDistinct=false){
		$this->countField=($isDistinct?'DISTINCT ':'').$this->formatField($field,false);
		return $this;
	}
	
	public function execute(){
		$this->setFields(array('COUNT('.$this->countField.')'));
		$query=$this->_toSQL();//debugVar($query);
		$res=$this->_db->doSelectValue($query);
		if($res!==false) $res=(int)$res;
		return $res;
	}
}	