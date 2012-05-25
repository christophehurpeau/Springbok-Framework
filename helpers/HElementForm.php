<?php
class HElementForm extends HElement{
	public static function &forModel($modelName,$name=null,$setValuesFromVar=true){
		$elt=new self;
		$elt->setModelName($modelName,$name,$setValuesFromVar);
		return $elt;
	}
	
	public static function &basic(){
		$elt=new self;
		//$elt->_basic();
		return $elt;
	}
	
	public $method='post',$urlfull=false,$action,
			$defaultLabel=true,$setValuesFromVar=true,$name,$modelName,
			$tagContainer='div',$fieldsetStarted=false;
	
	
	public function basic(){
		$this->modelName=null;
		$this->name=null;
	}
	
	public function setModelName($modelName=null,$name=null,$setValuesFromVar=true){
		if($name===null && $modelName !== null) $name=lcfirst($modelName);
		$this->modelName=&$modelName; $this->name=&$name;

		if($setValuesFromVar && $name && Controller::_isset($name)){
			$val=&Controller::get($name);

			if($val && $val!==false){///TODO : to do after method can be changeable
				if($this->method==='post'){ if(empty($_POST[$name])) $_POST[$name]=&$val->_getData(); }
				elseif($this->method==='get'){ if(empty($_GET[$name])) $_GET[$name]=&$val->_getData(); }
			}
		}
	}
	
	public function &action($action){$this->action=&$action; return $this; }
	public function &file(){ /* $this->method='post'; */ $this->attributes['enctype']='multipart/form-data'; return $this; }
	
	public function isContainable(){ return $this->tagContainer!==false; }
	public function getTagContainer(){ return $this->tagContainer; }
	public function &tagContainer($tagContainer){ $this->tagContainer=&$tagContainer; return $this; }
	public function &noContainer(){ $this->tagContainer=false; return $this; }
	
	
	public function __toString(){
		return '<form action="'.HHtml::url($this->action===null ? CRoute::getAll() : $this->action,$this->urlfull,true).'"'
				.' method="'.$this->method.'"'.$this->_attributes().'>';
	}
	
	public function end($title=true,$options=array(),$containerAttributes=NULL){
		if($title) $res=$this->submit($title,$options,$containerAttributes);
		else $res='';
		if($this->fieldsetStarted) $res.=$this->fieldsetStop();
		return $res.'</form>';
	}
	
	
	
	public function &all(){
		$modelName=&$this->modelName; $res='';
		foreach($modelName::$__PROP_DEF as $name=>&$def){
			$infos=$modelName::$__modelInfos['columns'][$name];
			if(! $infos['autoincrement'] && !in_array($name,array('created','updated','modified'))){
				$res.=$this->autoField($name,$def);
			}
		}
		return $res;
	}
	public function &autoFields($fields){
		if(is_string($fields)) $fields=explode(',',$fields);
		$res='';
		foreach($fields as &$field) $res.=$this->autoField($name);
		return $res;
	}
	public function autoField($name,$def=null){
		$modelName=&$this->modelName;
		if($def===null) $def=&$modelName::$__PROP_DEF[$name];
		/*if($infos['notnull']===false){
			$attr=array('id'=>'checkboxCRUD'.$modelName.$name);
			if($this->_getValue($name)===null) $attr['checked']=true;
			echo $this->checkbox($name,'NULL',$attr);
		}*/
		//foreach($data as $key=>&$val) if($val===null) $val='NULL';
		if(substr($name,-3)==='_id' && Controller::_isset($vname=UInflector::pluralize(substr($name,0,-3))))
			return $this->select($name,Controller::get($vname));
		elseif($def['type']==='boolean'){
			$attrs=$attributes;
			if($this->_getValue($name)==='') $attrs['checked']=true;
			return $this->hidden($name,'').$this->checkbox($name,_tF($modelName,$name),$attrs,$containerAttributes);
		}elseif(isset($def['annotations']['Enum'])) return $this->select($name,call_user_func($modelName.'::'.$def['annotations']['Enum'].'List'),$attributes,$containerAttributes);
		elseif(isset($def['annotations']['Text'])) return $this->textarea($name,$attributes,$containerAttributes);
		else return $this->input($name,$attributes,$containerAttributes);
	}
	
	public function fieldsetStart($legend=false){
		$this->fieldsetStarted=true;
		return '<fieldset>'.($legend!==false ? ('<legend>'.h($legend).'</legend>') : '');
	}
	public function fieldsetStop(){
		$this->fieldsetStarted=false;
		return '</fieldset>';
	}
	
	
	public function &text($name){
		$modelName=&$this->modelName;
		if(isset($modelName::$__PROP_DEF[$name]['annotations']['Text'])) return $this->textarea($name);
		return $this->input($name);
	}
	
	
	
	public function _getValue(&$name){
		$TAB=NULL;
		if($this->method=='post') $TAB=&$_POST;
		elseif($this->method=='get') $TAB=&$_GET;
		if($this->modelName === NULL) return isset($TAB[$name])? $TAB[$name] : NULL;
		else return isset($TAB[$this->name][$name]) ? $TAB[$this->name][$name] : NULL;
		return NULL;
	}
}