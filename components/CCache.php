<?php
/**
 * Component Cache
 * 
 * Configure your caches in the config/caches.php file
 */
abstract class CCache{
	private static $_instances,$_config;
	
	/** @ignore */
	public static function init(){
		self::$_config=App::configArray('caches');
	}

	/**
	 * Detect the best memory cache available
	 * 
	 * @return CCache 
	 */
	public static function detect(){
		if(!isset(self::$_instances['detected'])){
			//TODO on depl ?
			$exts=array_intersect(array('apc','xcache'),array_map('strtolower',get_loaded_extensions()));
			$config=array('type'=>array_shift($ref)?:'folder');
			self::$_instances['detected']=CCache::create($config);
		}
		return self::$_instances['detected'];
	}
	
	/* -- -- -- */
	
	/**
	 * Return an instance from the config
	 * 
	 * @param string
	 * @return CCache
	 */
	public static function get($cacheName='_'){
		if(!isset(self::$_instances[$cacheName]))
			self::$_instances[$cacheName]=CCache::create(self::$_config[$cacheName]);
		return self::$_instances[$cacheName];
	}
	
	protected $_expiration;
	
	public static function create($config){
		if(!isset($config['expiration'])) $config['expiration']=null;
		$instanceClassName='CCache_'.$config['type'];
		$instance=new $instanceClassName($config);
		return $instance;
	}
	
	/**
	 * Read from cache
	 * 
	 * @param mixed
	 * @return mixed
	 */
	public abstract function read($key);
	
	/**
	 * Write to cache
	 * 
	 * @param mixed
	 * @return mixed
	 */
	public abstract function write($key,$data);
	
	/**
	 * Delete in cache
	 * 
	 * @param mixed
	 * @return mixed
	 */
	public abstract function delete($key);
	
	/**
	 * Read in the cache. If not available, call the callback.
	 * 
	 * @param mixed
	 * @param function
	 * @return cache value
	 */
	public function readOrWrite($key,$callback){
		$cache=$this->read($key);
		if($cache===null){
			$cache=$callback();
			$this->write($key,$cache);
		}
		return $cache;
	}
	
	/**
	 * @param int
	 */
	protected function setExpiration($expiration){
		if($expiration===null) $this->_expiration=/*#if DEV */120/*#/if*//*#if false*/+/*#/if*//*#if PROD*/3600/*#/if*/;
		else $this->_expiration=$expiration;
	}
	
	/**
	 * @param mixed
	 * @return string
	 */
	protected static function serializeWithTime($data){
		return gzdeflate(serialize(array(time(),$data)));
	}
}
CCache::init();
