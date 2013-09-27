<?php
/**
 * Wikipedia Component
 * 
 * Retrieve pages from Wikipedia (fr)
 * 
 */
class CWikipedia{
	public static $WAIT_TIME=17,$cache,$httpClient,$lastContactTime;
	
	/**
	 * @param CHttpClient
	 * @param bool
	 * @return void
	 */
	public static function init($httpClient,$sleep=true){
		self::$cache=CCache::get('Wikipedia');
		self::$httpClient=&$httpClient;
		self::$lastContactTime=$sleep ? 0 : false;
	}
	
	/**
	 * @return void
	 */
	public static function sleep(){
		if(self::$lastContactTime===false) return;
		$lastContactTime=microtime(true);
		$time=$lastContactTime-self::$lastContactTime;
		if($time < self::$WAIT_TIME) usleep((self::$WAIT_TIME-$time)*1000000);
		self::$lastContactTime=microtime(true);
	}
	
	/**
	 * Read a page from cache or get it from wikipedia
	 * 
	 * @param string
	 * @return mixed
	 */
	public static function getPage($name){
		$name=str_replace(' ','_',$name);
		$data=self::$cache->readOrWrite($name,function() use (&$name){
			CWikipedia::sleep();
			return CWikipedia::$httpClient->get('http://fr.wikipedia.org/w/api.php?action=parse&page='.$name.'&format=php');
		});
		return unserialize($data);
	}
	
	/**
	 * Read a source page from cache or get it from wikipedia
	 * 
	 * @param string
	 * @return mixed
	 */
	public static function getPageSource($name){
		$name=str_replace(' ','_',$name);
		$data=self::$cache->readOrWrite($name,function() use (&$name){
			CWikipedia::sleep();
			return CWikipedia::$httpClient->get('http://fr.wikipedia.org/wiki/Spécial:Exporter/'.$name);
		});
		return simplexml_load_string($data);
	}
	
	/**
	 * Return the real url of a wikipedia file
	 * 
	 * @param string
	 * @return string
	 */
	public static function urlFile($name){
		return 'http://fr.wikipedia.org/wiki/Special:FilePath/'.str_replace(' ', '_',$name);
		$filename=str_replace(' ', '_',$name);
		$digest=md5($filename);
		$folder=$digest[0].'/'.$digest[0].$digest[1].'/'.$filename;
		return 'http://upload.wikimedia.org/wikipedia/commons/'.$folder;
	}
	
	/**
	 * Return the content of a wikipedia file
	 * 
	 * @param string
	 * @return string
	 */
	public static function getFile($name){
		return CWikipedia::$httpClient->get(self::urlFile($name));
	}
}