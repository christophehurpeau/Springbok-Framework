<?php

function _t($string){return $string;}
function _tC($string){return CLangCore::translate($string);}
function _tF($modelName,$fieldName=''){return $fieldName;}

class CLang{
	private static $db=NULL;
	public static function get(){ return Config::$lang; }
}
