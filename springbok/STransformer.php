<?php
/**
 * Transform tables into Csv, Html, Xls ...
 */
abstract class STransformer{
	/**
	 * Return the value of a field
	 * 
	 * @param string
	 * @param array
	 * @param SModel
	 */
	public function getDisplayableValue($field,$value,$obj){
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
	
	/**
	 * @param SModel
	 * @param string
	 * @param int
	 */
	public static function getValueFromModel($model,$field,$i){
		return isset($field['key']) && $model->isKeyExists($field['key']) ? $model->_get($field['key']) : false;
	}
	
	/**
	 * Start Head
	 * @return void
	 */
	public function startHead(){}
	
	/**
	 * End Head
	 * @return void
	 */
	public function endHead(){}
	
	/**
	 * Start Body
	 * @return void
	 */
	public function startBody(){}
	
	
	/**
	 * End Body
	 * @return void
	 */
	public function end(){}
	
	/**
	 * @var CModelTable
	 */
	protected $component;
	
	/**
	 * @param CModelTable
	 */
	public function __construct($component){
		$this->component=$component;
	}
	
	/**
	 * Magic method
	 */
	public function __get($name){
		/*#if DEV */
		if(empty($this->component->params) || !array_key_exists($name,$this->component->params)){//isset does'nt work if the value is null
			throw new Exception($name.' IS NOT in the params : '.print_r($this->component->params,true));
		}
		/*#/if */
		return $this->component->params[$name];
	}
}
