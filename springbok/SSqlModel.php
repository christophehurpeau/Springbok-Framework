<?php
/**
 * @see http://springbok-framework.com/Models-Creation.html
 */
class SSqlModel extends SModel{
	public static function init($modelName){
		parent::init($modelName);
		$modelName::$__modelInfos=include Config::$models_infos.$modelName;
		$modelName::$_relations=&$modelName::$__modelInfos['relations'];
		$modelName::$__PROP_DEF=&$modelName::$__modelInfos['props'];
	}
	
	/**
	 * @internal
	 * @return string
	 */
	public static function _getPkName(){
		/*#if DEV */if(empty(static::$__modelInfos['primaryKeys'])) throw new Exception(static::$__className.' does not have any primary keys');/*#/if*/
		return static::$__modelInfos['primaryKeys'][0];
	}
	
	/**
	 * @internal
	 * @return string
	 */
	public static function _fullTableName(){
		return static::$__modelDb->_getPrefix().static::$__tableName;
	}
	
	/**
	 * Return the primary key value.
	 * 
	 * @return string
	 */
	public function _getPkValue(){
		return $this->data[self::_getPkName()];
	}
	
	/**
	 * Set the value of the primary key.
	 * 
	 * @param mixed
	 * @return mixed
	 */
	public function _setPkValue($value){
		return $this->data[self::_getPkName()] = $value;
	}
	
	/**
	 * Return if the primary key exists in this model
	 * 
	 * @return bool
	 */
	public function _pkExists(){
		return isset($this->data[self::_getPkName()]);
	}
	
	
	/* Queries */
	
	/**
	 * @param QInsert
	 * @param array
	 * @return int|bool
	 */
	private function _insert($query,$data){
		$id=$query->data($data)->execute();
		if($id){
			if(static::$__modelInfos['isAI']) $this->data[static::$__modelInfos['primaryKeys'][0]]=$id;
			$this->_afterInsert($data);
		}
		return $id;
	}
	
	/**
	 * @return int|bool
	 */
	public function insert(){
		if(!$this->_beforeInsert()) return false;
		$data=$this->_getSaveData(func_get_args());
		return $this->_insert(static::QInsert(),$data);
	}
	
	/**
	 * @return int|bool
	 */
	public function insertIgnore(){
		if(!$this->_beforeInsert()) return false;
		$data=$this->_getSaveData(func_get_args());
		return $this->_insert(static::QInsert()->ignore(),$data);
	}
	
	/**
	 * @return int|bool
	 */
	public function findIdOrInsert(/*#if DEV */$UniqueField/*#/if*/){
		$args=func_get_args();
		$UniqueField=array_shift($args);
		if(!$this->_beforeInsert()) return false;
		$data=$this->_getSaveData($args);
		$id=static::QValue()->field('id')->where(array($UniqueField=>$data[$UniqueField]))->fetch();
		if($id){
			$this->data[static::$__modelInfos['primaryKeys'][0]]=$id;
		}else{
			if($id=$this->_insert(static::QInsert(),$data)){
			//if(!empty($data[$pkName=self::_getPkName()]))
				$id=/*$this->data[$pkName]=*/$data[/* $pkName */self::_getPkName()]; /* override id, if for example pk is in */
			}
		}
		return $id;
	}
	
	/**
	 * @return int|bool
	 */
	public function replace(){
		if(!$this->beforeSave()) return false;
		$data=$this->_getSaveData(func_get_args());
		$id=static::QReplace()->data($data)->execute();
		if(static::$__modelInfos['isAI']) $this->data[static::$__modelInfos['primaryKeys'][0]]=$id;
		$this->afterSave($data);
		return $id;
	}
	
	/**
	 * @return bool
	 */
	public function update(){
		if(!$this->_beforeUpdate()) return false;
		$data=$this->_getSaveData(func_get_args());
		$where=array();
		foreach(static::$__modelInfos['primaryKeys'] as $pkName){
			$where[$pkName]=$this->data[$pkName];
			unset($data[$pkName]);
		}
		if(!static::QUpdateOne()->values($data)->where($where)->execute()) return false;
		/*#if DEV */$resAfterUpdate=/*#/if*/$this->_afterUpdate($data);
		/*#if DEV *//*if($resAfterUpdate!==true) throw new Exception('After Updated Failed ('.$resAfterUpdate.')');*//*#/if*/
		return true;
	}
	
	/**
	 * @param string
	 * @param mixed
	 * @return bool
	 */
	public function updateField($fieldName,$value){
		$this->$fieldName=$value;
		$where=array();
		foreach(static::$__modelInfos['primaryKeys'] as $pkName){
			$where[$pkName]=$this->data[$pkName];
		}
		if(!static::QUpdateOne()->values(array($fieldName=>$value))->where($where)->execute()) return false;
		return true;
	}
	
	/**
	 * @return bool|int
	 */
	public function updateOrInsert(){
		if(!$this->beforeSave()) return false;
		$data=$this->_getSaveData(func_get_args());
		$where=array();
		foreach(static::$__modelInfos['primaryKeys'] as $pkName) $where[$pkName]=$this->data[$pkName];
		if(self::QExist()->where($where)->fetch()){
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
	
	/**
	 * @param array|null
	 * @return bool
	 */
	public function updateCompare($originalData=null){
		if(!$this->_beforeUpdate()) return false;
		/*#if DEV */if($originalData===null && $this->originalData===null) throw new Exception('Model needed to be prepared'); /*#/if*/
		$data=$this->compareData($originalData);
		if($data===false) return null;
		$where=array();
		foreach(static::$__modelInfos['primaryKeys'] as $pkName){
			$where[$pkName]=$this->data[$pkName];
			unset($data[$pkName]);
		}
		if(empty($data)) return null;
		if(!static::QUpdateOne()->values($data)->where($where)->execute()) return false;
		$this->_afterUpdateCompare($data,$where);
		return true;
		
	}
	
	/**
	 * @return bool
	 */
	public function delete(){
		if($this->beforeDelete()){
			$where=array();
			foreach(static::$__modelInfos['primaryKeys'] as $pkName)
				$where[$pkName]=$this->data[$pkName];
			if(!static::QDeleteOne()->where($where)->execute()) return false;
			return true;
		}
	}
	
	/**
	 * @param array|null
	 * @param bool
	 * @return bool
	 */
	public function exists($fields=NULL,$getPk=false){
		$data= $fields===NULL ? $this->_getData() : array_intersect_key($this->_getData(),array_flip(explode(',',$fields)));
		$res=self::QValue()
			->fields(($getPk?($getPk=static::_getPkName()):'1'))
			->where($data)
			->fetch();
		if($res && $getPk) $this->__set($getPk,$res);
		return $res;
	}
	
	/**
	 * Find fields in db to prepare the later comparison
	 * 
	 * @return void
	 * @see updateCompare
	 */
	public function findFieldsForCompare(){
		$where=array();
		foreach(static::$__modelInfos['primaryKeys'] as $pkName)
			$where[$pkName]=$this->data[$pkName];
		/*#if DEV */if(empty($where)) throw new Exception('WHERE should not be empty !'); /*#/if*/
		$this->originalData=self::QRow()->setFields(array_keys(array_diff_key($this->data,$where)))->where($where)->fetch();
	}
	
	/**
	 * Find using relations and put it in this model
	 * 
	 * @param string
	 * @param array
	 * @return SSqlModel|self
	 */
	public function findWith($key,$options=array()){
		QFind::findWith($this,$key,$options);
		return $this;
	}
	
	/**
	 * Find using relations and put it in this model
	 * 
	 * @param array
	 * @return SSqlModel|self
	 */
	public function findMWith($with){
		QFind::findMWith($this,$with);
		return $this;
	}
	
	/**
	 * Find using relations and put it in this model
	 * 
	 * @param string
	 * @param array
	 * @return SSqlModel|self
	 */
	public function findWithPaginate($key,$options=array()){
		return QFind::findWithPaginate('CPagination',$this,$key,$options);
	}
	
	/**
	 * Find using relations and put it in this model
	 * 
	 * @param string
	 * @param array
	 * @return SSqlModel|self
	 */
	public function findWithPaginateLetter($key,$options=array()){
		return QFind::findWithPaginate('CPagination_Letters',$this,$key,$options);
	}
	
	/**
	 * Create a QExist query from this model data
	 * 
	 * @return QExist
	 */
	public function createQExist(){
		return self::QExist()->where($this->_getData());
	}
	
	/**
	 * Create a QOne query from this model data
	 * 
	 * @param string|null fields separated by ','
	 * @return QOne
	 */
	public function createQOne($fields=NULL){
		return self::QOne()->where($fields===NULL?$this->_getData():array_intersect_key($this->_getData(),array_flip(explode(',',$fields))));
	}
	
	/**
	 * Create a QAll query from this model data
	 * 
	 * @param string|null fields separated by ','
	 * @return QAll
	 */
	public function createQAll($fields=NULL){
		return self::QAll()->where($fields===NULL?$this->_getData():array_intersect_key($this->_getData(),array_flip(explode(',',$fields))));
	}
	
	/**
	 * Escape a value to be inserted in a query
	 * @return string
	 */
	public static function dbEscape($string){return static::$__modelDb->escape($string);}
	
	/**
	 * Format a field
	 * @return string
	 */
	public static function dbFormatField($string){return static::$__modelDb->formatField($string);}
	
	/**
	 * Begin a new transaction
	 * 
	 * @return void
	 * @see commit
	 * @see rollback
	 */
	public static function beginTransaction(){ static::$__modelDb->beginTransaction(); }
	
	/**
	 * Commit this transaction
	 * 
	 * @return void
	 * @see beginTransaction
	 * @see rollback
	 */
	public static function commit(){ static::$__modelDb->commit(); }
	
	/**
	 * Rollback this transaction
	 * 
	 * @return void
	 * @see commit
	 * @see beginTransaction
	 */
	public static function rollBack(){ static::$__modelDb->rollBack(); }
	
	/**
	 * Set the current model autoincrement value
	 * 
	 * @param int
	 * @return void
	 */
	public static function setAutoIncrement($value){ static::$__modelDb->setAutoIncrement(static::_fullTableName(),$value); }
	
	/**
	 * Create a QInsert query
	 * 
	 * @return QInsert
	 */
	public static function QInsert(){return new QInsert(static::$__className);}
	
	/**
	 * Create a QInsertSelect query
	 * 
	 * @return QInsertSelect
	 */
	public static function QInsertSelect(){return new QInsertSelect(static::$__className);}
	
	/**
	 * Create a QReplace query
	 * 
	 * @return QReplace
	 */
	public static function QReplace(){return new QReplace(static::$__className);}
	
	/**
	 * Create a QUpdate query
	 * 
	 * @return QUpdate
	 */
	public static function QUpdate(){return new QUpdate(static::$__className);}
	
	/**
	 * Create a QUpdateOne query
	 * 
	 * @return QUpdateOne
	 */
	public static function QUpdateOne(){return new QUpdateOne(static::$__className);}
	
	/**
	 * Create a QDeleteAll query
	 * 
	 * @return QDeleteAll
	 */
	public static function QDeleteAll(){return new QDeleteAll(static::$__className);}
	
	/**
	 * Create a QDeleteOne query
	 * 
	 * @return QDeleteOne
	 */
	public static function QDeleteOne(){return new QDeleteOne(static::$__className);}
	
	/**
	 * Update one field in the db by the model's primary key
	 * UPDATE ... SET field = value, updated = NOW() WHERE pk = pkValue 
	 * 
	 * @param mixed
	 * @param string
	 * @param mixed
	 * @return bool|int
	 */
	public static function updateOneFieldByPk($pk,$field,$value/*#if DEV*/,$updateUpdated=null/*#/if*/){
		/*#if DEV*/ if($updateUpdated !== null) throw new Exception('Use updateOneFieldByPkWithoutUpdatingUpdated()'); /*#/if*/
		return static::QUpdateOne()->values(array($field=>$value))
			->where(array(static::_getPkName()=>$pk))->execute();
	}
	
	/**
	 * Update one field in the db by the model's primary key
	 * UPDATE ... SET field = value WHERE pk = pkValue 
	 * 
	 * @param mixed
	 * @param string
	 * @param mixed
	 * @return bool|int
	 */
	public static function updateOneFieldByPkWithoutUpdatingUpdated($pk,$field,$value){
		return static::QUpdateOne()->doNotUpdateUpdatedField()->values(array($field=>$value))
			->where(array(static::_getPkName()=>$pk))->execute();
	}
	
	/**
	 * Create a QUpdate query to update one field
	 * UPDATE ... SET field = value
	 * 
	 * Be careful, if you want update only one row, use ->limit1() then. The "One" stands for the field, not the number of rows updatable.
	 * 
	 * @param string
	 * @param mixed
	 * @return QUpdate
	 */
	public static function QUpdateOneField($field,$value){
		return static::QUpdate()->values(array($field=>$value));
	}
	
	/**
	 * Update the updated field
	 * 
	 * @param mixed
	 * @return bool|int
	 */
	public static function updateUpdated($pk){
		return static::QUpdateOne()->where(array(static::_getPkName()=>$pk))->execute();
	}
	
	/**
	 * Create a QFindAll query
	 * 
	 * @return QFindAll
	 */
	public static function QAll(){return new QFindAll(static::$__className);}
	
	/**
	 * Create a QFindRows query
	 * 
	 * @return QFindRows
	 */
	public static function QRows(){return new QFindRows(static::$__className);}
	
	/**
	 * Create a QFindListAll query
	 * 
	 * @return QFindListAll
	 */
	public static function QListAll(){return new QFindListAll(static::$__className);}
	
	/**
	 * Create a QFindOne query
	 * 
	 * @return QFindOne
	 */
	public static function QOne(){return new QFindOne(static::$__className);}
	
	/**
	 * Create a QFindRow query
	 * 
	 * @return QFindRow
	 */
	public static function QRow(){return new QFindRow(static::$__className);}
	
	/**
	 * Create a QExist query
	 * 
	 * @return QExist
	 */
	public static function QExist(){return new QExist(static::$__className);}
	
	/**
	 * Create a QCount query
	 * 
	 * @return QCount
	 */
	public static function QCount(){return new QCount(static::$__className);}
	
	/**
	 * Create a QFindList query
	 * 
	 * @return QFindList
	 */
	public static function QList(){return new QFindList(static::$__className);}
	
	/**
	 * Create a QFindListRows query
	 * 
	 * @return QFindListRows
	 */
	public static function QListRows(){return new QFindListRows(static::$__className);}
	
	/**
	 * Create a QFindValue query
	 * 
	 * @return QFindValue
	 */
	public static function QValue(){return new QFindValue(static::$__className);}
	
	/**
	 * Create a QFindValues query
	 * 
	 * @return QFindValues
	 */
	public static function QValues(){return new QFindValues(static::$__className);}
	
	/**
	 * Create a QLoadData query
	 * 
	 * @return QLoadData
	 */
	public static function QLoadData(){return new QLoadData(static::$__className);}
	
	/**
	 * Create a QUnionAll query
	 * 
	 * @return QUnionAll
	 */
	public static function QUnionAll(){return new QUnionAll(static::$__className);}
	
	/**
	 * Create a QUnionOne query
	 * 
	 * @return QUnionOne
	 */
	public static function QUnionOne(){return new QUnionOne(static::$__className);}
	
	/**
	 * Create a QOne query with the condition on 'id'
	 * 
	 * @param int
	 * @return QOne
	 */
	public static function ById($id){return self::QOne()->where(array('id'=>$id));}
	
	/**
	 * Create a QOne query with the condition on 'id' and 'status'
	 * 
	 * @param int
	 * @param int
	 * @return QOne
	 */
	public static function ByIdAndStatus($id,$status){return self::QOne()->where(array('id'=>$id,'status'=>$status));}
	
	/**
	 * Create a QOne query with the condition on 'id' and 'type'
	 * 
	 * @param int
	 * @param int
	 * @return QOne
	 */
	public static function ByIdAndType($id,$type){return self::QOne()->where(array('id'=>$id,'type'=>$type));}
	
	/**
	 * Execute the default QAll query
	 * 
	 * @return array
	 */
	public static function findAll(){return self::QAll()->fetch();}
	
	/**
	 * Execute the default QOne query
	 * 
	 * @return SModel|self|false
	 */
	public static function findOne(){return self::QOne()->fetch();}
	
	/**
	 * Return a model by its primary key
	 * 
	 * @return SModel|self|false
	 */
	public static function findOneByPk($pk){
		return self::QOne()->where(array( static::_getPkName() => $pk ))->fetch();
	}
	
	/**
	 * Create a QTable query
	 * 
	 * @return QTable
	 */
	public static function Table(){return new QTable(static::$__className);}
	
	/**
	 * Create a QTableOne query
	 * 
	 * @return QTableOne
	 */
	public static function TableOne(){return new QTableOne(static::$__className);}
	
	/**
	 * Create a QList query with the name field and the default order
	 * 
	 * @return QList
	 */
	public static function QListName(){
		$orderByField=static::$__orderByField;
		return self::QList()->setFields(array(self::_getPkName(),static::$__displayField))->orderBy($orderByField===null?static::$__displayField:$orderByField);
	}
	
	/**
	 * Execute the method QListName()
	 * 
	 * @return array
	 * @see QListName()
	 */
	public static function findListName(){/*#if DEV */if(func_num_args()!==0) throw new Exception('Use displayField now'); /*#/if*/return static::QListName()->fetch();}
	
	/**
	 * Store or Get the result of findListName from the cache
	 * 
	 * @return array
	 */
	public static function findCachedListName(){
		$className=static::$__className;
		return CCache::get('models')->readOrWrite($className,function() use($className){return $className::findListName();});
	}
	
	/**
	 * Store or Get the result of QListRows from the cache
	 * 
	 * @return array
	 */
	public static function findCachedListValues($fields){
		$className=static::$__className;
		return CCache::get('models')->readOrWrite($className,function() use($className,$fields){return $className::QListRows()->fields($fields);});
	}
	
	/**
	 * find the all the first letters from the name field
	 * 
	 * @return array
	 */
	public static function findFirstLetters($fieldName='name'){
		$className=static::$__className;
		return CCache::get('models_firstLetters')->readOrWrite($className.'_firstLetters',function() use($className,$fieldName){return $className::QValues()->field('DISTINCT SUBSTRING('.$fieldName.',1,1)');});
	}
	
	/**
	 * Execute the default QValues query
	 * 
	 * @return array
	 */
	public static function findValues($field){return self::QValues()->field($field)->fetch();}
	
	/**
	 * Insert several rows in the same time
	 * 
	 * @param array
	 * @param string|null
	 * @return mixed
	 */
	public static function insertAll($data,$cols=null){ return self::saveAll($data,$cols,'insert'); }
	
	/**
	 * Insert several rows in the same time
	 * 
	 * @param array
	 * @param string|null
	 * @return mixed
	 */
	public static function insertIgnoreAll($data,$cols=null){ return self::saveAll($data,$cols,'insertIgnore'); }
	
	/**
	 * Replace several rows in the same time
	 * 
	 * @param array
	 * @param string|null
	 * @return mixed
	 */
	public static function replaceAll($data,$cols=null){ return self::saveAll($data,$cols,'replace'); }
	
	/**
	 * Insert several rows with a common field/value in the data
	 * 
	 * @param string
	 * @param mixed
	 * @param array
	 * @param string|null
	 */
	public static function insertAllFor($field,$value,$data,$cols=null){
		if($cols!==null) $cols[]=$field;
		foreach($data as $m) $m->$field=$value;
		return self::saveAll($data,$cols,'insert');
	}
	
	/**
	 * @internal
	 * @param array
	 * @param string|null
	 * @param string
	 */
	public static function saveAll($data,$cols=null,$method='insert'){
		if(empty($data)) return false;
		$db=static::$__modelDb; $method.='Multiple';
		
		reset($data);
		$db->$method(static::_fullTableName(),count(current($data)->_getData()),$data,$cols);
	}
	
	/**
	 * Truncate the table
	 * 
	 * @return mixed
	 */
	public static function truncate(){
		return static::$__modelDb->truncate(self::_fullTableName());
	}
	
	public static function __callStatic($method, $params){
		if (!preg_match('/^(findOne|findAll|findValues|findValue|findListAll|findListName|deleteOne|deleteAll|exists?)(\w+)?By(\w+)$/',$method,$matches))
			throw new \Exception("Call to undefined method {$method} in class ".get_called_class()
					/*#if DEV */."\nKnown methods :".implode(', ',get_class_methods(static::$__className))/*#/if*/);
 
 		$className='Q'.ucfirst($matches[1]);
		$query = new $className(static::$__className);
		if(!empty($matches[2])){
			$fields=explode('And',$matches[2]);
			$fields=array_map('lcfirst',$fields);
			$query->setFields($fields);
		}
		$query->by($matches[3],$params);
		return $query->_execute_();
	}
	
	
	/* */
	
	/**
	 * You should override it when the model have several primary keys
	 * 
	 * @return mixed
	 */
	public function id(){
		return $this->_getPkValue();
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

