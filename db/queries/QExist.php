<?php
/**
 * Return a boolean
 */
class QExist extends QFindOne{
	/**
	 * @return bool
	 */
	public function execute(){
		$this->limit1();
		if($this->fields[0]===null) $this->fields[0]=array(1);
		$res=$this->_db->doSelectExist($this->_toSQL());
		return $res;
	}
	
	/**
	 * Add a relation in this query
	 * 
	 * @param string name of the relation
	 * @param array
	 * @return QExist|self
	 */
	public function with($with,$options=array()){
		$options+=array('fields'=>false,'join'=>true);
		$this->_addWithToQuery($with,$options);
		return $this;
	}
}