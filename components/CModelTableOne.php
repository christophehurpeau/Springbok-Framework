<?php
class CModelTableOne extends CModelTable{
	private $results;
	public function display($displayTotalResults=true,$transformerClass='THtml'){
		$this->results=$this->query->execute();
		$this->_setFields();
		$this->initController();
		$this->callTransformer($transformerClass,$this->results);
	}
	
	public function getResult(){
		return $this->results[0];
	}
}