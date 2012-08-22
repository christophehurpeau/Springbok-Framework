<?php
class HElementFormInput extends HElementFormContainable{
	private $type;
	
	public function __construct($form,$name,$largeSize=1){
		parent::__construct($form,$name);
		$this->type='text';
		
		$this->_setAttrValue();
		$this->_setAttrId();
		
		if($this->form->modelName !== null){
			$this->attributes['name']=$this->form->name.'['.$name.']';
			
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
						} 
						break;
				}
				if(isset($propDef['annotations']['Required'])) $this->attributes['required']=true;
				if(isset($propDef['annotations']['MinSize'])) $this->attributes['min']=$propDef['annotations']['MinSize'][0];
				if(isset($propDef['annotations']['MaxSize'])) $this->attributes['max']=$propDef['annotations']['MaxSize'][0];
				if(isset($propDef['annotations']['MaxLength'])){
					$this->attributes['maxlength']=$propDef['annotations']['MaxLength'][0];
					
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
		}else $this->attributes['name']=$name;
	}
	
	public function value($value){ $this->attributes['value']=$value; return $this; }
	public function value_(&$value){ $this->attributes['value']=$value; return $this; }
	public function readOnly(){ $this->attributes['readonly']=true; return $this; }
	public function disabled(){ $this->attributes['disabled']=true; return $this; }
	public function size($size){ $this->attributes['size']=$size; return $this; }
	public function noName(){ unset($this->attributes['name']); return $this; }
	public function placeholder($placeholder){ $this->attributes['placeholder']=$placeholder; return $this; }
	

	public function container(){ return new HElementFormContainer($this->form,$this,'input '.($this->type!=='text'?'text ':'').$this->type); }
	
	public function toString(){
		$this->attributes['type']=$this->type;
		return $this->_labelToString().$this->between.HHtml::tag('input',$this->attributes);
	}
}