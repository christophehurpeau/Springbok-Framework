<?php
/**
 * Create a Count Query
 */
class QCount extends QFindOne{
	private $countField='1';
	
	/**
	 * @param string
	 * @param bool
	 * @return QCount
	 */
	public function setCountField($field,$isDistinct=false){
		$this->countField=($isDistinct?'DISTINCT ':'').$this->formatField($field,false);
		return $this;
	}
	
	/**
	 * @return int
	 */
	public function fetch(){
		$this->setFields(array('COUNT('.$this->countField.')'));
		$query=$this->_toSQL();//debugVar($query);
		$res=$this->_db->doSelectValue($query);
		if($res!==false) $res=(int)$res;
		return $res;
	}
}	