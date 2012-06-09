<?php
class SMongoModel extends SModel{
	public static function init($modelName){
		parent::init($modelName);
		$modelName::$__collection=$modelName::$__modelDb->collection(static::$__tableName);
	}
	
	/* http://us2.php.net/manual/en/mongocollection.insert.php */
	public function insert(){
		if(!$this->_beforeInsert()) return false;
		$data=$this->_getData();
		$data['created']=new MongoDate();
		static::$__collection->insert($data);
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
			$data['created']=new MongoDate();
			static::$__collection->insert($data);
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
	
	public static function UpdateOne($criteria,$newObject){
		return static::$__collection->update($criteria,$newObject,array('multiple'=>false));
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
	
	public static function FindAll($query=array(),$fields=array()){
		return static::$__collection->find($query,$fields);
	}
	
	public static function RemoveOne($criteria){
		return static::$__collection->remove($criteria,array('justOne'=>true));
	}
	public static function RemoveAll($criteria){
		return static::$__collection->remove($criteria,array('justOne'=>false));
	}
}
