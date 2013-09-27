<?php
/**
 * A simple HttpClient with cached pages
 */
class CSimpleCachedHttpClient{
	private static $cache;
	public static $context;
	
	/** @ignore */
	public static function init(){
		self::$context=stream_context_create(array(
				'http'=>array(
					'user_agent'=>'Mozilla/5.0 (Ubuntu; X11; Linux x86_64; rv:8.0) Gecko/20100101 Firefox/8.0',
				)
			));
		self::$cache=CCache::create(array(
			'type'=>'TextFile', 'path'=>'simple_http_client/','expiration'=>360000//10h*10
		));
	}
	
	public static function getPage($url,$sleep=false){
		return self::$cache->readOrWrite(urlencode($url),function() use($url,$sleep){
			if($sleep!==false) sleep($sleep);
			return file_get_contents($url,0,CSImpleCachedHttpClient::$context);
		});
	}
}
CSimpleCachedHttpClient::init();