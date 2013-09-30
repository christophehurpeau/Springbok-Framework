<?php
/**
 * An <input type="hidden"> element
 * 
 * @see HElementForm::hidden
 */
class HElementFormInputHidden extends HElement{
	/**
	 * @internal
	 * @param HElementForm
	 * @param string
	 * @param mixed
	 */
	public function __construct($form,$name,$value){
		$this->attributes['type']='hidden';
		$this->attributes['name']=$form->modelName === NULL ? $name : $form->name.'['.$name.']';
		if($value!==false) $this->attributes['value']=$value;
	}
	
	/**
	 * @return string
	 */
	public function __toString(){
		return HHtml::tag('input',$this->attributes);
	}
}