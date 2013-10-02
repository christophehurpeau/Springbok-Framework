<?php
/**
 * Mongo Specific model
 */
class SMongoModel extends SModel{
	/**
	 * @internal
	 * @param string
	 * @return void
	 */
	public static function init($modelName){
		parent::init($modelName);
		$modelName::$__collection=$modelName::$__modelDb->collection($modelName);
	}
	
	/**
	 * @internal
	 * @return string
	 */
	public static function _fullTableName(){
		return static::$__className;
	}
	
	/**
	 * @return string
	 */
	public function _getPkName(){
		return '_id';
	}
	
	/**
	 * @return mixed
	 */
	public function _getPkValue(){
		return $this->data['_id'];
	}
	
	/**
	 * @return bool
	 */
	public function _pkExists(){
		return isset($this->data['_id']);
	}
	
	
	
	/**
	 * Insert the model in the mongo db
	 * 
	 * @see http://us2.php.net/manual/en/mongocollection.insert.php
	 */
	public function insert(){
		if(!$this->_beforeInsert()) return false;
		$data=$this->data=static::InsertOne($data=$this->_getData());
		$this->_afterInsert($data);
		return $data['_id'];
	}
	
	/** @deprecated */
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
	
	/**
	 * Update the model in the mongo db
	 * 
	 * @return void
	 */
	public function update(){
		if(!$this->_beforeUpdate()) return false;
		$data=$this->_getData();
		$data['updated']=new MongoDate();
		static::$__collection->save($data);
		$this->_afterUpdate($data);
	}
	
	/**
	 * Remove the model in the mongo db
	 * 
	 * @return void
	 */
	public function remove(){
		if(!$this->_beforeDelete()) return false;
		$data=$this->_getData();
		self::RemoveOne(array('_id'=>$this->data['_id']));
		$this->_afterDelete($data);
	}
	
	/**
	 * @param string
	 * @param mixed
	 * 
	 * Updpate just a field in the mongo db
	 */
	public function updateField($fieldName,$value){
		$this->data[$fieldName] = $value;
		static::$__collection->update(array('_id'=>$this->data['_id']),array($fieldName=>$value));
	}
	
	/**
	 * Insert one row in the mongo db
	 * 
	 * @param array
	 * @param array
	 * @return array
	 */
	public static function InsertOne($data,$options=array()){
		$data['created']=new MongoDate();
		static::$__collection->insert($data,$options=array());
		return $data;
	}
	
	/**
	 * Insert one row in the mongo db, safely
	 * 
	 * @param array
	 * @param array
	 * @return array
	 */
	public static function InsertOneSafe($data,$options=array()){
		$options['safe']=true;
		return static::InsertOne($data,$options);
	}
	
	/**
	 * Insert one row in the mongo db, but don't wait until it did
	 * 
	 * @param array
	 * @param array
	 * @return array
	 */
	public static function InsertOneUnsafe($data,$options=array()){
		$options['safe']=false;
		return static::InsertOne($data,$options);
	}
	
	/**
	 * Update one row in the mongo db, but don't wait until it did
	 * 
	 * @param array
	 * @param array
	 * @return bool|array
	 */
	public static function UpdateOneUnsafe($criteria,$newObject){
		return static::$__collection->update($criteria,$newObject,array('multiple'=>false,'safe'=>false));
	}
	
	/**
	 * Update one row by id in the mongo db, but don't wait until it did
	 * 
	 * @param mixed
	 * @param array
	 * @return bool|array
	 */
	public static function UpdateByIdUnsafe($id,$newObject){
		return static::UpdateOneUnsafe(array('_id'=>$id),$newObject);
	}
	
	/**
	 * Update one row by mongo id (UUID) in the mongo db, but don't wait until it did
	 * 
	 * @param string
	 * @param array
	 * @return bool|array
	 */
	public static function UpdateByMongoIdUnsafe($id,$newObject){
		return static::UpdateByIdUnsafe(new MongoId($id),$newObject);
	}
	
	/**
	 * Update one row in the mongo db, safely
	 * 
	 * @param array
	 * @param array
	 * @return bool|array
	 */
	public static function UpdateOneSafe($criteria,$newObject){
		return static::$__collection->update($criteria,$newObject,array('multiple'=>false,'safe'=>true));
	}
	
	/**
	 * Update one row by id in the mongo db, safely
	 * 
	 * @param mixed
	 * @param array
	 * @return bool|array
	 */
	public static function UpdateByIdSafe($id,$newObject){
		return static::UpdateOneSafe(array('_id'=>$id),$newObject);
	}
	
	/**
	 * Update one row by id in the mongo db, safely
	 * 
	 * @param mixed
	 * @param array
	 * @return bool|array
	 */
	public static function UpdateByMongoIdSafe($id,$newObject){
		return static::UpdateByIdSafe(new MongoId($id),$newObject);
	}
	
	
	/**
	 * Update multipe rows in the mongo db, but don't wait until it did
	 * 
	 * @param array
	 * @param array
	 * @return bool|array
	 */
	public static function UpdateAllUnsafe($criteria,$newObject){
		return static::$__collection->update($criteria,$newObject,array('multiple'=>true,'safe'=>false));
	}
	
	/**
	 * Update multipe rows in the mongo db, safely
	 * 
	 * @param array
	 * @param array
	 * @return bool|array
	 */
	public static function UpdateAllSafe($criteria,$newObject){
		return static::$__collection->update($criteria,$newObject,array('multiple'=>true,'safe'=>true));
	}
	
	/**
	 * Update or Insert one row in the mongo db, but don't wait until it did
	 * 
	 * @param array
	 * @param array
	 * @return bool|array
	 */
	public static function UpsertOneUnsafe($criteria,$newObject){
		return static::$__collection->update($criteria,$newObject,array('multiple'=>false,'safe'=>false,'upsert'=>true));
	}
	
	/**
	 * Update or Insert one row by id in the mongo db, but don't wait until it did
	 * 
	 * @param mixed
	 * @param array
	 * @return bool|array
	 */
	public static function UpsertByIdUnsafe($id,$newObject){
		return static::UpsertOneUnsafe(array('_id'=>$id),$newObject);
	}
	
	/**
	 * Update or Insert one row by mongo id in the mongo db, but don't wait until it did
	 * 
	 * @param string
	 * @param array
	 * @return bool|array
	 */
	public static function UpsertByMongoIdUnsafe($id,$newObject){
		return static::UpsertByIdUnsafe(new Mongo($id),$newObject);
	}
	
	/**
	 * Update or Insert one row in the mongo db, safely
	 * 
	 * @param array
	 * @param array
	 * @return bool|array
	 */
	public static function UpsertOneSafe($criteria,$newObject){
		return static::$__collection->update($criteria,$newObject,array('multiple'=>false,'safe'=>true,'upsert'=>true));
	}
	
	/**
	 * Update or Insert one row by id in the mongo db, safely
	 * 
	 * @param mixed
	 * @param array
	 * @return bool|array
	 */
	public static function UpsertByIdSafe($id,$newObject){
		return static::UpsertOneSafe(array('_id'=>$id),$newObject);
	}
	
	/**
	 * Update or Insert one row by id in the mongo db, safely
	 * 
	 * @param string
	 * @param array
	 * @return bool|array
	 */
	public static function UpsertByMongoIdSafe($id,$newObject){
		return static::UpsertByIdSafe(new Mongo($id),$newObject);
	}
	
	/**
	 * Group
	 * 
	 * @param mixed
	 * @param array
	 * @param MongoCode
	 * @param array
	 * @return array
	 * @see http://www.php.net/manual/en/mongocollection.group.php
	 */
	public static function Group($keys,$initial,$reduce,$options=null){
		return static::$__collection->group($keys,$initial,$reduce,$options);
	}
	
	/**
	 * @param array
	 * @param array
	 * @return array
	 * @see http://www.php.net/manual/en/mongocollection.findone.php
	 */
	public static function FindOne($query=array(),$fields=array()){
		return static::$__collection->findOne($query,$fields);
	}
	
	/**
	 * @param mixed
	 * @param array
	 * @return array
	 * @see http://www.php.net/manual/en/mongocollection.findone.php
	 */
	public static function FindOneById($id,$fields=array()){
		return static::$__collection->findOne(array('_id'=>$id),$fields);
	}
	
	/**
	 * @param string
	 * @param array
	 * @return array
	 * @see http://www.php.net/manual/en/mongocollection.findone.php
	 */
	public static function FindOneByMongoId($id){
		return static::FindOneById(new MongoId($id));
	}
	
	/**
	 * @param array
	 * @param array
	 * @return array
	 * @see http://www.php.net/manual/en/mongocollection.find.php
	 */
	public static function FindAll($query=array(),$fields=array()){
		return static::$__collection->find($query,$fields);
	}
	
	/**
	 * @param array
	 * @return bool|array
	 * @see http://www.php.net/manual/en/mongocollection.remove.php
	 */
	public static function RemoveOne($criteria){
		return static::$__collection->remove($criteria,array('justOne'=>true));
	}
	
	/**
	 * @param array
	 * @return bool|array
	 * @see http://www.php.net/manual/en/mongocollection.remove.php
	 */
	public static function RemoveAll($criteria){
		return static::$__collection->remove($criteria,array('justOne'=>false));
	}
	
	/**
	 * @return bool|array
	 * @see http://www.php.net/manual/en/mongocollection.remove.php
	 */
	public static function truncate(){
		return self::RemoveAll(array());
	}
	
	/**
	 * @param mixed
	 * @return bool
	 */
	public static function ExistsById($id){
		$res=static::$__collection->findOne(array('_id'=>$id),array('_id'));
		return $res!==null;
	}
	
	/**
	 * @param string
	 * @return bool
	 */
	public static function ExistsByMongoId($id){
		return static::ExistsById(new MongoId($id));
	}
	
	/**
	 * @see http://www.php.net/manual/en/mongocollection.aggregate.php
	 */
	public static function Aggregate(){
		return static::$__collection->aggregate(func_get_args());
	}
	
	/**
	 * @param array
	 * @param array
	 * @param int
	 * @param int|null
	 * @return array [total=>, totalPages=>, page=> cursor=>]
	 */
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
	
	/**
	 * @return mixed
	 */
	public function id(){
		return $this->_get('_id');
	}
}
