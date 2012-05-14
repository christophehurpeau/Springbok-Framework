<?php
class CValidation{
	const PATTERN_HOSTNAME='(?:[a-z0-9][-a-z0-9]*\.)*(?:[a-z0-9][-a-z0-9]{0,62})\.(?:(?:[a-z]{2}\.)?[a-z]{2,4}|museum|travel)';
	
	private static $_hasErrors=false;
	private static $_errors=array();

	public static function &hasErrors(){return self::$_hasErrors;}
	public static function &getErrors(){return self::$_errors;}
	public static function hasError($key){return isset(self::$_errors[$key]);}
	public static function &getError($key){return self::$_errors[$key];}
	public static function reset(){
		self::$_errors=array();
		self::$_hasErrors=false;
	}
	
	public static function &valid($key,&$annotations,$val){
		foreach($annotations as $name=>$params){
			if(!method_exists(get_called_class(),'valid'.$name)) continue;
			if($params) array_unshift($params,$val);
			else $params=array($val);
			self::_addError($key,call_user_func_array(array('self','valid'.$name),$params));
		}
		return $val;
	}
	
	public static function errors(){
		if(!self::hasErrors()) return '';
		$str='<div class="frame errors"><h3>Oops...</h3><ul>';
		foreach(self::$_errors as $key => $error)
			$str.='<li>'.$key.' : '.$error.'</li>';
		return $str.'</ul></div>';
	}
	
	public static function addError($key,$error){
		return self::_addError($key,$error);
	}
	
	private static function _addError(&$key,$error){
		if(!$error) return false;
		self::$_hasErrors=true;
		self::$_errors[$key]=$error;
		return true;
	}
	
	public static function required($key,$val){
		return self::_addError($key,self::validRequired($val));
	}
	private static function validRequired($val){
		return ($val===false || $val===null || trim($val)==='') ? _tC('This field is required') : false;
	}

	
	public static function notEmpty($key,$val){
		return self::_addError($key,self::validRequired($val));
	}
	private static function validNotEmpty($val){
		return empty($val) ? _tC('This field is required') : false;
	}

	

	public static function maxLength($key,$val,$maxLength){
		return self::_addError($key,self::validMaxLength($val,$maxLength));
	}
	private static function validMaxLength($val,$maxLength){
		return (strlen($val) <= $maxLength) ? false : _tC('This field is too long');
	}

	public static function length($key,$val,$length){
		return self::_addError($key,self::validLength($val,$length));
	}
	private static function validLength($val,$length){
		return (strlen($val) == $length) ? false : _tC('This field has not a good length');
	}

	public static function minLength($key,$val,$minLength){
		return self::_addError($key,self::validMinLength($val,$minLength));
	}
	private static function validMinLength($val,$minLength){
		return (strlen($val) >= $minLength) ? false : _tC('This field is too short');
	}

	public static function maxSize($key,$val,$maxSize){
		return self::_addError($key,self::validMaxSize($val,$maxSize));
	}
	private static function validMaxSize($val,$maxSize){
		return ($val <= $maxSize) ? false : _tC('This field is too high');
	}

	public static function minSize($key,$val,$minSize){
		return self::_addError($key,self::validMinSize($val,$minSize));
	}
	private static function validMinSize($val,$minSize){
		return ($val >= $minSize) ? false : _tC('This field is too low');
	}
	
	public static function email($key,$val){
		return self::_addError($key,self::email($val));
	}
	private static function validEmail($val){
		return preg_match('/^[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+)*@'.self::PATTERN_HOSTNAME.'$/i',$val) ? false : _tC('This is not a valid email');
	}
	
	public static function match($key,$val,$match){
		return self::_addError($key,self::validMatch($val,$match));
	}
	public static function validMatch($val,$match){
		return preg_match('/'.$match.'/',$val) ? false : _tC('This fields does not valid:').' '.$match;
	}
}