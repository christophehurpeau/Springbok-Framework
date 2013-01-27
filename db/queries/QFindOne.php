<?php
class QFindOne extends QFind{
	public function execute(){
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
	
	public function toArray(){
		$res=$this->execute();
		return $res===false?$res:$res->toArray();
	}
	
	public function notFoundIfFalse(){
		$res=$this->execute();
		if($res===false) notFound();
		return $res;
	}
}