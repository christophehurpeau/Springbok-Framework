<?php
/**
 * An element <input type="checkbox">
 * 
 * @see HElementForm::checkbox
 */
class HElementFormInputCheckbox extends HElementFormContainable{
	/**
	 * 
	 */
	public function __construct($form,$name,$label=null){
		parent::__construct($form,$name);
		$this->attributes['type']='checkbox';
		if($name!==false) $this->_setAttrName($name);
		$this->_setAttrId();
		$this->label=$label;
		
		$value=$this->form->_getValue($this->name);
		if($value!==null && $value!==false) $this->checked();
	}
	
	/**
	 * @return HElementFormContainer
	 */
	public function container(){ return new HElementFormContainer($this->form,$this,'input checkbox'); }
	
	/**
	 * Check the checkbox
	 * 
	 * @param bool
	 * @return HElementFormInputCheckbox|self
	 */
	public function checked($isChecked=true){ if($isChecked) $this->attributes['checked']=true; return $this; }
	
	/**
	 * @return string
	 */
	public function toString(){
		return HHtml::tag('input',$this->attributes).$this->_labelToString(' ','');
	}
}