<?php
/**
 * A view element cached in a store
 */
class SViewCachedElement extends SViewElement{
	protected static $views=array('view');
	
	/**
	 * @var string
	 */
	public static $store;
	
	/**
	 * Put all the views of the element in cache
	 * 
	 * @return void 
	 */
	public static function generate(){
		$calledClass=get_called_class();
		$path=call_user_func_array($calledClass.'::path',$vars=func_get_args());
		/*#if DEV*/if(!is_array($path)) throw new Exception('path is not an array : '.$path);/*#/if*/
		$vars=call_user_func_array($calledClass.'::vars',$vars);
		
		include_once CORE.'mvc/views/View.php';
		call_user_func(static::$store.'::writeAll',$calledClass,$calledClass::$views,$path,$vars);
	}
	
	/**
	 * Return the content of the generated view element
	 * 
	 * @param string
	 * @param string
	 * @param array
	 * @return string
	 */
	public static function renderFile($calledClass,$view,$vars){
		$vars['_viewName']=$view;
		return render(APP.'viewsElements/'.substr($calledClass,1).'/'.$view.'.php',$vars,true);
	}
	
	/**
	 * Destroy all the views of the element in the store
	 * @return void
	 */
	public static function destroy(){
		$path=call_user_func_array(get_called_class().'::path',func_get_args());
		call_user_func(static::$store.'::destroyAll',$path,static::$views);
	}
	
	/**
	 * @var SViewCacheStoreFile|SViewCacheStoreMongo
	 */
	protected $_store;
	/**
	 * @var array
	 */
	protected $_vars;
	
	/**
	 * @param array
	 * @return void
	 */
	public function __construct($vars){
		$this->calledClass=get_called_class();
		$this->_store=new static::$store($this,call_user_func_array($this->calledClass.'::path',$this->_vars=$vars));
		if($this->exists()!==true)
			$this->generateAll();
		$this->_store->preinit();
	}
	
	/**
	 * @return void
	 */
	public function loadVars($v=null){
		if($this->vars===null) parent::loadVars($this->_vars);
	}
	
	/**
	 * @param bool
	 * @return void
	 */
	public function generateAll($close=true){
		$this->loadVars();
		try{
			include_once CORE.'mvc/views/View.php';
			$this->_store->init();
			foreach(static::$views as $view) $this->_store->write($view,parent::render($view));
			$this->_store->end($close);
		}catch(Exception $e){
			/*#if DEV */ throw $e; /*#/if*/
			$this->_store->removeAll(static::$views);
		}
	}
	
	/**
	 * @return bool
	 */
	public function exists(){
		/*#if DEV*/ return false;/*#/if*/
		return $this->_store->exists();
	}
	
	/**
	 * Read a view in the store
	 * 
	 * @param string
	 * @return string
	 */
	public function render($view='view'){
		return $this->_store->read($view);
	}
	
	/**
	 * Include a view in the store
	 * 
	 * @param string
	 * @param array
	 * @return string
	 */
	public function incl($view='view',$vars=array()){
		return $this->_store->incl($view,$vars);
	}
}
SViewCachedElement::$store=Config::$cacheStore;
if(!call_user_func(SViewCachedElement::$store.'::isAvailable'))
	SViewCachedElement::$store='SViewCacheStoreFileAlwaysRerender';
