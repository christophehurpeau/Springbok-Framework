<?php

function _t($string){return $string;}
function _tC($string){return CLang::translate($string);}
function _tF($modelName,$fieldName=''){return $fieldName;}

class CLang{
	private static $db=NULL;
	public static function get(){ return Config::$lang; }
	
	public static function &translate($string){
		if(self::$db === NULL){
			self::$db=DB::init('_lang',array(
				'type'=>'SQLite',
				'file'=>CORE.'i18n'.DS.self::get().'.db',
				'flags'=>SQLITE3_OPEN_READONLY
			));
		}
		$r=self::$db->doSelectValue('SELECT t FROM t WHERE s='.self::$db->escape($string).' LIMIT 1');
		return $r;
	}
}
