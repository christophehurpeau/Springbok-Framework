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
			if(file_exists($path.$view)) UFile::rm($path.$view);
	}
	
	protected $path,$_file;
	public function __construct($vars){
		$this->calledClass=get_called_class();
		$this->path=call_user_func_array($this->calledClass.'::path',$vars).'_';
		if($this->exists()!==true){
			parent::__construct($vars);
			$this->generateAll();
		}
		try{
			$this->_file=UFile::open($this->path.'view','rb');
		}catch(ErrorException $e){
			parent::__construct($vars);
			$this->generateAll();
			$this->_file=UFile::open($this->path.'view','rb');
		}
		$this->_file->lockShared();
		
	}
	public function __destruct(){
		if($this->_file!==null){
			$this->_file->unlock();
			$this->_file->close();
		}
	}
	public function exists(){ return true; }
	public function generateAll(){
		try{
			include_once CORE.'mvc/views/View.php';
			$this->_file=UFile::open($this->path.'view','w');
			$this->_file->lockExclusive();
			foreach(static::$views as $view) $this->write($view,parent::render($view));
			$this->_file->unlock();
			$this->_file->close();
		}catch(Exception $e){
			foreach(static::$views as $view)
				if(file_exists($this->path.$view)) UFile::rm($this->path.$view);
		}
	}
	
	
	public function render($view='view'){
		return $this->read($view);
	}
	public function incl($view='view',$vars=array()){
		return render($this->path.$view,$vars,true);
	}
	
	protected function read($view){
		return $view==='view' ? $this->_file->read() : file_get_contents($this->path.$view);
	}
	protected function write($view,$content){
		return $view==='view' ? $this->_file->write($content) : file_put_contents($this->path.$view,$content);
	}
}