<?php
/**
 * return a value or false
 */
class QFindValue extends QFind{
	/**
	 * @return mixed
	 */
	public function fetch(){
		$this->limit1();
		return $this->_db->doSelectValue($this->_toSQL());
	}
	
	/**
	 * @return mixed
	 */
	public function mustFetch(){
		$res=$this->execute();
		if($res===false) notFound();
		return $res;
	}
	
	/** @deprecated */
	public function notFoundIfFalse(){
		return $this->mustFetch();
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