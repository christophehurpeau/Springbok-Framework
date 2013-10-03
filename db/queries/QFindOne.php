<?php
/**
 * return one model or false
 */
class QFindOne extends QFind{
	/**
	 * @return SModel
	 */
	public function fetch(){
		$this->limit1();
		/*$row=$this->_db->doSelectRow_($query);
		if($row){
			$obj=$this->_createObject($row);
			$this->_afterQuery_obj($obj);
			return $obj;
		}*/
		$obj=$this->_db->doSelectObject($this->_toSQL(),$this,$this->queryResultFields);
		if($obj){
			$this->_afterQuery_obj($obj);
			return $obj;
		}
		
		return false;
	}
	
	/**
	 * @return array
	 */
	public function toArray(){
		$res=$this->fetch();
		return $res===false?$res:$res->toArray();
	}
	
	/**
	 * @return SModel
	 */
	public function mustFetch(){
		$res=$this->fetch();
		if($res===false) notFound();
		return $res;
	}
	
	
	/** @deprecated */
	public function notFoundIfFalse(){
		return $this->mustFetch();
	}
}