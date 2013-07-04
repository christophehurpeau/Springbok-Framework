<?php
class SViewCacheStoreFile{
	public static function isAvailable(){
		return true;
	}
	
	private static function writeStatic($path,$view,$content){
		file_put_contents($path.'_'.$view,$content);
	}
	public static function destroyAll($path,$views){
		$path=self::path($path);
		foreach($views as $view)
			if(file_exists($path.'_'.$view)) UFile::rm($path.'_'.$view);
	}
	
	public static function writeAll($calledClass,$views,$path,$vars){
		$path=self::path($path);
		foreach($views as $view)
			self::writeStatic($path,$view,SViewCachedElement::renderFile($calledClass,$view,$vars));
	}
	
	private static function path($path){
		return DATA.'elementsCache/'.$path[0].'/'.$path[1];
	}
	
	private $ve,$path,$_file;
	
	public function __construct($ve,$path){
		$this->path=self::path($path);
		$this->ve=$ve;
	}
	
	public function preinit(){
		try{
			$this->_file=UFile::open($this->path.'_view','rb');
		}catch(ErrorException $e){
			$this->ve->generateAll(false);
		}
		$this->_file->lockShared();
	}
	
	public function __destruct(){
		if($this->_file!==null){
			$this->_file->unlock();
			$this->_file->close();
		}
	}
	
	public function exists(){
		return file_exists($this->path.'_view');
	}
	
	public function incl($view,$vars){
		try{
			return render($this->path.'_'.$view,$vars,true);
		}catch(ErrorException $e){ //try again (if cache is currently removed, can occur)
			$this->ve->generateAll(false);
			return render($this->path.'_'.$view,$vars,true);
		}
	}
	
	public function read($view){
		if($view==='view') return $this->_file->read();
		try{
			return file_get_contents($this->path.'_'.$view);
		}catch(ErrorException $e){ //try again (if cache is currently removed, can occur)
			$this->ve->generateAll(false);
			return file_get_contents($this->path.'_'.$view);
		}
	}
	public function write($view,$content){
		return $view==='view' ? $this->_file->write($content) : self::writeStatic($this->path,$view,$content);
	}
	
	public function removeAll($views){
		foreach($views as $view) UFile::rm($this->path.'_'.$view);
	}
	
	public function init(){
		$this->_file=UFile::open($this->path.'_view','w');
		$this->_file->lockExclusive();
	}

	public function end($close=true){
		$this->_file->unlock();
		if($close===true) $this->_file->close();
	}
}
