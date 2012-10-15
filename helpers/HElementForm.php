<?php
class HElementForm extends HElement{
	public static function ForModel($modelName,$name=null,$setValuesFromVar=true){
		$elt=new static('post');
		$elt->setModelName($modelName,$name,$setValuesFromVar);
		return $elt;
	}
	public static function ForModelGET($modelName,$name=null,$setValuesFromVar=true){
		$elt=new static('get');
		$elt->setModelName($modelName,$name,$setValuesFromVar);
		return $elt;
	}
	
	public static function Post(){ return new static('post'); }
	public static function Get(){ return new static('get'); }
	public static function File(){
		$elt= new static('post');
		$elt->attr('enctype','multipart/form-data');
		return $elt;
	}
	
	public $method,$action,$actionEntry,$urlfull,
			$defaultLabel=true,$name,$modelName,
			$tagContainer='div',$fieldsetStarted=false;
	
	
	public function __construct($method='post'){
		$this->method=$method;
	}
	
	public function basic(){
		$this->modelName=null;
		$this->name=null;
	}
	
	public function setModelName($modelName=null,$name=null,$setValuesFromVar=true){
		if($name===null && $modelName !== null) $name=lcfirst($modelName);
		$this->modelName=$modelName; $this->name=$name;

		if($setValuesFromVar && $name && Controller::_isset($name)){
			$val=Controller::get($name);

			if($val && $val!==false){///TODO : to do after method can be changeable
				if($this->method==='post'){ if(empty($_POST[$name])) $_POST[$name]=&$val->_getData(); }
				elseif($this->method==='get'){ if(empty($_GET[$name])) $_GET[$name]=&$val->_getData(); }
			}
		}
	}
	
	/**
	 * @return HElementForm
	 */
	public function action($action,$entry=null,$urlfull=null){$this->action=$action; $this->actionEntry=$entry; $this->urlfull=$urlfull; return $this; }
	public function urlfull($urlfull){$this->urlfull=$urlfull; return $this; }
	//public function &file(){ /* $this->method='post'; */ $this->attributes['enctype']='multipart/form-data'; return $this; }
	
	public function isContainable(){ return $this->tagContainer!==false; }
	public function getTagContainer(){ return $this->tagContainer; }
	public function tagContainer($tagContainer){ $this->tagContainer=$tagContainer; return $this; }
	public function noContainer(){ $this->tagContainer=false; return $this; }
	public function noDefaultLabel(){ $this->defaultLabel=false; return $this; }
	
	public function ajax($saveOnChange=false){ HHtml::jsReady('$("#'.$this->getAttr('id').'").ht5ifv().ajaxForm()'
		.($saveOnChange?'.change(function(e){$(this).submit()})':'')); return $this; }
	
	public function onSubmit($onSubmit){ $this->attributes['onsubmit']=$onSubmit; return $this; }
	
	public function __toString(){
		return '<form action="'.HHtml::url($this->action===null ? CRoute::getAll() : $this->action,$this->actionEntry,$this->urlfull,true).'"'
				.' method="'.$this->method.'"'.$this->_attributes().'>';
	}
	
	public function end($title=true){
		if($title!==false) $res=$this->submit($title);
		else $res='';
		if($this->fieldsetStarted) $res.=$this->fieldsetStop();
		return $res.'</form>';
	}
	
	
	
	public function all(){
		$modelName=$this->modelName; $res='';
		foreach($modelName::$__PROP_DEF as $name=>&$def){
			$infos=$modelName::$__modelInfos['columns'][$name];
			if(! $infos['autoincrement'] && !in_array($name,array('created','updated','modified'))){
				$res.=$this->autoField($name,$def);
			}
		}
		return $res;
	}
	public function autoFields($fields){
		if(is_string($fields)) $fields=explode(',',$fields);
		$res='';
		foreach($fields as &$field) $res.=$this->autoField($name);
		return $res;
	}
	public function autoField($name,$def=null){
		$modelName=$this->modelName;
		if($def===null) $def=$modelName::$__PROP_DEF[$name];
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
	
	
	public function text($name){
		$modelName=$this->modelName;
		if(isset($modelName::$__PROP_DEF[$name]['annotations']['Text'])) return $this->textarea($name);
		return $this->input($name);
	}
	
	public function input($name,$largeSize=1){
		return new HElementFormInput($this,$name,$largeSize);
	}
	public function textarea($name){
		return new HElementFormTextarea($this,$name);
	}
	
	public function hidden($name,$value=false){
		return new HElementFormInputHidden($this,$name,$value);
	}
	
	public function submit($title=true){
		return new HElementFormInputSubmit($this,$title);
	}
	
	public function checkbox($name,$label=false){
		return new HElementFormInputCheckbox($this,$name,$label);
	}
	
	public function select($name,$list=null,$selected=null){
		return new HElementFormInputSelect($this,$name,$list,$selected);
	}
	
	public function stars($name,$nbStars=5){
		return new HElementFormStars($this,$name,$nbStars);
	}
	
	public function _getValue(&$name){
		$TAB=null;
		if($this->method=='post') $TAB=$_POST;
		elseif($this->method=='get') $TAB=$_GET;
		if($this->modelName === null) return isset($TAB[$name])? $TAB[$name] : null;
		else return isset($TAB[$this->name][$name]) ? $TAB[$this->name][$name] : null;
		return null;
	}
}