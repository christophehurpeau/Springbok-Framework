<?php
/**
 * View Element Store Mongo
 * 
 * This allow cached elements to be stored in mongo database
 */
class SViewCacheStoreMongo{
	private static $db;
	
	/**
	 * Return false if mongo is not available... but then I guess the app wouldn't work anyway.
	 * But it could just use mongo for caching
	 * 
	 * @return bool
	 */
	public static function isAvailable(){
		return self::$db!==false;
	}
	
	/**
	 * @internal
	 * Try to access to mongo
	 * 
	 * @return void
	 */
	public static function _initDb(){
		try{
			self::$db=DB::init('cache');
		}catch(Exception $e){
			usleep(200);
			try{
				self::$db=DB::init('cache');
			}catch(Exception $e){
				self::$db=false;
			}
		}
	}
	
	/**
	 * @param string
	 * @param array
	 * @return void
	 */
	public static function destroyAll($path,$views){
		self::$db->collection($path[0])->remove(array('_id'=>$path[1]),array('justOne'=>true));
	}
	
	/**
	 * @param string
	 * @param array
	 * @param string
	 * @param array
	 * @return void
	 */
	public static function writeAll($calledClass,$views,$path,$vars){
		$data=array('date'=>new MongoDate);
		foreach($views as $view) $data[$view]=SViewCachedElement::renderFile($calledClass,$view,$vars);
		self::$db->collection($path[0])->update(array('_id'=>$path[1]),$data,array('w'=>0,'upsert'=>true));
	}
	
	private $ve,$collection,$id,$data;
	
	/**
	 * @param SViewCachedElement
	 * @param array [0=>collection, 1=>_id]
	 */
	public function __construct($ve,$path){
		$this->ve=$ve;
		$this->collection=self::$db->collection($path[0]);
		$this->id=$path[1];
	}
	
	/**
	 * @return void
	 */
	public function preinit(){
	}
	
	/**
	 * Return if the element already exists in the cache
	 * 
	 * @return bool
	 */
	public function exists(){
		return $this->collection->findOne(array('_id'=>$this->id),array('_id')) !== null;
	}
	
	/**
	 * @param string
	 * @param array
	 * @return string
	 */
	public function incl($view,$vars){
		extract($vars);
		$res=$this->read($view);
		if(substr($res,0,5)==='<?php') $res=substr($res,5);
		else $res='?>'.$res;
		ob_start();
		eval($res);
		return ob_get_clean();
	}
	
	/**
	 * @param string
	 * @return string
	 */
	public function read($view){
		if($this->data!==null) return $this->data[$view];
		$res=$this->collection->findOne(array('_id'=>$this->id),array($view));
		return $res[$view];
	}
	
	/**
	 * @param string
	 * @param string
	 * @return void
	 */
	public function write($view,$content){
		$this->data[$view]=$content;
	}
	
	/**
	 * @param array
	 * @return void
	 */
	public function removeAll($views){
		$this->collection->remove(array('_id'=>$this->id),array('justOne'=>true));
	}
	
	/**
	 * @return void
	 */
	public function init(){
		$this->data=array();
	}

	/**
	 * @return void
	 */
	public function end(){
		$this->data['_id'] = $this->id;
		$this->data['date'] = new MongoDate;
		$this->collection->update(array('_id'=>$this->id),$this->data,array('w'=>0,'upsert'=>true));
	}
}
SViewCacheStoreMongo::_initDb();
