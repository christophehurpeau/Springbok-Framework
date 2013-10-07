<?php
/**
 * Table for rendering only one row
 * 
 * @see CModelTable
 */
class CModelTableOne extends CModelTable{
	private $results;
	
	private function fetch(){
		if($this->results===null) $this->results=$this->query->fetch();
	}
	
	/**
	 * Check if there is a result. If not, render a not found and stop.
	 * 
	 * @return CModelTableOne|self
	 */
	public function mustFetch(){
		/*#if DEV */if($this->results!==null) throw new Exception('$this->results!==null'); /*#/if*/
		$this->fetch();
		if(empty($this->results)) notFound();
		return $this;
	}
	
	/** @deprecated */
	public function notFoundIfFalse(){
		return $this->mustFetch();
	}
	
	/**
	 * Display the row in a table
	 * 
	 * @param bool
	 * @param string
	 */
	public function display($displayTotalResults=false,$transformerClass='THtml'){
		$this->fetch();
		$this->_setFields();
		$this->initController();
		$this->callTransformer($transformerClass,$this->results);
	}
	
	/**
	 * Return if the row was found
	 * 
	 * @return bool
	 */
	public function hasResult(){
		$this->fetch();
		return isset($this->results[0]);
	}
	
	/**
	 * Return the displayed row
	 * 
	 * @return SModel
	 */
	public function getResult(){
		return $this->results[0];
	}
	
	/**
	 * Create a new Table Query from a relation
	 * 
	 * @param string
	 * @param array
	 * @return QTable
	 */
	public function rel($relName,$options=array()){
		$with=array();
		QFind::_addWith($with,$relName,$options,$this->getModelName()); $w=$with[$relName];
		return QFind::createWithQuery($this->getResult(),$w,new QTable($w['modelName']));
	}
}