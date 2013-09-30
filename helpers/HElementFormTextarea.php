<?php
/**
 * A <textarea> element
 * 
 * @see HElementForm::textarea
 */
class HElementFormTextarea extends HElementFormContainable{
	private $value;
	
	/**
	 * @internal
	 * @param HElementForm
	 * @param string
	 */
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
	
	/**
	 * Removes the name attribute
	 * 
	 * @return HElementFormTextarea|self
	 */
	public function noName(){ unset($this->attributes['name']); return $this; }
	
	/**
	 * Set the value attribute
	 * 
	 * @param string
	 * @return HElementFormTextarea|self
	 */
	public function value($value){ $this->value=$value; return $this; }
	
	/**
	 * Set the value attribute by reference
	 * 
	 * @param string
	 * @return HElementFormTextarea|self
	 */
	public function value_(&$value){ $this->value=$value; return $this; }
	
	/**
	 * Set the rows attribute
	 * 
	 * @param string
	 * @return HElementFormTextarea|self
	 */
	public function rows($rows){ $this->attributes['rows']=$rows; return $this; }
	
	
	/**
	 * Set the name attribute
	 * 
	 * @param string
	 * @return HElementFormTextarea|self
	 */
	public function cols($cols){ $this->attributes['cols']=$cols; return $this; }
	
	/**
	 * Set the class attribute to "wp100"
	 * 
	 * @param string
	 * @return HElementFormInput|self
	 */
	public function wp100(){ $this->attributes['class']='wp100'; return $this; }
	
	/**
	 * Returns the container
	 * 
	 * @return HElementFormContainer
	 */
	public function container(){ return new HElementFormContainer($this->form,$this,'textarea'); }
	
	/**
	 * @return string
	 */
	public function toString(){
		if(empty($this->value)) $this->value='';//close the 'textarea' tag
		return $this->_labelToString().$this->between.HHtml::tag('textarea',$this->attributes,$this->value);
	}
}