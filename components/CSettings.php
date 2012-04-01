<?php
class CSettings{
	private static $settings;
	
	public static function init(){
		self::$settings=include DATA.'settings.php';
	}
	
	public static function write(){
		file_put_contents(DATA.'settings.php','<?php return '.UPhp::exportCode(self::$settings).';');
	}
	
	public static function get($name){
		return self::$settings[$name];
	}
	
	public static function afterDeploy(){
		if(empty(self::$settings)) self::$settings=include APP.'config/basicSettings.php';
		else self::$settings+=include APP.'config/basicSettings.php';
		self::write();
	}
}
CSettings::init();