<?php
class FSecureAdmin extends CSecure{
	private static $_config,$_user;
	
	public static function init(){
		self::$_config=self::loadConfig('secure-admin');
	}
	
	protected static function issetConfig($name){ return isset(self::$_config[$name]); }
	protected static function &config($name){ return self::$_config[$name]; }
	public static function &user(){ return self::$_user; }
	
	
	public static function createCookie($user){
		self::loadCookie();
		self::$_cookie->admin=true;
		parent::createCookie($user);
	}
	
	protected static function checkCookie(){
		return isset(self::$_cookie->admin);
	}
}
FSecureAdmin::init();