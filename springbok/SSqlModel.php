<?php
class SSqlModel extends SModel{
	public static function _getPkName(){
		/* DEV */if(empty(static::$__modelInfos['primaryKeys'])) throw new Exception(static::$__className.' does not have any primary keys');/* /DEV */
		return static::$__modelInfos['primaryKeys'][0];
	}
	
	public static function _fullTableName(){
		return static::$__modelDb->_getPrefix().static::$__tableName;
	}
	
	public function _getPkValue(){
		return $this->data[self::_getPkName()];
	}
	
	public function _pkExists(){
		return isset($this->data[self::_getPkName()]);
	}
	
	
	/* Queries */

	public function insert(){
		if(!$this->_beforeInsert()) return false;
		$data=$this->_getSaveData(func_get_args());
		$id=static::QInsert()->data($data)->execute();
		if(static::$__modelInfos['isAI']) $this->data[static::$__modelInfos['primaryKeys'][0]]=$id;
		$this->_afterInsert($data);
		return $id;
	}
	public function insertIgnore(){
		if(!$this->_beforeInsert()) return false;
		$data=$this->_getSaveData(func_get_args());
		$id=static::QInsert()->ignore()->data($data)->execute();
		if(static::$__modelInfos['isAI']) $this->data[static::$__modelInfos['primaryKeys'][0]]=$id;
		$this->_afterInsert($data);
		return $id;
	}
	
	public function replace(){
		if(!$this->beforeSave()) return false;
		$data=$this->_getSaveData(func_get_args());
		$id=static::QReplace()->data($data)->execute();
		if(static::$__modelInfos['isAI']) $this->data[static::$__modelInfos['primaryKeys'][0]]=$id;
		$this->afterSave($data);
		return $id;
	}
	
	public function update(){
		if(!$this->_beforeUpdate()) return false;
		$data=$this->_getSaveData(func_get_args());
		$where=array();
		foreach(static::$__modelInfos['primaryKeys'] as $pkName){
			$where[$pkName]=$this->data[$pkName];
			unset($data[$pkName]);
		}
		if(!static::QUpdateOne()->values($data)->where($where)->execute()) return false;
		$this->_afterUpdate($data);
		return true;
		
	}
	
	public function updateOrInsert(){
		if(!$this->beforeSave()) return false;
		$data=$this->_getSaveData(func_get_args());
		$where=array();
		foreach(static::$__modelInfos['primaryKeys'] as $pkName) $where[$pkName]=$this->data[$pkName];
		if(self::QExist()->where($where)->execute()){
			foreach(array_keys($where) as $pkName) unset($data[$pkName]);
			$res=static::QUpdateOne()->values($data)->where($where)->execute();
			$this->_afterUpdate($data);
		}else{
			foreach($where as $pkName=>&$value) $data[$pkName]=$value;
			$res=static::QInsert()->data($data)->execute();
			$this->_afterInsert($data);
			if(static::$__modelInfos['isAI']) $this->data[static::$__modelInfos['primaryKeys'][0]]=$res;
		}
		return $res;
	}
	
	public function updateField($fieldName,$value){
		$this->$fieldName=$value;
		$where=array();
		foreach(static::$__modelInfos['primaryKeys'] as $pkName){
			$where[$pkName]=$this->data[$pkName];
		}
		if(!static::QUpdateOne()->values(array($fieldName=>$value))->where($where)->execute()) return false;
	}
	
	public function delete(){
		if($this->beforeDelete()){
			$where=array();
			foreach(static::$__modelInfos['primaryKeys'] as $pkName)
				$where[$pkName]=$this->data[$pkName];
			if(!static::QDeleteOne()->where($where)->execute()) return false;
			return true;
		}
	}
	
	public function exists($fields=NULL,$getPk=false){
		$data= $fields===NULL ? $this->_getData() : array_intersect_key($this->_getData(),array_flip(explode(',',$fields)));
		$res=self::QValue()
			->fields(($getPk?($getPk=static::_getPkName()):'1'))
			->where($data)
			->execute();
		if($res && $getPk) $this->__set($getPk,$res);
		return $res;
	}
	
	public function &findWith($key,$options=array()){
		QFind::findWith($this,$key,$options);
		return $this;
	}
	
	public function &findMWith($with){
		QFind::findMWith($this,$with);
		return $this;
	}
	
	public function &findWithPaginate($key,$options=array()){
		return QFind::findWithPaginate('CPagination',$this,$key,$options);
	}
	
	public function &findWithPaginateLetter($key,$options=array()){
		return QFind::findWithPaginate('CPagination_Letters',$this,$key,$options);
	}
	
	public function createQExist(){
		return self::QExist()->where($this->_getData());
	}
	public function createQOne($fields=NULL){
		return self::QOne()->where($fields===NULL?$this->_getData():array_intersect_key($this->_getData(),array_flip(explode(',',$fields))));
	}
	public function createQAll($fields=NULL){
		return self::QAll()->where($fields===NULL?$this->_getData():array_intersect_key($this->_getData(),array_flip(explode(',',$fields))));
	}
	
	public static function dbEscape($string){return static::$__modelDb->escape($string);}
	public static function beginTransaction(){ static::$__modelDb->beginTransaction(); }
	public static function commit(){ static::$__modelDb->commit(); }
	public static function rollBack(){ static::$__modelDb->rollBack(); }
	public static function setAutoIncrement($value){ static::$__modelDb->setAutoIncrement(static::_fullTableName(),$value); }
	
	public static function QInsert(){return new QInsert(static::$__className);}
	public static function QInsertSelect(){return new QInsertSelect(static::$__className);}
	public static function QReplace(){return new QReplace(static::$__className);}
	public static function QUpdate(){return new QUpdate(static::$__className);}
	public static function QUpdateOne(){return new QUpdateOne(static::$__className);}
	public static function QDeleteAll(){return new QDeleteAll(static::$__className);}
	public static function QDeleteOne(){return new QDeleteOne(static::$__className);}
	
	public static function updateOneFieldByPk($pk,$field,$value){
		return static::QUpdateOne()->values(array($field=>&$value))
			->where(array(static::_getPkName()=>$pk))
			->execute();
	}
	public static function QUpdateOneField($field,$value){
		return static::QUpdate()->values(array($field=>&$value));
	}
	public static function updateUpdated($pk){
		return static::QUpdateOne()->where(array(static::_getPkName()=>&$pk))->execute();
	}
	
	public static function QAll(){return new QFindAll(static::$__className);}
	public static function QRows(){return new QFindRows(static::$__className);}
	public static function QListAll(){return new QFindListAll(static::$__className);}
	/** @return QFindOne */
	public static function QOne(){return new QFindOne(static::$__className);}
	public static function QRow(){return new QFindRow(static::$__className);}
	public static function QExist(){return new QExist(static::$__className);}
	/** @return QCount */
	public static function QCount(){return new QCount(static::$__className);}
	public static function QList(){return new QFindList(static::$__className);}
	public static function QListRows(){return new QFindListRows(static::$__className);}
	public static function QValue(){return new QFindValue(static::$__className);}
	public static function QValues(){return new QFindValues(static::$__className);}
	public static function QLoadData(){return new QLoadData(static::$__className);}
	public static function QUnionAll(){return new QUnionAll(static::$__className);}
	public static function QUnionOne(){return new QUnionOne(static::$__className);}

	public static function ById(&$id){return self::QOne()->where(array('id'=>&$id));}
	public static function ByIdAndStatus(&$id,$status){return self::QOne()->where(array('id'=>&$id,'status'=>&$status));}
	public static function ByIdAndType(&$id,$type){return self::QOne()->where(array('id'=>&$id,'type'=>&$type));}
	
	public static function findAll(){return self::QAll()->execute();}
	public static function findOne(){return self::QOne()->execute();}
	
	public static function Table(){return new QTable(static::$__className);}
	public static function TableOne(){return new QTableOne(static::$__className);}
	
	public static function QListName(){
		$orderByField=&static::$__orderByField;
		return self::QList()->setFields(array(self::_getPkName(),static::$__displayField))->orderBy($orderByField===null?static::$__displayField:$orderByField);
	}
	public static function findListName(){/* DEV */if(func_num_args()!==0) throw new Exception('Use displayField now'); /* /DEV */return static::QListName()->execute();}
	public static function findCachedListName(){
		$className=&static::$__className;
		return CCache::get('models')->readOrWrite($className,function() use(&$className){return $className::findListName();});
	}
	public static function findCachedListValues($fields){
		$className=&static::$__className;
		return CCache::get('models')->readOrWrite($className,function() use(&$className,&$fields){return $className::QListRows()->fields($fields)->execute();});
	}
	
	public static function findFirstLetters($fieldName='name'){
		$className=&static::$__className;
		return CCache::get('models_firstLetters')->readOrWrite($className.'_firstLetters',function() use(&$className,&$fieldName){return $className::QValues()->field('DISTINCT SUBSTRING('.$fieldName.',1,1)');});
	}
	
	public static function findValues($field){return self::QValues()->fields($field)->execute();}
	
	public static function insertAll($data,$cols=null){ return self::saveAll($data,$cols,'insert'); }
	public static function insertIgnoreAll($data,$cols=null){ return self::saveAll($data,$cols,'insertIgnore'); }
	public static function replaceAll($data,$cols=null){ return self::saveAll($data,$cols,'replace'); }
	
	
	public static function insertAllFor($field,$value,$data,$cols=null){
		if($cols!==null) $cols[]=$field;
		foreach($data as $m) $m->$field=$value;
		return self::saveAll($data,$cols,'insert');
	}
	
	
	public static function saveAll($data,$cols=null,$method='insert'){
		if(empty($data)) return false;
		$db=static::$__modelDb; $method.='Multiple';
		
		reset($data);
		$db->$method(static::_fullTableName(),count(current($data)->_getData()),$data,$cols);
	}
	
	public static function truncate(){
		return static::$__modelDb->truncate(self::_fullTableName());
	}
	
	public static function __callStatic($method, $params){
        if (!preg_match('/^(findOne|findAll|findValues|findValue|findListAll|findListName|deleteOne|deleteAll|exist)(\w+)?By(\w+)$/',$method,$matches))
            throw new \Exception("Call to undefined method {$method}");
 
 		$className='Q'.ucfirst($matches[1]);
        $query = new $className(static::$__className);
		if(!empty($matches[2])){
			$fields=explode('And',$matches[2]);
			$fields=array_map('lcfirst',$fields);
			$query->setFields($fields);
		}
		$query->by($matches[3],$params);
        return $query->execute();
    }
	
	
	/* */
	
	public function name(){
		return $this->name;
	}
	
}
/*
class EmptyModel extends SSqlModel{
	public static $__className='EmptyModel',$__modelDb,$__modelInfos,$__PROP_DEF,$_relations,$__tableName,$__alias='aa',$__dbName;
	
	public static function init($db,$tableName='',$alias='em'){
		self::$__tableName=$tableName;
		if(is_string($db)) self::$__modelDb=DB::init(self::$__dbName=$db);
		else{
			self::$__modelDb=&$db;
			self::$__dbName=$db->_getName();
		}
		self::$__loadedModels[]='EmptyModel';
		self::$__modelInfos=DBSchema::createModelInfos(self::$__modelDb,'EmptyModel');
		self::$__PROP_DEF=DBSchema::createModelPropDef(self::$__modelDb,'EmptyModel');
	}
}
*/

