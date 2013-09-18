<?php
/**
 * Select and set the current user language 
 *
 * <b>Configuration</b>
 * 
 * config/_.php
 * 
 * The 'defaultlang' conf define the lang used if no other has been found. The 'searchlang' conf is optional and define a list cf Selecting lang
 * <code>
 * return array(
 * 	'default_lang'=>'en',
 * 	'search_lang'=>array('session','cookie')
 * );
 * </code>
 * 
 * <b>Selecting lang</b>
 * 
 * The first time CLang is called, the component search for the lang using config 'search_lang'.
 * 
 * <ul>
 * <li>Session: search for $_SESSION['_lang']</li>
 * <li>Cookie: search for $_COOKIE['lang']</li>
 * <li>Urls: search among Config::$lang_urls to determine the lang</li>
 * </ul>
 * 
 * Urls : example
 * 
 * <code>
 * return array(
 * 	'search_lang'=>array('session','urls'),
 * 	'lang_urls'=>array('en.springbok-framework.com'=>'en','fr.springbok-framework.com'=>'fr')
 * );
 * </code>
 * 
 */
class CLang{
	private static $lang,$cache;
	/** @var DB */
	private static $db;
	
	public static function init(){
		if(isset(Config::$search_lang)) foreach(Config::$search_lang as $type){
			switch($type){
				case 'session':
					if(CSession::exists('_lang')){
						self::$lang=CSession::get('_lang');
						goto foundlang;
					}
					break;
				case 'cookie':
					if(CCookie::exists('lang')){
						self::$lang=CCookie::getLang();
						goto foundlang;
					}
					break;
				case 'urls':
					if(!empty($_SERVER['HTTP_HOST'])){
						if(isset(Config::$lang_urls[$_SERVER['HTTP_HOST']])){
							$langs=Config::$lang_urls[$_SERVER['HTTP_HOST']];
							self::$lang=is_array($langs) ? $langs[0] : $langs;
							goto foundlang;
						}
					}
					break;
			}
		}
		
		//$locale = Locale::acceptFromHttp($_SERVER('HTTP_ACCEPT_LANGUAGE'));
		//if($locale!==null)
		
		self::$lang=/*#if DEV */isset(App::$enhancing)&&App::$enhancing?(file_exists(dirname(APP).'/src/locales/fr.yml')?'fr':'en'):/*#/if*/Config::$availableLangs[0];
		foundlang:
		self::$db=DB::init('_lang',array(
			'type'=>'SQLite',
			'file'=>DB::langDir().self::$lang.'.db',
			'flags'=>SQLITE3_OPEN_READONLY
		));
	}
	
	public static function set($lang){
		if(isset(Config::$search_lang)) foreach(Config::$search_lang as $type){
			switch($type){
				case 'session':
					CSession::set('_lang',$lang);
					break;
				case 'cookie':
					CCookie::setLang($lang);
					break;
			}
		}
	}
	
	public static function get(){
		return self::$lang;
	}
	
	public static function _getAll(){
		return Config::$allLangs;
	}
	
	public static function getAvailable(){
		return Config::$availableLangs;
	}
	
	public static function getDefault(){
		return Config::$availableLangs[0];
	}
	
	public static function translate($string,$category){
		return isset(self::$cache[$category][$string]) ? self::$cache[$category][$string] : 
			self::$cache[$category][$string]=self::$db->doSelectValue('SELECT t FROM t WHERE c=\''.$category.'\' AND s='.self::$db->escape($string).' LIMIT 1');
	}
}
CLang::init();