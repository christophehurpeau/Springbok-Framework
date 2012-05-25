<?php
class HElementFormInputCheckbox extends HElementFormContainable{
	public function __construct(&$form,&$name){
		parent::__construct($form,$name);
		$this->attributes['type']='checkbox';
		if($name!==false){
			$this->_setAttrName();
			$this->_setAttrId();
		}
	}
	
	public function container(){ return new HElementFormContainer($this->form,$this,'input checkbox'); }
	
	public function toString(){
		return HHtml::tag('input',$attributes).$this->_labelToString(' ','');
	}
}