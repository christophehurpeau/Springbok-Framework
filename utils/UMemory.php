<?php
/** Memory utils */
class UMemory{
	private static $memoryLimit=null;
	
	/**
	 * Return the memory limit defined in configuration file
	 * 
	 * @return int octets
	 */
	public static function memoryLimit(){
		if(self::$memoryLimit!==null) return self::$memoryLimit;
		$val = trim(ini_get('memory_limit'));
		$last = strtolower($val[strlen($val)-1]);
		switch($last) {
			case 'g':
				$val *= 1024;
			case 'm':
				$val *= 1024;
			case 'k':
				$val *= 1024;
		}
		return self::$memoryLimit=$val;
	}
	
	/**
	 * Return if memory is closed to reach the limit defined in the configuration file
	 * 
	 * @return bool
	 */
	public static function isClosedToReachMemoryLimit(){
		$memLimit=self::memoryLimit();
		return ($memLimit - ($memLimit*5/100)) < memory_get_usage();
	}
}