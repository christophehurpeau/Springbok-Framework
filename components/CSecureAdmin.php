<?php
/**
 * Class for admin secure
 * 
 * Load the config secure-admin.php instead of secure.php
 * 
 * Create a cookie with the admin info
 */
class CSecureAdmin extends CSecure{
	private static $_config;
	
	/** @ignore */
	public static function init(){
		self::$_config=self::loadConfig('secure-admin');
	}
	
	/**
	 * @param string
	 * @return bool
	 */
	protected static function issetConfig($name){
		return isset(self::$_config[$name]);
	}
	
	/**
	 * @param string
	 * @return mixed
	 */
	public static function config($name){
		return self::$_config[$name];
	}
	
	
	/**
	 * @param SModel
	 * @return void
	 */
	public static function createCookie($user){
		self::loadCookie();
		self::$_cookie->admin=true;
		parent::createCookie($user);
	}
}
CSecureAdmin::init();