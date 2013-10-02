<?php
/**
 * View Element Store File
 * 
 * This allow cached elements to be stored in files
 */
class SViewCacheStoreFile{
	/**
	 * This store is always available
	 * 
	 * @return true
	 */
	public static function isAvailable(){
		return true;
	}
	
	/**
	 * @param string
	 * @param string
	 * @param string
	 * @return void
	 */
	private static function writeStatic($path,$view,$content){
		file_put_contents($path.'_'.$view,$content);
	}
	
	/**
	 * Destroy all files corresponding to one view element
	 * 
	 * @param string
	 * @param array
	 * @return void
	 */
	public static function destroyAll($path,$views){
		$path=self::path($path);
		foreach($views as $view)
			if(file_exists($path.'_'.$view)) UFile::rm($path.'_'.$view);
	}
	
	/**
	 * Write all files corresponding to one view element, for each views
	 * 
	 * @param string
	 * @param array
	 * @param string
	 * @param array
	 */
	public static function writeAll($calledClass,$views,$path,$vars){
		$path=self::path($path);
		foreach($views as $view)
			self::writeStatic($path,$view,SViewCachedElement::renderFile($calledClass,$view,$vars));
	}
	
	/**
	 * @param string
	 * @return string
	 */
	private static function path($path){
		return DATA.'elementsCache/'.$path[0].'/'.$path[1];
	}
	
	private $ve,$path,$_file;
	
	/**
	 * @param SViewCachedElement
	 * @param array [0=>folder, 1=>filename]
	 */
	public function __construct($ve,$path){
		$this->path=self::path($path);
		$this->ve=$ve;
	}
	
	/**
	 * Lock shared file
	 * 
	 * @return void
	 */
	public function preinit(){
		try{
			$this->_file=UFile::open($this->path.'_view','rb');
		}catch(ErrorException $e){
			$this->ve->generateAll(false);
		}
		$this->_file->lockShared();
	}
	
	/**
	 * Unlock file
	 * 
	 * @return void
	 */
	public function __destruct(){
		if($this->_file!==null){
			$this->_file->unlock();
			$this->_file->close();
		}
	}
	
	/**
	 * @return bool
	 */
	public function exists(){
		return file_exists($this->path.'_view');
	}
	
	/**
	 * @param string
	 * @param array
	 * @return string
	 */
	public function incl($view,$vars){
		try{
			return render($this->path.'_'.$view,$vars,true);
		}catch(ErrorException $e){ //try again (if cache is currently removed, can occur)
			$this->ve->generateAll(false);
			return render($this->path.'_'.$view,$vars,true);
		}
	}
	
	/**
	 * @param string
	 * @return string
	 */
	public function read($view){
		if($view==='view') return $this->_file->read();
		try{
			return file_get_contents($this->path.'_'.$view);
		}catch(ErrorException $e){ //try again (if cache is currently removed, can occur)
			$this->ve->generateAll(false);
			return file_get_contents($this->path.'_'.$view);
		}
	}
	
	/**
	 * @param string
	 * @param string
	 * @return mixed
	 */
	public function write($view,$content){
		return $view==='view' ? $this->_file->write($content) : self::writeStatic($this->path,$view,$content);
	}
	
	/**
	 * @param array
	 */
	public function removeAll($views){
		foreach($views as $view) UFile::rm($this->path.'_'.$view);
	}
	
	/**
	 * Lock for write
	 * 
	 * @return void
	 */
	public function init(){
		$this->_file=UFile::open($this->path.'_view','w');
		$this->_file->lockExclusive();
	}
	
	/**
	 * End
	 * 
	 * @param bool if the file ressource should be closed
	 * @return void
	 */
	public function end($close=true){
		$this->_file->unlock();
		if($close===true) $this->_file->close();
	}
}
