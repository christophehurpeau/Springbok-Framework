<?php
class CLang{
	private static $lang;
	/** @var DB */
	private static $db;
	
	public static function init(){
		$lang=false;
		if(isset(Config::$search_lang)) foreach(Config::$search_lang as $type){
			switch($type){
				case 'session':
					if(CSession::exists('_lang')) $lang=CSession::get('_lang');
					break;
				case 'cookie':
					if(CCookie::exists('lang')) $lang=CCookie::getLang();
					break;
			}
		}
		if($lang===false) $lang=/* DEV */isset(App::$enhancing)&&App::$enhancing?'fr':/* /DEV */Config::$default_lang;
		self::$lang=$lang;
		self::$db=DB::init('_lang',array(
			'type'=>'SQLite',
			'file'=>DB::langDir().$lang.'.db',
			'flags'=>SQLITE3_OPEN_READONLY
		));
	}
	
	public static function &get(){
		return self::$lang;
	}
	
	public static function translate($string,$category){
		return self::$db->doSelectValue('SELECT t FROM t WHERE c=\''.$category.'\' AND s='.self::$db->escape($string).' LIMIT 1');
	}
}
CLang::init();