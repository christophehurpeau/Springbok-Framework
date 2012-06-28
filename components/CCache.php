<?php
abstract class CCache{
	private static $_instances,$_config;
	public static function init(){
		self::$_config=App::configArray('caches');
	}

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
	
	public abstract function read($key);
	public abstract function write($key,$data);
	public abstract function delete($key);
	
	public function readOrWrite($key,$callback){
		$cache=$this->read($key);
		if($cache===null){
			$cache=$callback();
			$this->write($key,$cache);
		}
		return $cache;
	}
	
	
	protected function setExpiration($expiration){
		if($expiration===null) $this->_expiration=/* DEV */120/* /DEV *//* HIDE */+/* /HIDE *//* PROD */3600/* /PROD */;
		else $this->_expiration=$expiration;
	}
	
	protected static function serializeWithTime($data){
		return gzdeflate(serialize(array(time(),$data)));
	}
}
CCache::init();
