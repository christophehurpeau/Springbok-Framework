<?php
class QFindRows extends QFind{
	protected static $FORCE_ALIAS=true;
	private $groupResBy;
	
	public function execute(){
		$res=$this->_db->doSelectRows($this->_toSQL());
		
		if($this->calcFoundRows===true){
			if($res)  $this->calcFoundRows=$this->_db->doSelectValue('SELECT FOUND_ROWS()');
			else $this->calcFoundRows=0;
		}
		
		if($res){
			if($this->groupResBy!==null){
				$grbf=$this->groupResBy;
				$finalRes=array();
				foreach($res as $key=>&$row) $finalRes[$row[$grbf]][$key]=$row;
				$res=$finalRes;
			}
		}
		return $res;
	}
	
	public function callback($callback){
		$this->_db->doSelectRowsCallback($this->_toSQL(),$callback);
	}
	
	public function calcFoundRows(){
		$this->calcFoundRows=true;
		return $this;
	}
	
	public function hasCalcFoundRows(){
		return $this->calcFoundRows;
	}
	
	public function foundRows(){
		return $this->calcFoundRows;
	}
	
	public function groupResBy($field){
		$this->groupResBy=$field;
		return $this;
	}
}