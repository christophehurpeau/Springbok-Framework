<?php
/**
 * A Containable is an element which can be wrapped in a tag
 * 
 * @see HElementFormContainer 
 */
abstract class HElementFormContainable extends HElement{
	protected $form;
	
	/** @internal */
	public $name,$label,$labelEscape=true,$between='';
	
	/**
	 * @internal
	 * @param HElementForm
	 * @param string
	 */
	public function __construct($form,$name){
		$this->form=$form;
		$this->name=$name;
	}
	
	/**
	 * @
	 * 
	 * @return HElementContainer
	 */
	public abstract function container();
	
	/**
	 * Ends the element and return the generated html string, without container
	 * 
	 * @return string
	 */
	public function noContainer(){ return $this->toString(); }
	
	/**
	 * Set the label
	 * 
	 * @param string
	 * @return HElementFormContainable|self
	 */
	public function label($label){ $this->label=$label; return $this; }
	
	/**
	 * Set the label, in html
	 * 
	 * @param string
	 * @return HElementFormContainable|self
	 */
	public function htmlLabel($label){ $this->label=$label; $this->labelEscape=false; return $this; }
	
	/**
	 * Remove the label
	 * 
	 * @return HElementFormContainable|self
	 */
	public function noLabel(){ $this->label=false; return $this; }
	
	/**
	 * Set the required attribute
	 * 
	 * @param bool
	 * @return HElementFormContainable|self
	 */
	public function required($isRequired=true){
		if($isRequired) $this->attributes['required']=true;
		return $this;
	}
	
	/**
	 * Set the readOnly attribute
	 * 
	 * @param bool
	 * @return HElementFormContainable|self
	 */
	public function readOnly($isReadOnly=true){
		if($isReadOnly) $this->attributes['readonly']=true;
		return $this;
	}
	
	/**
	 * Set the disabled attribute
	 * 
	 * @param bool
	 * @return HElementFormContainable|self
	 */
	public function disabled($isDisabled=true){
		if($isDisabled) $this->attributes['disabled']=true;
		return $this;
	}
	
	/**
	 * Set html content between the label and the element
	 * 
	 * @return HElementFormContainable|self
	 */
	public function between($content){
		$this->between=$content;
		return $this;
	}
	
	/**
	 * @internal
	 * 
	 * @param string
	 * @param string
	 * @return string
	 */
	protected function _labelToString($prefix='',$suffix=' '){
		if($this->label===null) $this->label=$this->form->defaultLabel ? ($this->form->modelName !== null ? _tF($this->form->modelName,$this->name) : $this->name): false;
		if($this->label===false) return '';
		if($this->label!==null) $label=$this->label;
		else{
			if(!$this->form->defaultLabel) return '';
			$label=$this->form->modelName != NULL ? _tF($this->form->modelName,$this->name) : $this->name;
		}
		return $prefix.HHtml::tag('label',array('for'=>$this->attributes['id']),$label,$this->labelEscape).$suffix;
	}
	
	/**
	 * Returns the element into a real html string
	 * 
	 * @return string
	 */
	public function __toString(){
		/*#if DEV */ if(Springbok::$inError) return '[HElementFormContainable]'; /*#/if*/
		return $this->form->isContainable() ? $this->container()->__toString() : $this->toString();
	}
	
	/**
	 * Set the value from the form data
	 * 
	 * @return void
	 */
	protected function _setAttrValue(){
		$value=$this->form->_getValue($this->name);
		if($value !== null) $this->attributes['value']=&$value;
	}
	
	/**
	 * Set the id attribute from the form's model name
	 * 
	 * @return void
	 */
	protected function _setAttrId(){
		$this->attributes['id']=$this->form->modelName != null ? $this->form->modelName.ucfirst($this->name) : $this->name;
	}
	
	/**
	 * Set the name attribute from the form's model name
	 * 
	 * @return void
	 */
	protected function _setAttrName($name){
		$this->attributes['name']=$this->_name($name);
	}
	
	
	/**
	 * @internal
	 * Return the computed name from the form's model name
	 * 
	 * @return string
	 */
	protected function _name($name){
		return $this->form->modelName !== null && $this->form->name!==false ? $this->form->name.'['.$this->name.']' : $this->name;
	}
}