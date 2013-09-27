<?php
/**
 * return a value or false
 */
class QFindValue extends QFind{
	/**
	 * @return mixed
	 */
	public function execute(){
		$this->limit1();
		$res=$this->_db->doSelectValue($this->_toSQL());
		return $res;
	}
	
	/**
	 * @return mixed
	 */
	public function notFoundIfFalse(){
		$res=$this->execute();
		if($res===false) notFound();
		return $res;
	}
	
	/**
	 * @param string
	 * @param array
	 * @return QFindValue|self
	 */
	public function with($with,$options=array()){
		$options+=array('fields'=>false,'join'=>true);
		$this->_addWithToQuery($with,$options);
		return $this;
	}
}