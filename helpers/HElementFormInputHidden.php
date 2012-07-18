<?php
class HElementFormInputHidden extends HElement{
	public function __construct($form,$name,$value){
		$this->attributes['type']='hidden';
		$this->attributes['name']=$form->modelName === NULL ? $name : $form->name.'['.$name.']';
		if($value!==false) $this->attributes['value']=$value;
	}
	
	public function __toString(){
		return HHtml::tag('input',$this->attributes);
	}
}