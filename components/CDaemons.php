<?php
class CDaemons{
	private static $_config;
	
	public static function init(){
		self::$_config=App::configArray('daemons');
	}
	
	public static function getAll(){
		return self::$_config;
	}
	
	public static function isAlive($daemon,$instance='default'){
		$lockfile = sys_get_temp_dir().DS.$daemon.'--'.$instance.'.daemonlock';
		return file_exists($lockfile) && posix_getsid(file_get_contents($lockfile)) !== false;
	}
	
	public static function start($daemon,$instance='default'){
		return UExec::exec('php '.escapeshellarg(CORE.'daemon.php').' '.escapeshellarg(APP).' '.escapeshellarg($daemon).' '.escapeshellarg($instance).' 1>/dev/null 2>&1');
	}
	
	public static function startIfNotAlive($daemon,$instance='default'){
		if(self::isAlive($daemon,$instance)) return false;
		return self::start($daemon,$instance);
	}
	
	public static function startAll(){
		return UExec::exec('php '.escapeshellarg(APP.'cli.php').' daemons');
	}
}
CDaemons::init();