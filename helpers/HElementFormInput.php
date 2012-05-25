<?php
class HElementFormInput extends HElementFormContainable{
	private $type;
	
	public function __construct(&$form,&$name,$largeSize=1){
		parent::__construct($form,$name);
		$this->type='text';
		
		$this->_setValueInAttrs();
		$this->attributes['id']=$this->form->modelName != null ? $this->form->modelName.ucfirst($name) : $name;
		
		if($this->form->modelName !== null){
			$this->attributes['name']=$this->form->name.'['.$name.']';
			
			$modelName=&$this->form->modelName;
			if(isset($modelName::$__PROP_DEF[$name])){
				$propDef=$modelName::$__PROP_DEF[$name];
				switch($propDef['type']){
					case 'int': $type='number'; break;
					case 'string':
						switch($name){
							case 'pwd': case 'password':
								$type='password';
								$this->attributes['value']='';
								break;
							case 'email': case 'mail':
								$type='email';
								break;
							case 'url': case 'site_web':
								$type='url';
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
	
	
	
	public function &toString(){
		$label=isset($attributes['label']) ? $attributes['label'] : ($this->defaultLabel ? ($this->modelName != NULL ? _tF($this->modelName,$name) : $name): false); unset($attributes['label']);
		
		if($label) $content=HHtml::tag('label',array('for'=>$attributes['id']),$label).' ';
		else $content='';
		if(isset($attributes['between'])){ $content.=$attributes['between']; unset($attributes['between']);}
		$content.=HHtml::tag('input',$attributes);
		
		if($hasError=(!isset($containerAttributes['error']) || $containerAttributes['error']) && CValidation::hasError($key=($this->modelName === NULL ? $name : $this->name.'.'.$name)))
			$content.=HHtml::tag('div',array('class'=>'validation-advice'),isset($containerAttributes['error'])?$containerAttributes['error']:CValidation::getError($key));
		unset($containerAttributes['error']);
		
		return $this->_inputContainer($content,'input '.($type!=='text'?'text ':'').$type.($hasError?' invalid':''),$containerAttributes);
	}
}