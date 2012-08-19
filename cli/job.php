<?php
if(empty($argv[1])) echo 'What job ?';
else{
	$jobName=$argv[1];
	unset($argv[0],$argv[1]);
	$argv=array_values($argv);
	
	$logger=CLogger::get('jobs');
	
	$lockfile = sys_get_temp_dir().DS.$jobName.'.joblock'.(empty($argv)?'':'.'.md5(implode('¤',$argv)));
	if(file_exists($lockfile)){
		$pid = file_get_contents($lockfile);
		if (posix_getsid($pid) !== false){
			$logger->log($jobName.' : PID is still alive! can not run twice!');
			exit;
		}
	}
	file_put_contents($lockfile, getmypid()); // create lockfile
	
	abstract class Job{
		public static function doJob(){}
	}
	
	$logger->log($jobName.' : START');
	$className=$jobName.'Job';
	include APP.'jobs/'.$className.'.php';
	call_user_func_array(array($className,'main'),$argv);
	$logger->log($jobName.' : END');
}