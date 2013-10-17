<?php
/**
 * return an array of arrays
 * 
 * Be careful ! fech_row returns an array of strings even for ints and floats 
 * 
 * @see http://php.net/manual/en/mysqli-result.fetch-row.php
 */
class QFindRows extends QFind{
	protected static $FORCE_ALIAS=true;
	private $groupResBy;
	
	/**
	 * @return string[]|array
	 */
	public function fetch(){
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
	
	/**
	 * @param function
	 * @return void
	 */
	public function forEachRow($callback){
		$this->_db->doSelectRowsCallback($this->_toSQL(),$callback);
	}
	
	/** @deprecated */
	public function callback($callback){
		return $this->forEachRow($callback);
	}
	
	/**
	 * @return QFindRows|self
	 */
	public function calcFoundRows(){
		$this->calcFoundRows=true;
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
	
	/**
	 * @param string
	 * @return QFindRows|self
	 */
	public function groupResBy($field){
		$this->groupResBy=$field;
		return $this;
	}
}
