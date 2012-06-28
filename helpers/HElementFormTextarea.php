<?php
class HElementFormTextarea extends HElementFormContainable{
	private $value;
	
	public function __construct($form,$name){
		parent::__construct($form,$name);
		
		$this->attributes['rows']=7;
		$this->attributes['cols']=100;
		$this->attributes['id']=$this->form->modelName != null ? $this->form->modelName.ucfirst($name) : $name;
		
		if($this->form->modelName !== null){
			$this->attributes['name']=$this->form->name.'['.$name.']';
			
			$modelName=$this->form->modelName;
			if(isset($modelName::$__PROP_DEF[$name])){
				$propDef=$modelName::$__PROP_DEF[$name];
				if(isset($propDef['annotations']['Required'])) $this->attributes['required']=true;
				if(isset($propDef['annotations']['MaxLength'])){
					$this->attributes['maxlength']=$propDef['annotations']['MaxLength'][0];
					$this->attributes['rows']=5;
				}
			}
		}else $this->attributes['name']=$name;
		
		$this->value=$this->form->_getValue($name);
	}
	
	public function value($value){ $this->value=$value; return $this; }
	public function value_(&$value){ $this->value=$value; return $this; }
	
	public function container(){ return new HElementFormContainer($this->form,$this,'textarea'); }
	
	public function toString($name){
		if(empty($this->value)) $this->value='';//close the 'textarea' tag
		return $this->_labelToString().$this->between.HHtml::tag('textarea',$this->attributes,$value);
	}
}