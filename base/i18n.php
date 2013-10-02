<?php
// date_default_timezone_set('UTC');
require '../components/CLang.php';

/**
 * Translate a string using APP/locales
 * @param string
 * @param string|null
 * @return string
 */
function _t($string,$alt=null){
	$r=CLang::translate($string,'a');
	return $r!==false ? $r : ($alt===null?$string:$alt);
}

/**
 * Translate a string using vsprintf
 * 
 * @param string
 * @param mixed... $args
 * @return string 
 */
function tf($string){
	$args=func_get_args();
	$r=CLang::translate(array_shift($args),'a');
	return vsprintf($r!==false ? $r : $string,$args);
}

/**
 * Translate a string using the core translations
 * 
 * @param string
 * @return string
 * @see CLangCore::translate
 */
function _tC($string){
	$r=CLangCore::translate($string);
	return $r!==null ? $r : $string;
}

/**
 * Translate a model's field name
 * 
 * @param string
 * @param string
 * @param string|null
 */
function _tF($modelName,$fieldName='',$alt=null){
	$r=CLang::translate($modelName.':'.$fieldName,'f');
	return $r!==false ? $r : ($alt===null?$fieldName:$alt);
}

/**
 * Translate a plural / singular string
 * 
 * @param string
 * @param int
 * @return string the singular or the plural string
 * @see CLocal::isPlural
 * @see CLang::translate
 */
function _t_p($string,$count){
	$r=CLang::translate($string,App::getLocale()->isPlural((int)$count)?'s':'p');
	return $r!==false ? $r : $string;
}

/**
 * Translate a route
 * 
 * @param string
 * @return string
 * @see CRoute::translate
 * @see CLang::get
 */
function _tR($string){
	return CRoute::translate($string,CLang::get());
}

/**
 * Return the right singular or plural based on the count
 * 
 * @param int
 * @param string
 * @param string
 * @return string singular or plural
 * @see CLocal::isPlural
 */
function _sp($count,$singular,$plural){
	return App::getLocale()->isPlural((int)$count)?$plural:$singular;
}
