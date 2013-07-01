<?php
class SViewCacheStoreMongo{
	private static $db;
	
	public static function isAvailable(){
		return self::$db!==false;
	}
	public static function _initDb(){
		try{
			self::$db=DB::init('cache');
		}catch(Exception $e){
			self::$db=false;
		}
	}
	
	public static function destroyAll($path,$views){
		self::$db->collection($path[0])->remove(array('_id'=>$path[1]),array('justOne'=>true));
	}
	
	public static function writeAll($calledClass,$views,$path,$vars){
		$data=array('_id'=>$path[1]);
		foreach($views as $view) $data[$view]=SViewCachedElement::renderFile($calledClass,$view,$vars);
		self::$db->collection($path[0])->update($data,array('w'=>0,'upsert'=>true));
	}
	
	private $ve,$collection,$id,$data;
	
	public function __construct($ve,$path){
		$this->ve=$ve;
		$this->collection=self::$db->collection($path[0]);
		$this->id=$path[1];
	}
	
	public function preinit(){
	}
	
	public function exists(){
		return $this->collection->findOne(array('_id'=>$this->id),array('_id')) !== null;
	}
	
	public function incl($view,$vars){
		extract($vars);
		$res=$this->read($view);
		if(substr($res,0,5)==='<?php') $res=substr($res,5);
		else $res='?>'.$res;
		ob_start();
		eval($res);
		return ob_get_clean();
	}
	
	public function read($view){
		if($this->data!==null) return $this->data[$view];
		$res=$this->collection->findOne(array('_id'=>$this->id),array($view));
		return $res[$view];
	}
	public function write($view,$content){
		$this->data[$view]=$content;
	}
	
	public function init(){
		$this->data=array();
	}

	public function end(){
		$this->data['_id']=$this->id;
		$this->collection->update(array('_id'=>$this->id),$this->data,array('w'=>0,'upsert'=>true));
	}
}
SViewCacheStoreMongo::_initDb();
