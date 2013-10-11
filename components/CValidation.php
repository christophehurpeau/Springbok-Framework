<?php
/**
 * This component validates values. Used by CBinder
 * 
 * You can use annotations in actions and model fields to valid their format :
 * 
 * - @Required
 * - @NotEmpty
 * - @Id
 * - @MinLength(int $minLength)
 * - @MaxLength(int $maxLength)
 * - @Length(int $length)
 * - @MinSize(int $minSize)
 * - @MaxSize(int $maxSize)
 * - @Url
 * - @Email
 * - @Match(string $pattern [,string $errorMessage])
 * 
 * You can also create your own CValidation Component and create methods to be able to validate other pattern
 * 
 * <code>
 * class ACValidation extends CValidation{
 * 	public static function frenchPhoneNumber($key,$val){
 * 		return self::_addError($key,self::validFrenchPhoneNumber($val));
 * 	}
 * 	protected static function validFrenchPhoneNumber($val){
 * 		return preg_match('^(\+|[0-9][1-9])[0-9\.\- ]+$',$val) ? false : sprintf(_t('This phone number "%s" is not a french phone number'),$val);
 * 	}
 * 	protected static function htmlFrenchPhoneNumber($input){
 * 		$input->pattern('^(\+|[0-9][1-9])[0-9\.\- ]+$');
 * 	}
 * }
 * </code>
 * 
 * Then you will be able to use <var>@FrenchPhoneNumber</var>
 * 
 * The valid method return false if the value is valid or a string with an error message.
 * 
 * You can pass as many arguments as you like : for the annotation <var>@Test(1,2,3)</var>, the method can be:
 * <code>
 * protected static function validTest($val,$param1,$param2,$param3){
 * }
 * </code>
 * 
 * @see CBinder
 * @see Controller
 */
class CValidation{
	const PATTERN_HOSTNAME='((?:[a-z0-9][-a-z0-9]*\.)*(?:[a-z0-9][-a-z0-9]{0,62}))\.((?:(?:[a-z]{2,3}\.)?[a-z]{2,4}|museum|travel))';
	
	private static $_hasErrors=false;
	private static $_errors=array();
	
	/** @return bool */
	public static function hasErrors(){return self::$_hasErrors;}
	/** @return array */
	public static function getErrors(){return self::$_errors;}
	/** @return bool */
	public static function hasError($key){return isset(self::$_errors[$key]);}

	public static function getError($key){return self::$_errors[$key];}
	
	/** @return void */
	public static function reset(){
		self::$_errors=array();
		self::$_hasErrors=false;
	}
	
	/**
	 * Validate using annotations
	 * <code>
	 * CValidation::valid('test',array('Length'=>array(5)),'value')
	 * </code>
	 * 
	 * @param string
	 * @param array
	 * @param mixed
	 * @return mixed
	 */
	public static function valid($key,$annotations,$val){
		if(!empty($annotations)){
			$calledClass=get_called_class();
			foreach($annotations as $name=>$params){
				if(!method_exists($calledClass,'valid'.$name)) continue;
				if($params) array_unshift($params,$val);
				else $params=array($val);
				self::_addError($key,call_user_func_array(array($calledClass,'valid'.$name),$params));
			}
		}
		return $val;
	}
	
	public static function inputValidation($input,$annotations){
		if(!empty($annotations)){
			$calledClass=get_called_class();
			foreach($annotations as $name=>$params){
				if(!method_exists($calledClass,'html'.$name)) continue;
				if($params) array_unshift($params,$input);
				else $params=array($input);
				call_user_func_array(array($calledClass,'html'.$name),$params);
			}
		}
		return $input;
	}
	
	/**
	 * Return HTML list of errors
	 * 
	 * @return string
	 */
	public static function errors(){
		if(!self::hasErrors()) return '';
		$str='<div class="frame errors"><h3>Oops...</h3><ul>';
		foreach(self::$_errors as $key => $error)
			$str.='<li>'.$key.' : '.$error.'</li>';
		return $str.'</ul></div>';
	}
	
	/**
	 * Add a new error if error is true
	 * 
	 * @param string
	 * @param mixed
	 * @return bool
	 */
	public static function addError($key,$error){
		return self::_addError($key,$error);
	}
	
	/**
	 * @param string
	 * @param mixed
	 * @return bool
	 */
	private static function _addError($key,$error){
		if(!$error) return false;
		self::$_hasErrors=true;
		self::$_errors[$key]=$error;
		return true;
	}
	
	/**
	 * $val!==false && $val!==null && trim($val)!==''
	 * 
	 * @param string
	 * @param mixed
	 * @return bool
	 */
	public static function required($key,$val){
		return self::_addError($key,self::validRequired($val));
	}
	private static function validRequired($val){
		return ($val===false || $val===null || trim($val)==='') ? _tC('validation.required') : false;
	}
	private static function htmlRequired($input){
		$input->required();
	}
	
	
	/**
	 * !empty($val)||$val==='0'
	 * 
	 * @param string
	 * @param mixed
	 * @return bool
	 */
	public static function notEmpty($key,$val){
		return self::_addError($key,self::validNotEmpty($val));
	}
	private static function validNotEmpty($val){
		return empty($val)&&$val!=='0' ? _tC('validation.required') : false;
	}
	private static function htmlNotEmpty($input){
		$input->required();
	}
	
	
	/**
	 * $val > 0
	 * 
	 * @param string
	 * @param mixed
	 * @return bool
	 */
	public static function id($key,$val){
		return self::_addError($key,self::validId($val));
	}
	private static function validId($val){
		return /*!preg_match('/^[0-9]+$/',$val)*/$val>0 ? false : _tC('This field should be a valid id');
	}
	
	
	/**
	 * strlen($val) <= $maxLength
	 * 
	 * @param string
	 * @param mixed
	 * @param int
	 * @return bool
	 */
	public static function maxLength($key,$val,$maxLength){
		return self::_addError($key,self::validMaxLength($val,$maxLength));
	}
	private static function validMaxLength($val,$maxLength){
		return (strlen($val) <= $maxLength) ? false : _tC('validation.maxlength');
	}
	private static function htmlMaxLength($input,$maxLength){
		$input->attr('maxlength',$maxLength);
	}
	
	/**
	 * strlen($val) == $length
	 * 
	 * @param string
	 * @param mixed
	 * @param int
	 * @return bool
	 */
	public static function length($key,$val,$length){
		return self::_addError($key,self::validLength($val,$length));
	}
	private static function validLength($val,$length){
		return (strlen($val) == $length) ? false : sprintf(_tC('This field must have a length of %s'),$length);
	}
	private static function htmlLength($input,$length){
		$input->attr('maxlength',$length)->attr('minlength',$length);
	}

	/**
	 * strlen($val) >= $minLength
	 * 
	 * @param string
	 * @param mixed
	 * @param int
	 * @return bool
	 */
	public static function minLength($key,$val,$minLength){
		return self::_addError($key,self::validMinLength($val,$minLength));
	}
	private static function validMinLength($val,$minLength){
		return (strlen($val) >= $minLength) ? false : sprintf(_tC('validation.minlength'),$minLength);
	}
	private static function htmlMinLength($input,$minLength){
		$input->attr('minlength',$minLength);
	}

	/**
	 * $val <= $maxSize
	 * 
	 * @param string
	 * @param mixed
	 * @param int
	 * @return bool
	 */
	public static function maxSize($key,$val,$maxSize){
		return self::_addError($key,self::validMaxSize($val,$maxSize));
	}
	private static function validMaxSize($val,$maxSize){
		return ($val <= $maxSize) ? false : sprintf(_tC('validation.maxsize'),$maxSize);
	}
	private static function htmlMaxSize($input,$maxSize){
		$input->attr('max',$maxSize);
	}

	/**
	 * $val >= $minSize
	 * 
	 * @param string
	 * @param mixed
	 * @param int
	 * @return bool
	 */
	public static function minSize($key,$val,$minSize){
		return self::_addError($key,self::validMinSize($val,$minSize));
	}
	private static function validMinSize($val,$minSize){
		return ($val >= $minSize) ? false : sprintf(_tC('validation.minsize'),$minSize);
	}
	private static function htmlMinSize($input,$minSize){
		$input->attr('min',$minSize);
	}
	
	/**
	 * /^[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+)*@ PATTERN_HOSTNAME $/i
	 * 
	 * @param string
	 * @param string
	 * @return bool
	 */
	public static function email($key,$val){
		return self::_addError($key,self::validEmail($val));
	}
	private static function validEmail($val){
		return self::isValidEmail($val) ? false : _tC('validation.email');
	}
	private static function htmlEmail($input){
		$input->setType('email');
	}
	
	/**
	 * /^[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+)*@ PATTERN_HOSTNAME $/i
	 * 
	 * @param mixed
	 * @return bool
	 */
	public static function isValidEmail($val){
		return preg_match('/^[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+)*@'.self::PATTERN_HOSTNAME.'$/i',$val);
	}
	
	/**
	 * if val match a pattern
	 * 
	 * @param string
	 * @param mixed
	 * @param string
	 * @return bool
	 */
	public static function match($key,$val,$match){
		return self::_addError($key,self::validMatch($val,$match));
	}
	public static function validMatch($val,$match,$message=null){
		return preg_match('/'.$match.'/',$val) ? false : ($message!==null ? _t($message) : sprintf(_tC('validation.pattern'),$match));
	}
	private static function htmlMatch($input,$match,$message=null){
		$input->pattern($match);
		if($message !== null) $input->dataattr('pattern-error-message',_t($message));
	}
	
	/**
	 * /^https?\:\/\/ PATTERN_HOSTNAME $/i
	 * 
	 * @param string
	 * @param string
	 * @return bool
	 */
	public static function url($key,$val){
		return self::_addError($key,self::validUrl($val,$maxSize));
	}
	private static function validUrl($val){
		return self::isValidUrl($val) ? false : _tC('validation.url');
	}
	private static function htmlUrl($input,$match){
		$input->setType('url');
	}
	
	/**
	 * 
	 * @param string
	 * @return bool
	 */
	public static function isValidUrl($val){
		return preg_match('/^https?\:\/\/'.self::PATTERN_HOSTNAME.'$/i',$val);
	}
}
/*
_tC('validation.color');
_tC('validation.date');
_tC('validation.datetime');
_tC('validation.time');
_tC('validation.month');
_tC('validation.number');
_tC('validation.range');
_tC('validation.text');
_tC('validation.url');
_tC('validation.checkbox');
*/