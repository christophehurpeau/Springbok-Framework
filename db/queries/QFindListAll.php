<?php
/**
 * Return an associated array of models
 */
class QFindListAll extends QFindAll{
	
	/**
	 * @return array
	 */
	public function fetch(){
		$res=$this->_db->doSelectListObjects($this->_toSQL(),$this,$this->queryResultFields);
		
		if($res){
			if($this->calcFoundRows===true) $this->calcFoundRows=(int)$this->_db->doSelectValue('SELECT FOUND_ROWS()');
			$this->_afterQuery_objs($res);
		}elseif($this->calcFoundRows===true) $this->calcFoundRows=0;
		return $res;
	}
	
	/**
	 * @return QFindListAll|self
	 */
	public function calcFoundRows(){
		$this->calcFoundRows=true;
		return $this;
	}
	
	/**
	 * @return QFindListAll|self
	 */
	public function noCalcFoundRows(){
		$this->calcFoundRows=null;
		return $this;
	}
	
	/**
	 * @return bool
	 */
	public function hasCalcFoundRows(){
		return $this->calcFoundRows;
	}
	
	/**
	 * @return int
	 */
	public function foundRows(){
		return $this->calcFoundRows;
	}
}
