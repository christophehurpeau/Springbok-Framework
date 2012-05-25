<?php
class HElementFormContainable extends HElement{
	protected $form,$name,$label;
	public function __construct(&$form,&$name){
		$this->form=&$form;
		$this->name=&$name;
	}
	
	public function container(){ return new HElementFormContainer($this->form,$this); }
	public function noContainer(){ return $this->toString(); }
	
	public function __toString(){ return $this->form->isContainable() ? $this->container()->__toString() : $this->toString(); }


	protected function _setValueInAttrs(){
		$value=$this->form->_getValue($this->name);
		if($value !== null) $this->attributes['value']=&$value;
	}
}