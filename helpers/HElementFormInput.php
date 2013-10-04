<?php
/**
 * An <input> element
 * 
 * @see HElementForm::input()
 */
class HElementFormInput extends HElementFormContainable{
	private $type;
	
	/**
	 * @internal
	 * @param HElementForm
	 * @param string
	 * @param int
	 */
	public function __construct($form,$name,$largeSize=1){
		parent::__construct($form,$name);
		$this->type='text';
		
		$this->_setAttrValue();
		$this->_setAttrId();
		$this->_setAttrName($name);
		
		if($this->form->modelName !== null){
			$modelName=$this->form->modelName;
			if(isset($modelName::$__PROP_DEF[$name])){
				$propDef=$modelName::$__PROP_DEF[$name];
				switch($propDef['type']){
					case 'int': $type='number'; break;
					case 'float': case 'double':
						$this->attributes['pattern']='[0-9]*([\.\,][0-9]+)?';
						break;
					case 'string':
						switch($name){
							case 'pwd': case 'password':
								$this->type='password';
								$this->attributes['value']='';
								break;
							case 'email': case 'mail':
								$this->type='email';
								break;
							case 'url': case 'website':
								$this->type='url';
								break;
							case 'phone_number':
								$this->type='tel';
								break;
							case 'file':
								$this->type='file';
								break;
						} 
						break;
				}
				
				CValidation::inputValidation($this,$propDef['annotations']);
				if(isset($propDef['annotations']['MaxLength'])){
					if($this->attributes['maxlength'] < 10) $this->attributes['size']=11;
					elseif($this->attributes['maxlength'] <= 30) $this->attributes['size']=25;
					elseif($this->attributes['maxlength'] < 80) $this->attributes['size']=30;
					elseif($this->attributes['maxlength'] < 120) $this->attributes['size']=40;
					elseif($this->attributes['maxlength'] < 160) $this->attributes['size']=50;
					elseif($this->attributes['maxlength'] < 200) $this->attributes['size']=60;
					else $this->attributes['size']=70;
					$this->attributes['size']*=$largeSize;
				}
			}
		}
	}
	
	/**
	 * Set the type of input : text, number, password, mail, url, file, ...
	 * 
	 * @param string
	 * @return HElementFormInput|self
	 */
	public function setType($type){ $this->type=$type; return $this; }
	
	/**
	 * Set the value attribute
	 * 
	 * @param string
	 * @return HElementFormInput|self
	 */
	public function value($value){ $this->attributes['value']=$value; return $this; }

	/**
	 * Set the value attribute, by reference
	 * 
	 * @param string
	 * @return HElementFormInput|self
	 */
	public function value_(&$value){ $this->attributes['value']=$value; return $this; }
	
	/**
	 * Set the size attribute
	 * 
	 * @param string
	 * @return HElementFormInput|self
	 */
	public function size($size){ $this->attributes['size']=$size; return $this; }
	
	/**
	 * Set the name attribute
	 * 
	 * @param string
	 * @return HElementFormInput|self
	 */
	public function name($value){ $this->attributes['name']=$value; return $this; }
	
	
	/**
	 * Remove the value attribute
	 * 
	 * @return HElementFormInput|self
	 */
	public function noName(){ unset($this->attributes['name']); return $this; }
	
	/**
	 * Set the placeholder attribute
	 * 
	 * @param string
	 * @return HElementFormInput|self
	 */
	public function placeholder($placeholder){ $this->attributes['placeholder']=$placeholder; return $this; }
	
	
	/**
	 * Set the pattern attribute
	 * 
	 * @param string
	 * @return HElementFormInput|self
	 */
	public function pattern($pattern){ $this->attributes['pattern']=$pattern; return $this; }
	
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
	public function container(){ return new HElementFormContainer($this->form,$this,'input '.($this->type!=='text'?'text ':'').$this->type); }
	
	/**
	 * @return string
	 */
	public function toString(){
		/*#if DEV */ if(Springbok::$inError) return '[HElementFormInput]'; /*#/if*/
		$this->attributes['type']=$this->type;
		return $this->_labelToString().(empty($this->between)?'':$this->between).HHtml::tag('input',$this->attributes);
	}
}