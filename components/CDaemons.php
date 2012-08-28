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
		return ($res=self::start($daemon,$instance))===''?true:$res;
	}
	
	public static function kill($daemon,$instance='default'){
		$lockfile = sys_get_temp_dir().DS.$daemon.'--'.$instance.'.daemonlock';
		if(!file_exists($lockfile)) return false;// posix_kill(file_get_contents($lockfile),SIGTERM) !== false;
		if(posix_getsid(($pid=file_get_contents($lockfile)))===false) return false;
		shell_exec('kill '.$pid);
		sleep(1);
		if(posix_getsid($pid)!==false) shell_exec('kill -9 '.$pid);
		UFile::rm($lockfile);
		return true;
	}
	
	public static function startAll(){
		return UExec::exec('php '.escapeshellarg(APP.'cli.php').' daemons');
	}
}
CDaemons::init();