<?php
class UProfiling{
	public static function execute($nbIt,$callback){
		$t=microtime(true);
		for($i=0; $i < $nbIt; $i++) $callback();
		return microtime(true) - $t;
	}
	
	public static function compare($nbIt/* HIDE */,$callbacks/* /HIDE */){
		$callbacks=func_get_args();
		unset($callbacks[0]);
		$res=array();
		foreach($callbacks as $key=>$callback){
			$res[$key]=self::execute($nbIt,$callback);
		}
		return $res;
	}
}