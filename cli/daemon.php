<?php
if(empty($argv[1])) echo 'What daemon ?';
elseif(empty($argv[2])) echo 'What instance ?';
else{
	$logger=CLogger::get('daemon');
	
	$lockfile = sys_get_temp_dir().DS.$argv[1].'--'.$argv[2].'.daemonlock';
	if(file_exists($lockfile)){
		$pid = file_get_contents($lockfile);
		if (posix_getsid($pid) !== false){
			$logger->log($argv[1].' - '.$argv[2].': PID is still alive! cannot run twice!');
			exit;
		}
	}
	
	abstract class Daemon{
		public static function start($instanceName){}
		
		public static function _exit(){}
		public static function _restart(){}
		
		protected static function check(){
			
			gc_collect_cycles();
		}
	}
	
	
	$pid = pcntl_fork();
	if($pid == -1) exit('pcntl_fork failed');
	elseif($pid) exit; //père
	else{
		//Fait du processus courant un chef de session
		posix_setsid();
		file_put_contents($lockfile, getmypid()); // create lockfile
		
		function sig_handler($signo){
			if($signo===SIGTERM || $signo===SIGINT){
				CLogger::get('daemon')->log(/*$argv[1].'--'.$argv[2].': '.*/($signo===SIGTERM?'SIGTERM':'SIGINT'));
				Daemon::_exit();
				exit;
			}elseif($signo===SIGHUP){
				CLogger::get('daemon')->log(/*$argv[1].'--'.$argv[2].*/': SIGUP');
				Daemon::_restart();
			}elseif($signo===SIGCHLD) pcntl_waitpid(-1, $status);
		}
		declare(ticks = 1);
		pcntl_signal(SIGTERM, 'sig_handler');
		pcntl_signal(SIGINT, 'sig_handler');
		pcntl_signal(SIGCHLD, 'sig_handler');
		
		$logger->log($argv[1].': START');
		$className=$argv[1].'Daemon';
		include APP.'daemons/'.$className.'.php';
		$className::start($argv[2]);
		$logger->log($argv[1].': END');
	}
}