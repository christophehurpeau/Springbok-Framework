<?php
class CLangCore{
	private static $translations;
	public static function init(){
		self::$translations=include CORE.'i18n/langs/'.CLang::get().'.php';
	}
	
	public static function translate($string){
		return self::$translations[$string];
	}
}
CLangCore::init();