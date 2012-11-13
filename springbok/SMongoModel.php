<?php
class SMongoModel extends SModel{
	public static function init($modelName){
		parent::init($modelName);
		$modelName::$__collection=$modelName::$__modelDb->collection($modelName);
	}
	
	public static function _fullTableName(){
		return static::$__className;
	}
	public function _getPkName(){
		return '_id';
	}
	public function _getPkValue(){
		return $this->data['_id'];
	}
	public function _pkExists(){
		return isset($this->data['_id']);
	}
	
	
	
	/* http://us2.php.net/manual/en/mongocollection.insert.php */
	public function insert(){
		if(!$this->_beforeInsert()) return false;
		$data=$this->data=static::InsertOne($data=$this->_getData());
		$this->_afterInsert($data);
		return $data['_id'];
	}
	
	public function save(){
		throw new Exception();
		if(!$this->beforeSave()) return false;
		$data=$this->_getData();
		//$data['created']=new MongoDate();
		//static::$__collection->save($data);
		if(isset($data['_id']))
			static::$__collection->save(array('_id'=>$data['_id']),$data);
		else{
			$data=$this->data=static::InsertOne($data);
		}
		$this->afterSave($data);
	}
	
	
	public function update(){
		if(!$this->_beforeUpdate()) return false;
		$data=$this->_getData();
		$data['updated']=new MongoDate();
		static::$__collection->save($data);
		$this->_afterUpdate($data);
	}
	
	public function remove(){
		if(!$this->_beforeDelete()) return false;
		$data=$this->_getData();
		self::RemoveOne(array('_id'=>$this->data['_id']));
		$this->_afterDelete($data);
	}
	
	public function updateField($fieldName,$value){
		static::$__collection->update(array('_id'=>$this->data['_id']),array($fieldName=>$value));
	}
	
	
	public static function InsertOne($data,$options=array()){
		$data['created']=new MongoDate();
		static::$__collection->insert($data,$options=array());
		return $data;
	}
	public static function InsertOneSafe($data,$options=array()){
		$options['safe']=true;
		return static::InsertOne($data,$options);
	}
	public static function InsertOneUnsafe($data,$options=array()){
		$options['safe']=false;
		return static::InsertOne($data,$options);
	}
	
	public static function UpdateOne($criteria,$newObject){
		return static::$__collection->update($criteria,$newObject,array('multiple'=>false));
	}
	public static function UpdateOneById($id,$newObject){
		return static::$__collection->update(array('_id'=>$id),$newObject,array('multiple'=>false));
	}
	public static function UpdateOneByMongoId($id,$newObject){
		return static::UpdateOneById(new MongoId($id),$newObject);
	}
	public static function UpdateAll($criteria,$newObject){
		return static::$__collection->update($criteria,$newObject,array('multiple'=>true));
	}
	
	public static function Group($keys,$initial,$reduce,$options=array()){
		return static::$__collection->group($keys,$initial,$reduce,$options);
	}
	
	public static function FindOne($query=array(),$fields=array()){
		return static::$__collection->findOne($query,$fields);
	}
	public static function FindOneById($id,$fields=array()){
		return static::$__collection->findOne(array('_id'=>$id),$fields);
	}
	public static function FindOneByMongoId($id){
		return static::FindOneById(new MongoId($id));
	}
	
	public static function FindAll($query=array(),$fields=array()){
		return static::$__collection->find($query,$fields);
	}
	
	public static function RemoveOne($criteria){
		return static::$__collection->remove($criteria,array('justOne'=>true));
	}
	public static function RemoveAll($criteria){
		return static::$__collection->remove($criteria,array('justOne'=>false));
	}
	
	
	public static function Paginate($query=array(),$fields=array(),$pageSize=35,$page=null){
		if($page===null) $page=isset($_REQUEST['page']) ? (int)$_REQUEST['page'] : 1;
		$skip = (int)($pageSize * ($page - 1));
		
		$cursor=static::$__collection->find($query,$fields)->limit($pageSize)->skip($skip);
		$count=$cursor->count();
		if($count===0) return false;
		
		return array(
			'total'=>$count,
			'totalPages'=>ceil($count / $pageSize),
			'page'=>$page,
			'cursor'=>$cursor
		);
	}
	
	
	public static function Table(){
		
	}
}
