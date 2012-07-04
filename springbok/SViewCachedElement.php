<?php
class SViewCachedElement extends SViewElement{
	protected static $views=array('view');
	
	public static function generate(){
		$calledClass=get_called_class();
		$path=call_user_func_array($calledClass.'::path',$vars=func_get_args()).'_';
		$vars=call_user_func_array($calledClass.'::vars',$vars);
		include_once CORE.'mvc/views/View.php';
		foreach(static::$views as $view)
			file_put_contents($path.$view,render(APP.'viewsElements/'.substr($calledClass,1).'/'.$view.'.php',$vars,true));
	}
	public static function destroy(){
		$path=call_user_func_array(get_called_class().'::path',func_get_args()).'_';
		foreach(static::$views as $view)
			if(file_exists($path.$view)) unlink($path.$view);
	}
	
	protected $path;
	public function __construct($vars){
		$this->calledClass=get_called_class();
		$this->path=call_user_func_array($this->calledClass.'::path',$vars).'_';
		if(!$this->exists()){
			parent::__construct($vars);
			$this->generateAll();
		}
	}
	public function exists(){ return file_exists($this->path.'view'); }
	public function generateAll(){
		foreach(static::$views as $view) $this->write($view,parent::render($view));
	}
	
	
	public function render($view='view'){
		return $this->read($view);
	}
	public function incl($view='view',$vars=array()){
		return render($this->path.$view,$vars,true);
	}
	
	protected function read($view){
		return file_get_contents($this->path.$view);
	}
	protected function write($view,$content){
		return file_put_contents($this->path.$view,$content);
	}
}