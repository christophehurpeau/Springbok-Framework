<?php
class CWikipedia{
	private static $cache;
	public static $context;
	
	public static function init(){
		self::$context=stream_context_create(array(
				'http'=>array(
					'user_agent'=>'Mozilla/5.0 (Ubuntu; X11; Linux x86_64; rv:8.0) Gecko/20100101 Firefox/8.0',
				)
			));
		self::$cache=CCache::get('Wikipedia');
	}
	
	public static function getPage($name){
		$data=self::$cache->readOrWrite($name,function() use (&$name){
			return file_get_contents('http://fr.wikipedia.org/w/api.php?action=parse&page='.$name.'&format=php',0,CWikipedia::$context);
		});
		return unserialize($data);
	}
	
	public static function export($name){
		'http://fr.wikipedia.org/wiki/Spécial:Exporter/';
		return ;
	}
}
CWikipedia::init();