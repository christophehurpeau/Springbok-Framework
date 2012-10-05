<?php
class HElementFormInputCheckbox extends HElementFormContainable{
	public function __construct($form,$name,$label=false){
		parent::__construct($form,$name);
		$this->attributes['type']='checkbox';
		if($name!==false) $this->_setAttrName($name);
		$this->_setAttrId();
		$this->label=$label;
	}
	
	public function container(){ return new HElementFormContainer($this->form,$this,'input checkbox'); }
	public function checked($isChecked=true){ if($isChecked) $this->attributes['checked']=true; return $this; }
	
	public function toString(){
		return HHtml::tag('input',$this->attributes).$this->_labelToString(' ','');
	}
}