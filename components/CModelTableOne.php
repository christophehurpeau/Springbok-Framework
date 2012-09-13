<?php
class CModelTableOne extends CModelTable{
	private $results;
	
	private function execute(){
		if($this->results===null) $this->results=$this->query->execute();
	}
	
	public function display($displayTotalResults=false,$transformerClass='THtml'){
		$this->execute();
		$this->_setFields();
		$this->initController();
		$this->callTransformer($transformerClass,$this->results);
	}
	
	public function getResult(){
		return $this->results[0];
	}
	
	public function rel($relName,$options=array()){
		$this->execute();
		$with=array();
		QFind::_addWith($with,$relName,$options,$this->getModelName()); $w=$with[$relName];
		return QFind::createWithQuery($this->getResult(),$w,new QTable($w['modelName']));
	}
}