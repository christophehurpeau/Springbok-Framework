<?php
class STransformer{
	
	public function getDisplayableValue(&$field,&$value,&$obj){
		if(isset($field['callback'])){
			if($value===null) $value=false;
			return call_user_func($field['callback'],$value);
		}elseif(isset($field['function'])){
			if($value===null) $value=false;
			return call_user_func($field['function'],$obj,$value);
		}elseif(isset($field['tabResult'])){
			if($value===null) $value=false;
			if(isset($field['tabResult'][$value])) return $field['tabResult'][$value];
		}
		return $value;
	}
	
	public static function getValueFromModel(&$model,&$field,&$i){
		return isset($field['key']) ? $model->_get($field['key']) : false;
	}
	
	public function startHead(){}
	public function endHead(){}
	public function startBody(){}
	public function end(){}
	
	protected $component;
	public function __construct(&$component){
		$this->component=&$component;
	}
	
	public function __get($name){
		/* DEV */
		if(empty($component->params) || !array_key_exists($name,$this->component->params)){//isset does'nt work if the value is null
			throw new Exception($name.' IS NOT in the params : '.print_r($this->component->params,true));
		}
		/* /DEV */
		return $this->component->params[$name];
	}
}
