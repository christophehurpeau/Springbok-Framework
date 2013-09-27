<?php
class CMeteorologic{
	private static $cache;
	
	public static function init(){
		self::$cache=CCache::get('Meteorologic');
	}
	
	public static function getPrevisions($cp){
		return self::$cache->readOrWrite($cp,function() use (&$cp){
			return file_get_contents('http://api.meteorologic.net/forecarss?p='.$cp);
		});
	}
}
CMeteorologic::init();