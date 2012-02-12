<?php
header('HTTP/1.1 503 Service Temporarily Unavailable',true,503);

include CORE.'springbok.php';

Springbok::$scriptname=substr(basename($_SERVER['SCRIPT_NAME']),0,-4);
define('BASE_URL',substr($_SERVER['SCRIPT_NAME'], 0,-strlen(Springbok::$scriptname)-5));
define('IS_HTTPS',/*isset($_SERVER['HTTPS']) ? */!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'/* : false (substr($_SERVER['SCRIPT_NAME'],0,8)=='https://'))*/);
if(isset($_SERVER['HTTP_HOST'])) define('FULL_BASE_URL','http'.( IS_HTTPS ? 's':'').'://'.$_SERVER['HTTP_HOST']);

class App{
	public static function configArray($name,$withSuffix=false){
		return include APP.'config/'.$name.($withSuffix ? '_'.ENV : '').'.php';
	}
	
	/** @return CLocale */
	public static function getLocale(){
		return CLocale::get('fr');
	}
	
	public static function run(){
		self::configArray('',true);
		if(isset(Config::$base))
			foreach(Config::$base as $name) include CORE.'base/'.$name.'.php';
		
		$vars=array();
		if(file_exists(APP.'views/maintenance.php')) render(APP.'views/maintenance.php',$vars);
		elseif(file_exists(CORE.'mvc/views/maintenance.php')) render(CORE.'mvc/views/maintenance.php',$vars);
		else echo "<h1>503 Service Temporarily Unavailable</h1>";
		
		ob_end_flush();
	}
	
	public static function shutdown(){}
}

App::run();