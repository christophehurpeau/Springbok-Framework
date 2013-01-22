<?php
class UMemory{
	private static $memoryLimit=null;
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
	
	public static function isClosedToReachMemoryLimit(){
		$memLimit=self::memoryLimit();
		return ($memLimit - ($memLimit*5/100)) < memory_get_usage();
	}
}