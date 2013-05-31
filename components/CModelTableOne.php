<?php
class CModelTableOne extends CModelTable{
	private $results;
	
	private function execute(){
		if($this->results===null) $this->results=$this->query->execute();
	}
	
	public function notFoundIfFalse(){
		/*#if DEV */if($this->results!==null) throw new Exception('$this->results!==null'); /*#/if*/
		$this->execute();
		if(empty($this->results)) notFound();
		return $this;
	}
	
	public function display($displayTotalResults=false,$transformerClass='THtml'){
		$this->execute();
		$this->_setFields();
		$this->initController();
		$this->callTransformer($transformerClass,$this->results);
	}
	
	public function hasResult(){
		$this->execute();
		return isset($this->results[0]);
	}
	public function getResult(){
		return $this->results[0];
	}
	
	public function rel($relName,$options=array()){
		$with=array();
		QFind::_addWith($with,$relName,$options,$this->getModelName()); $w=$with[$relName];
		return QFind::createWithQuery($this->getResult(),$w,new QTable($w['modelName']));
	}
}