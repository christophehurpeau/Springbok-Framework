<?php
/**
 * Keep changeable settings in data/settings.php
 */
class CSettings{
	private static $settings;
	
	/** @ignore */
	public static function init(){
		self::$settings=include DATA.'settings.php';
	}
	
	/**
	 * Write the changes
	 * 
	 * @return void
	 */
	public static function write(){
		file_put_contents(DATA.'settings.php','<?php return '.UPhp::exportCode(self::$settings).';');
	}
	
	/**
	 * Get a setting
	 * 
	 * @param string
	 * @return mixed
	 */
	public static function get($name){
		return self::$settings[$name];
	}
	
	/**
	 * Get a boolean settings
	 * 
	 * @param string
	 * @return bool
	 */
	public static function is($name){
		return (bool)self::$settings[$name];
	}
	
	/**
	 * Executed after deployment : set the missing settings from config/basicSettings.php
	 */
	public static function afterDeploy(){
		if(empty(self::$settings)) self::$settings=include APP.'config/basicSettings.php';
		else self::$settings+=include APP.'config/basicSettings.php';
		self::write();
	}
}
CSettings::init();