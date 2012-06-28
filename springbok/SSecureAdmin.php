<?php
class SSecureAdmin extends CSecure{
	private static $_config;
	
	public static function init(){
		self::$_config=self::loadConfig('secure-admin');
	}
	
	protected static function issetConfig($name){ return isset(self::$_config[$name]); }
	public static function config($name){ return self::$_config[$name]; }
	
	
	public static function createCookie($user){
		self::loadCookie();
		self::$_cookie->admin=true;
		parent::createCookie($user);
	}
}
SSecureAdmin::init();