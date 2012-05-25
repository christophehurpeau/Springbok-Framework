<?php
class HElementFormTextarea extends HElement{
	private function &value(&$value){ $this->value=&$value; return $this; }
	
	public function toString($name){
		if(is_string($attributes)){
			$value=$attributes;
			$attributes=array();
		}
		
		if($this->modelName != NULL){
			if(!isset($attributes['name'])) $attributes['name']=$this->name.'['.$name.']';
			elseif($attributes['name']===false) unset($attributes['name']);
			
			$modelName=$this->modelName;
			if(isset($modelName::$__PROP_DEF[$name])){
				$propDef=$modelName::$__PROP_DEF[$name];
				if(isset($propDef['annotations']['Required'])) $attributes['required']=true;
				if(isset($propDef['annotations']['MaxLength'])){
					$attributes['maxlength']=$propDef['annotations']['MaxLength'][0];
					if(!isset($attributes['rows'])) $attributes['rows']=5;
				}
			}
		}elseif(!isset($attributes['name'])) $attributes['name']=$name;
		elseif($attributes['name']===false) unset($attributes['name']);
		
		$attributes+=array('rows'=>7,'cols'=>100);
		
		if(!isset($value)){
			$this->_setValue($name,$attributes);
			if(isset($attributes['value'])){
				$value=$attributes['value'];
				unset($attributes['value']);
			}else $value='';//close the 'textarea' tag
		}
		
		if(!isset($attributes['id'])) $attributes['id']=$this->modelName != NULL ? $this->modelName.ucfirst($name) : $name;
		$label=isset($attributes['label']) ? $attributes['label'] : ($this->defaultLabel ? ($this->modelName != NULL ? _tF($this->modelName,$name) : $name) : false); unset($attributes['label']);
		
		$content='';
		if($label) $content.=HHtml::tag('label',array('for'=>$attributes['id']),$label);
		if(isset($attributes['between'])){
			$content.=$attributes['between'];
			unset($attributes['between']);
		}
		$content.=HHtml::tag('textarea',$attributes,$value);
		
		if($hasError=(!isset($containerAttributes['error']) || $containerAttributes['error']) && CValidation::hasError($key=($this->modelName === NULL ? $name : $this->name.'.'.$name)))
			$content.=HHtml::tag('div',array('class'=>'validation-advice'),isset($containerAttributes['error'])?$containerAttributes['error']:CValidation::getError($key));
		unset($containerAttributes['error']);
		
		return $this->_inputContainer($content,'textarea'.($hasError?' invalid':''),$containerAttributes);
	}
}