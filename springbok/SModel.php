<?php
abstract class SModel implements Iterator/*,JsonSerializable*/{
	public static $__dbName='default',$__modelDb,$__displayField='name',$__orderByField=null;
	public static $__loadedModels=array();
	
	public static function init($modelName){
		$modelName::$__modelDb=DB::init(static::$__dbName);
		self::$__loadedModels[]=$modelName;
		$modelName::$__modelInfos=include Config::$models_infos.$modelName;
		$modelName::$_relations=&$modelName::$__modelInfos['relations'];
		$modelName::$__PROP_DEF=&$modelName::$__modelInfos['props'];
	}
	public static function updateAllDB(){
		foreach(self::$__loadedModels as $model) $model::updateDB();
	}
	public static function updateDB(){static::$__modelDb=DB::get(static::$__dbName);}
	public static function getDB(){return static::$__modelDb;}
	
	
	/* Properties */
	
	protected $data=array();
	
	public function __isset($name){
		return isset($this->data[$name]);
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
	
	public function &_getData(){
		return $this->data;
	}
	
	public function _set($name,$value){
		$this->data[$name]=$value;
		/*$this->$name=$this->data[$name];*/
	}
	public function _setRef($name,&$value){
		$this->data[$name]=&$value;
		/*$this->$name=&$this->data[$name];*/
	}
	public function &_get($name){
		return $this->data[$name];
	}
	public function _isEmpty(){
		return empty($this->data);
	}
	
	
	protected function _getSaveData($args){
		return !empty($args) ? array_intersect_key($this->_getData(),array_flip($args),static::$__PROP_DEF) : array_intersect_key($this->_getData(),static::$__PROP_DEF);
	}
	
	
	/* Iterator */
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
	}
	
	
	/* Export */
	public function toArray(){
		return $this->data;
	}
	
	public function toHtml(){
		$res='';
		foreach($this->_getData() as $key=>$value)
			if(!is_array($value)) $res.='<div>'._tF(static::$__className,$key).': '.$value.'</div>';
		return $res;
	}
	
	public function toJSON(){
		return json_encode($this->_getData());
	}
	public function jsonSerialize(){
		return json_encode($this->_getData());
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
		return UPhp::exportCode($this->data);
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
	
	protected function afterSave(){}
	protected function afterInsert(){}
	protected function afterUpdate(){}
	protected function afterDelete(){}
	
	
	/* Helpers */
	
	public static function Form($name=null,$setValuesFromVar=true){
		return HElementForm::ForModel(static::$__className,$name,$setValuesFromVar);
	}
}
