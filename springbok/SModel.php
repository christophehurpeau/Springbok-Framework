<?php
class SModel implements Iterator{
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
			$this->data[$name]=&$value;
		//else
		//	call_user_func(array($this,$methodName),$value);
	}
	public function __unset($name){
		unset($this->data[$name]);
	}
	
	public function _setData(&$data){
		$this->data=&$data;
	}
	public function _copyData(&$data){
		$d=array();
		foreach($data as $key=>$val){
			$d[$key]=$val;//copy
/*			$this->$key=&$d[$key];*/
		}
		$this->_setData($d);
	}
	
	public function &_getData(){
		return $this->data;
	}
	
	public function _set($name,&$value){
		$this->data[$name]=$value;
		/*$this->$name=&$this->data[$name];*/
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
	public static function json_encode($models,$suffix=''){
		if(empty($models)) return '[]';
		$res='';
		foreach($models as &$model) $res.=$model->{'toJSON'.$suffix}().',';
		return '['.substr($res,0,-1).']';
	}
	
	public static function &mToArray($models){
		if(empty($models)) return array();
		$res=array();
		foreach($models as $key=>&$model) $res[$key]=&$model->toArray();
		return $res;
	}
	
	public function __toString(){
		return UPhp::exportCode($this->data);
	}
}
