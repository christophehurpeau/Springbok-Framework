<?php
class CJobs{
	/*public static function isAlive($job,$args=array()){
		$lockfile = sys_get_temp_dir().DS.$daemon.'--'.$instance.'.daemonlock';
		return file_exists($lockfile) && posix_getsid(file_get_contents($lockfile)) !== false;
	}
	*/
	public static function start($job,$args=array()){
		return UExec::exec('php '.escapeshellarg(/*#if PROD*/APP/*#/if*//*#if false*/./*#/if*//*#if DEV */dirname(APP).DS/*#/if*/.'job.php').' '.escapeshellarg($job).' '
				.implode(' ',array_map('escapeshellarg',$args)).' 2>&1');
	}
	/*
	public static function startIfNotAlive($job,$args=array()){
		if(self::isAlive($daemon,$instance)) return false;
		return ($res=self::start($daemon,$instance))===''?true:$res;
	}
	
	public static function kill($job,$instance='default'){
		$lockfile = sys_get_temp_dir().DS.$daemon.'--'.$instance.'.daemonlock';
		if(!file_exists($lockfile)) return false;// posix_kill(file_get_contents($lockfile),SIGTERM) !== false;
		if(posix_getsid(($pid=file_get_contents($lockfile)))===false) return false;
		shell_exec('kill '.$pid);
		sleep(1);
		if(posix_getsid($pid)!==false) shell_exec('kill -9 '.$pid);
		UFile::rm($lockfile);
		return true;
	}*/
}