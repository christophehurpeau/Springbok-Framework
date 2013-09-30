<?php
/**
 * Model abstract class
 */
abstract class SModel implements IteratorAggregate,ArrayAccess,Serializable,JsonSerializable{
	/**
	 * The default dbName, set in config
	 */
	public static $__dbName='default';
	public static $__modelDb,$__displayField='name',$__orderByField=null;
	public static $__loadedModels=array();
	
	/**
	 * @ignore
	 */
	public static function init($modelName){
		$modelName::$__modelDb=DB::init(static::$__dbName);
		self::$__loadedModels[]=$modelName;
	}
	/**
	 * Update DB of all models
	 * 
	 * @return void
	 */
	public static function updateAllDB(){
		foreach(self::$__loadedModels as $model) $model::updateDB();
	}
	
	/**
	 * Update DB
	 * 
	 * @return void
	 */
	public static function updateDB(){
		static::$__modelDb = DB::get(static::$__dbName);
	}
	/**
	 * Return the model's DB
	 * 
	 * @return DB
	 */
	public static function getDB(){
		return static::$__modelDb;
	}
	
	
	/* Properties */
	
	/**
	 * The data
	 * 
	 * @var array
	 */
	protected $data=array();
	
	/**
	 * The data, before modifications
	 * 
	 * @var array
	 * @see prepareUpdate
	 */
	protected $originalData;
	
	/**
	 * @param string
	 * @return bool
	 */
	public function __isset($name){
		return isset($this->data[$name]);
	}
	/**
	 * @param string
	 * @return bool
	 */
	public function isKeyExists($key){
		return array_key_exists($key,$this->data);
	}
	
	/**
	 * @param string
	 * @return mixed
	 */
	public function __get($name){
		/*#if DEV */
		if(!/*isset($this->data[$name])*/array_key_exists($name,$this->data)){//isset does'nt work if the value is null
			throw new Exception($name.' IS NOT in the object : '.print_r($this->data,true));
		}
		/*#/if */
		//$methodName='get'.ucfirst($name);
		//if(!is_callable(array($this,$methodName)))
			return $this->data[$name];
		//return call_user_func(array($this,$methodName));
	}
	
	/**
	 * @param string
	 * @param mixed
	 * @return void
	 */
	public function __set($name,$value){
		//$methodName='get'.ucfirst($name);
		//if(!is_callable(array($this,$methodName)))
			$this->data[$name]=$value;
		//else
		//	call_user_func(array($this,$methodName),$value);
	}
	/**
	 * @param string
	 * @return void
	 */
	public function __unset($name){
		unset($this->data[$name]);
	}
	
	/**
	 * @param array
	 * @return void
	 */
	public function _setData($data){
		$this->data=$data;
	}
	
	/**
	 * @param array
	 * @return void
	 */
	public function _copyData($data){
		$d=array();
		foreach($data as $key=>$val){
			$d[$key]=$val;//copy
/*			$this->$key=$d[$key];*/
		}
		
		//set data is used here because it can be oevrrided
		$this->_setData($d);
	}
	
	/**
	 * @param array
	 * @return void
	 */
	public function mset($data){
		foreach($data as $key=>$val)
			$this->data[$key]=$val;
	}
	
	/**
	 * @return array
	 */
	public function &_getData(){
		return $this->data;
	}
	
	/**
	 * @param array
	 * @return array
	 */
	public function _getFields($names){
		$data=array();
		foreach($names as $name) $data[$name]=$this->data[$name];
		return $data;
	}
	
	/**
	 * @param string
	 * @param mixed
	 * @return SModel|self
	 */
	public function _set($name,$value){
		$this->data[$name]=$value;
		/*$this->$name=$this->data[$name];*/
		return $this;
	}
	
	/**
	 * @param string
	 * @param mixed
	 */
	public function _setRef($name,&$value){
		$this->data[$name]=&$value;
		/*$this->$name=&$this->data[$name];*/
	}
	
	/**
	 * @param string
	 * @return mixed
	 */
	public function _get($name){
		return $this->data[$name];
	}
	
	/**
	 * @param string
	 * @return mixed
	 */
	public function &_getRef($name){
		return $this->data[$name];
	}
	
	/**
	 * @return bool
	 */
	public function _isEmpty(){
		return empty($this->data);
	}
	
	/**
	 * @param array
	 * @return array
	 */
	protected function _getSaveData($args){
		if(empty($args)) return array_intersect_key($this->_getData(),static::$__PROP_DEF);
		$args[]='updated';
		return array_intersect_key($this->_getData(),array_flip($args),static::$__PROP_DEF);
	}
	
	/**
	 * @return SModel|self
	 */
	public function prepareUpdate(){
		$this->originalData=$this->data;
		return $this;
	}
	
	/**
	 * @param array
	 * @return array
	 */
	public function compareData($originalData=null){
		$keys=array_keys(array_diff_assoc($this->data,$originalData===null?$this->originalData:$originalData));
		if(empty($keys)) return false;
		return $this->_getSaveData($keys);
	}
	
	
	
	/*#if DEV */
	/*
	public function __call($method,$args){
		//$reflClass=new ReflectionClass(__CLASS__);
		//$knownMethods=array_map(function($m){ return $m->name; },$reflClass->getMethods(ReflectionMethod::IS_STATIC));
		
		throw new Exception("Call to undefined method {$method} in class ".get_called_class()
				."\nKnown methods :".implode(', ',get_class_methods(__CLASS__)));
	}*/
	/*#/if */
	
	
	
	/* Iterator */
	/*
	public function rewind(){
		reset($this->data);
	}
	public function current(){
		return current($this->data);
		//return $this->__get(key($this->data));
	}
	public function key(){
		return key($this->data);
	}
	public function next(){
		return next($this->data);
	}
	public function valid(){
		return $this->key();
	}*/
	/* http://www.garfieldtech.com/blog/benchmarking-magic
	 * Iterate internal iterator	22.87
	 * Iterate external iterator	6.06
	 * 
	 */
	/* IteratorAggregate */
	
	/**
	 * @return ArrayIterator
	 */
	public function getIterator(){
		return new ArrayIterator($this->data);
	}
	
	
	/* ArrayAccess */
	
	/**
	 * @param string
	 * @return bool
	 */
	public function offsetExists($offset){
		return isset($this->data[$offset]);
	}
	
	/**
	 * @param string
	 * @return mixed
	 */
	public function offsetGet($offset){
		return isset($this->data[$offset]) ? $this->data[$offset] : null;
	}
	
	/**
	 * @param string
	 * @param mixed
	 * @return void
	 */
	public function offsetSet($offset,$value){
		/*is_null($offset) ? $this->data[]=$value :*/$this->data[$offset]=$value;
	}
	
	/**
	 * @param string
	 * @return void
	 */
	public function offsetUnset($offset){
		unset($this->data[$offset]);
	}
	
	/* Serializable */
	
	/**
	 * Generates a storable representation of this model
	 * 
	 * @return string a string containing a byte-stream representation of value that can be stored anywhere.
	 * @see unserialize
	 */
	public function serialize(){
		return serialize($this->data);
	}
	
	/**
	 * Unserialize this model from a storable representation
	 * 
	 * @return void
	 * @see serialize
	 */
	public function unserialize($data){
		$this->data=unserialize($data);
	}
	
	
	
	/* Export */
	
	/**
	 * @return array
	 */
	public function toArray(){
		return self::_ToArray($this->data,static::$__PROP_DEF);
	}
	
	/**
	 * @param array
	 * @param array
	 * @return array
	 */
	private static function _ToArray($data,$props=null){
		foreach($data as $colName => $value){
			//if(is_array($var)) foreach($data[$colName] as $k=>&$v) self::_ToArray($data);
			if($props!== null && isset($props[$colName]) && $props[$colName]['type']){
				if($props[$colName]['type']==='boolean') $data[$colName]=$value!==null&&$value!==false&&$value!==0;
			}
		}
		return $data;
	}
	
	/**
	 * @return string
	 */
	public function toHtml(){
		$res='';
		foreach($this->_getData() as $key=>$value)
			if(!is_array($value)) $res.='<div>'._tF(static::$__className,$key).': '.$value.'</div>';
		return $res;
	}
	
	/**
	 * @return string
	 */
	public function toJSON(){
		return json_encode($this->toArray());
	}
	/**
	 * @return array
	 */
	public function jsonSerialize(){
		return $this->toArray();
	}
	
	/**
	 * @param array
	 * @param string
	 * @return string
	 */
	public static function json_encode($models,$suffix=''){
		if(empty($models)) return '[]';
		$res='';
		foreach($models as &$model) $res.=$model->{'toJSON'.$suffix}().',';
		return '['.substr($res,0,-1).']';
	}
	
	/**
	 * @param array
	 * @return array
	 */
	public static function mToArray($models){
		$res=array();
		if(!empty($models))
			foreach($models as $key=>&$model) $res[$key]=$model->toArray();
		return $res;
	}
	
	/**
	 * @return string
	 */
	public function __toString(){
		return var_export($this->data,true);
	}
	
	/* Callbacks */
	
	/**
	 * @return bool
	 */
	protected function _beforeInsert(){
		return $this->beforeSave() && $this->beforeInsert();
	}
	
	/**
	 * @return bool
	 */
	protected function _beforeUpdate(){
		return $this->beforeSave() && $this->beforeUpdate();
	}
	/**
	 * @return bool
	 */
	protected function beforeSave(){return true;}
	/**
	 * @return bool
	 */
	protected function beforeInsert(){return true;}
	/**
	 * @return bool
	 */
	protected function beforeUpdate(){return true;}
	/**
	 * @return bool
	 */
	protected function beforeDelete(){return true;}
	
	
	/**
	 * @return void
	 */
	protected function _afterInsert($data){
		$this->afterSave($data);
		$this->afterInsert($data);
	}
	/**
	 * @return void
	 */
	protected function _afterUpdate($data){
		$this->afterSave($data);
		$this->afterUpdate($data);
	}
	/**
	 * @return void
	 */
	protected function _afterUpdateCompare($data,$primaryKeys){
		$this->afterUpdateCompare($data,$primaryKeys);
		$this->_afterUpdate($data);
	}
	
	/**
	 * @return void
	 */
	protected function afterSave(){}
	/**
	 * @return void
	 */
	protected function afterInsert(){}
	/**
	 * @return void
	 */
	protected function afterUpdate(){}
	/**
	 * @param array
	 * @param array
	 * @return void
	 */
	protected function afterUpdateCompare($data,$primaryKeys){ }
	/**
	 * @return void
	 */
	protected function afterDelete(){}
	
	/* */
	
	/**
	 * @return mixed
	 */
	public function name(){
		return $this->data['name'];
	}
	
	/**
	 * @return mixed
	 */
	public function id(){
		return $this->data['id'];
	}
	
	/**
	 * @return bool
	 */
	public function isEditable(){
		return true;
	}
	
	/**
	 * @return bool
	 */
	public function isDeletable(){
		return true;
	}
	
	/* Helpers */
	
	/**
	 * @param string
	 * @param bool
	 * @return HElementForm
	 */
	public static function Form($name=null,$setValuesFromVar=true){
		return HElementForm::ForModel(static::$__className,$name,$setValuesFromVar);
	}
	
	/**
	 * @param string
	 * @param bool
	 * @return HElementForm
	 */
	public static function FormFile($name=null,$setValuesFromVar=true){
		return self::Form($name,$setValuesFromVar)->fileEnctype();
	}
	
	public function getTableClass(){ return null; }
}
