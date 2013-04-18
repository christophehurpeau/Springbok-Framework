<?php
abstract class SModel implements IteratorAggregate,ArrayAccess,Serializable,JsonSerializable{
	public static $__dbName='default',$__modelDb,$__displayField='name',$__orderByField=null;
	public static $__loadedModels=array();
	
	public static function init($modelName){
		$modelName::$__modelDb=DB::init(static::$__dbName);
		self::$__loadedModels[]=$modelName;
	}
	public static function updateAllDB(){
		foreach(self::$__loadedModels as $model) $model::updateDB();
	}
	public static function updateDB(){static::$__modelDb=DB::get(static::$__dbName);}
	public static function getDB(){return static::$__modelDb;}
	
	
	/* Properties */
	
	protected $data=array(),$originalData;
	
	public function __isset($name){
		return isset($this->data[$name]);
	}
	public function isKeyExists($key){
		return array_key_exists($key,$this->data);
	}
	
	public function __get($name){
		/* DEV */
		if(!/*isset($this->data[$name])*/array_key_exists($name,$this->data)){//isset does'nt work if the value is null
			throw new Exception($name.' IS NOT in the object : '.print_r($this->data,true));
		}
		/* /DEV */
		//$methodName='get'.ucfirst($name);
		//if(!is_callable(array($this,$methodName)))
			return $this->data[$name];
		//return call_user_func(array($this,$methodName));
	}
	public function __set($name,$value){
		//$methodName='get'.ucfirst($name);
		//if(!is_callable(array($this,$methodName)))
			$this->data[$name]=$value;
		//else
		//	call_user_func(array($this,$methodName),$value);
	}
	public function __unset($name){
		unset($this->data[$name]);
	}
	
	public function _setData($data){
		$this->data=$data;
	}
	public function _copyData($data){
		$d=array();
		foreach($data as $key=>$val){
			$d[$key]=$val;//copy
/*			$this->$key=$d[$key];*/
		}
		$this->_setData($d);
	}
	
	public function mset($data){
		foreach($data as $key=>$val)
			$this->data[$key]=$val;
	}
	
	public function &_getData(){
		return $this->data;
	}
	public function _getFields($names){
		$data=array();
		foreach($names as $name) $data[$name]=$this->data[$name];
		return $data;
	}
	
	public function _set($name,$value){
		$this->data[$name]=$value;
		/*$this->$name=$this->data[$name];*/
		return $this;
	}
	public function _setRef($name,&$value){
		$this->data[$name]=&$value;
		/*$this->$name=&$this->data[$name];*/
	}
	public function _get($name){
		return $this->data[$name];
	}
	public function &_getRef($name){
		return $this->data[$name];
	}
	public function _isEmpty(){
		return empty($this->data);
	}
	
	protected function _getSaveData($args){
		if(empty($args)) return array_intersect_key($this->_getData(),static::$__PROP_DEF);
		$args[]='updated';
		return array_intersect_key($this->_getData(),array_flip($args),static::$__PROP_DEF);
	}
	
	
	public function prepareUpdate(){
		$this->originalData=$this->data;
		return $this;
	}
	
	public function compareData($originalData=null){
		$keys=array_keys(array_diff_assoc($this->data,$originalData===null?$this->originalData:$originalData));
		if(empty($keys)) return false;
		return $this->_getSaveData($keys);
	}
	
	
	
	/* DEV */
	/*
	public function __call($method,$args){
		//$reflClass=new ReflectionClass(__CLASS__);
		//$knownMethods=array_map(function($m){ return $m->name; },$reflClass->getMethods(ReflectionMethod::IS_STATIC));
		
		throw new Exception("Call to undefined method {$method} in class ".get_called_class()
				."\nKnown methods :".implode(', ',get_class_methods(__CLASS__)));
	}*/
	/* /DEV */
	
	
	
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
	public function getIterator(){
		return new ArrayIterator($this->data);
	}
	
	
	/* ArrayAccess */
	public function offsetExists($offset){
		return isset($this->data[$offset]);
	}
	public function offsetGet($offset){
		return isset($this->data[$offset]) ? $this->data[$offset] : null;
	}
	public function offsetSet($offset,$value){
		/*is_null($offset) ? $this->data[]=$value :*/$this->data[$offset]=$value;
	}
	public function offsetUnset($offset){
		unset($this->data[$offset]);
	}
	
	/* Serializable */
	public function serialize(){
		return serialize($this->data);
	}
	public function unserialize($data){
		$this->data=unserialize($data);
	}
	
	
	
	/* Export */
	public function toArray(){
		return self::_ToArray($this->data,static::$__PROP_DEF);
	}
	
	private static function _ToArray($data,$props=null){
		foreach($data as $colName => $value){
			//if(is_array($var)) foreach($data[$colName] as $k=>&$v) self::_ToArray($data);
			if($props!== null && isset($props[$colName]) && $props[$colName]['type']){
				if($props[$colName]['type']==='boolean') $data[$colName]=$value!==null&&$value!==false&&$value!==0;
			}
		}
		return $data;
	}
	
	public function toHtml(){
		$res='';
		foreach($this->_getData() as $key=>$value)
			if(!is_array($value)) $res.='<div>'._tF(static::$__className,$key).': '.$value.'</div>';
		return $res;
	}
	
	public function toJSON(){
		return json_encode($this->toArray());
	}
	public function jsonSerialize(){
		return $this->toArray();
	}
	
	public static function json_encode($models,$suffix=''){
		if(empty($models)) return '[]';
		$res='';
		foreach($models as &$model) $res.=$model->{'toJSON'.$suffix}().',';
		return '['.substr($res,0,-1).']';
	}
	
	public static function mToArray($models){
		$res=array();
		if(!empty($models))
			foreach($models as $key=>&$model) $res[$key]=$model->toArray();
		return $res;
	}
	
	public function __toString(){
		return var_export($this->data,true);
	}
	
	/* Callbacks */

	protected function _beforeInsert(){
		return $this->beforeSave() && $this->beforeInsert();
	}
	
	protected function _beforeUpdate(){
		return $this->beforeSave() && $this->beforeUpdate();
	}
	protected function beforeSave(){return true;}
	protected function beforeInsert(){return true;}
	protected function beforeUpdate(){return true;}
	protected function beforeDelete(){return true;}
	
	
	protected function _afterInsert($data){
		$this->afterSave($data);
		$this->afterInsert($data);
	}
	protected function _afterUpdate($data){
		$this->afterSave($data);
		$this->afterUpdate($data);
	}
	protected function _afterUpdateCompare($data,$primaryKeys){
		$this->afterUpdateCompare($data,$primaryKeys);
		$this->_afterUpdate($data);
	}
	
	protected function afterSave(){}
	protected function afterInsert(){}
	protected function afterUpdate(){}
	protected function afterUpdateCompare($data,$primaryKeys){ }
	protected function afterDelete(){}
	
	/* */
	
	public function name(){
		return $this->data['name'];
	}
	public function id(){
		return $this->data['id'];
	}
	public function isEditable(){
		return true;
	}
	public function isDeletable(){
		return true;
	}
	
	/* Helpers */
	
	public static function Form($name=null,$setValuesFromVar=true){
		return HElementForm::ForModel(static::$__className,$name,$setValuesFromVar);
	}
	
	public function getTableClass(){ return null; }
}
