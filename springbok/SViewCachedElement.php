<?php
class SViewCachedElement extends SViewElement{
	protected static $views=array('view');
	public static $store;
	
	public static function generate(){
		$calledClass=get_called_class();
		$path=call_user_func_array($calledClass.'::path',$vars=func_get_args());
		/*#if DEV*/if(!is_array($path)) throw new Exception('path is not an array : '.$path);/*#/if*/
		$vars=call_user_func_array($calledClass.'::vars',$vars);
		
		include_once CORE.'mvc/views/View.php';
		$vars=call_user_func(static::$store.'::writeAll',$calledClass,$calledClass::$views,$path,$vars);
	}
	
	
	public static function renderFile($calledClass,$view,$vars){
		return render(APP.'viewsElements/'.substr($calledClass,1).'/'.$view.'.php',$vars,true);
	}
	
	public static function destroy(){
		$path=call_user_func_array(get_called_class().'::path',func_get_args());
		call_user_func(static::$store.'::destroyAll',$path,static::$views);
	}
	
	protected $_store,$_vars;
	public function __construct($vars){
		$this->calledClass=get_called_class();
		$this->_store=new static::$store($this,call_user_func_array($this->calledClass.'::path',$this->_vars=$vars));
		if($this->exists()!==true)
			$this->generateAll();
		$this->_store->preinit();
	}
	public function loadVars($v=null){
		if($this->vars===null) parent::loadVars($this->_vars);
	}
	public function generateAll($close=true){
		$this->loadVars();
		try{
			include_once CORE.'mvc/views/View.php';
			$this->_store->init();
			foreach(static::$views as $view) $this->_store->write($view,parent::render($view));
			$this->_store->end($close);
		}catch(Exception $e){
			/*#if DEV */ throw $e; /*#/if*/
			foreach(static::$views as $view) UFile::rm($this->path.$view);
		}
	}
	
	public function exists(){
		/*#if DEV*/ return false;/*#/if*/
		return $this->_store->exists();
	}
	
	public function render($view='view'){
		return $this->_store->read($view);
	}
	
	public function incl($view='view',$vars=array()){
		return $this->_store->incl($view,$vars);
	}
}
SViewCachedElement::$store=Config::$cacheStore;
if(!call_user_func(SViewCachedElement::$store.'::isAvailable'))
	SViewCachedElement::$store='SViewCacheStoreFileAlwaysRerender';
