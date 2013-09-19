<?php
/** Utils for profiling */
class UProfiling{
	
	/**
	 * Execute a callback $nbIt times and return the times it took
	 * 
	 * @param int
	 * @param function
	 * @return int
	 */
	public static function execute($nbIt,$callback){
		$t=microtime(true);
		for($i=0; $i < $nbIt; $i++) $callback();
		return microtime(true) - $t;
	}
	
	/**
	 * Execute a list of callbacks
	 * 
	 * @param int
	 * @param function...
	 * @return array
	 */
	public static function compare($nbIt/*#if false*/,$callbacks/*#/if*/){
		$callbacks=func_get_args();
		unset($callbacks[0]);
		$res=array();
		foreach($callbacks as $key=>$callback){
			$res[$key]=self::execute($nbIt,$callback);
		}
		return $res;
	}
}