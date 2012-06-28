<?php
abstract class HElementFormContainable extends HElement{
	protected $form;
	public $name,$label,$labelEscape=true,$between='';
	
	public function __construct($form,$name){
		$this->form=$form;
		$this->name=$name;
	}
	
	public function placeholder($placeholder){ $this->attributes['placeholder']=$placeholder; return $this; }
	
	public abstract function container();
	public function noContainer(){ return $this->toString(); }
	
	public function label($label){ $this->label=$label; return $this; }
	public function htmlLabel($label){ $this->label=$label; $this->labelEscape=false; return $this; }
	public function noLabel(){ $this->label=false; return $this; }
	
	public function between($content){ $this->between=$content; return $this; }
	
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
	
	public function __toString(){ return $this->form->isContainable() ? $this->container()->__toString() : $this->toString(); }


	protected function _setAttrValue(){
		$value=$this->form->_getValue($this->name);
		if($value !== null) $this->attributes['value']=&$value;
	}
	
	protected function _setAttrId(){
		$this->attributes['id']=$this->form->modelName != null ? $this->form->modelName.ucfirst($this->name) : $this->name;
	}
	
	protected function _setAttrName(){
		$this->attributes['name']=$this->_name();
	}
	
	
	protected function _name($name){
		return $this->form->modelName !== null ? $this->form->name.'['.$this->name.']' : $this->name;
	}
}