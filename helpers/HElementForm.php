<?php
/** 
 * Form Element
 * 
 * 
 * <b>Example 1</b>
 * <code>
 * {=$form=Post::Form()->action('/post/add')->fullUrl(Config::$siteUrl)->method(HForm::GET)->tagContainer('div')}
{=$form->fieldsetStart()}
{=$form->input('title')->label('Title of the new post')->attrId('PostNewTitleSpecialId')->container()->attr('id','divPostNewTitleContainerSpecialId)->after('I`m a text after the input !')}
{=$form->end()}
 * </code>
 * 
 * <b>Example 2</b>
 * <code>
 * <div class="clear mt20">
    {=$form=User::Form()->action('/site/login')->attrClass('w600 centered big')}
    <h2>{tC 'Sign in'}</h2>
    {=$form->fieldsetStart()}
    {=$form->input('email')}
    {=$form->input('pwd')}
    {=$form->submit(_tC('Sign in'))->container()->attrClass('center')}
    <? HHtml::tag('div',array('class'=>'center','style'=>'float:none'),HHtml::link(_tC('Password lost ?'),'/site/lostPassword'),false) ?>
    {=$form->end(false)}
</div>
 * </code>
 * 
 * generate : 
 * <code>
 * <div class="clear mt20">
    <form class="w600 centered big" method="post" action="/2011/projects/dev/site/login">
        <h2>Connexion</h2>
        <fieldset>
            <div class="input text email"><label for="UserEmail">Email</label> <input type="email" size="40" maxlength="100" required="required" name="user[email]" id="UserEmail"></div>
            <div class="input text password"><label for="UserPwd">Mot de passe</label> <input type="password" size="30" maxlength="40" required="required" value="" name="user[pwd]" id="UserPwd"></div>
            <div class="center"><input type="submit" class="submit" value="Connexion"></div>
            <div style="float:none" class="center"><a href="/2011/projects/dev/site/lostPassword">Mot de passe perdu ?</a></div>
        </fieldset>
    </form>
</div>
 * </code>
 */
class HElementForm extends HElement{
	/**
	 * Create a new HElementForm for a Model
	 * 
	 * @param string the name of the Model
	 * @param string the first part of the name attribute (eg: name="<b>post</b>[title]"). if null : lcfirst($modelName)
	 * @param bool
	 * @return HElementForm
	 */
	public static function ForModel($modelName,$name=null,$setValuesFromVar=true){
		$elt=new static('post');
		$elt->setModelName($modelName,$name,$setValuesFromVar);
		return $elt;
	}
	/**
	 * Create a new HElementForm for a Model with GET method
	 * 
	 * @param string the name of the Model
	 * @param string the first part of the name attribute (eg: name="<b>post</b>[title]"). if null : lcfirst($modelName)
	 * @param bool
	 * @return HElementForm
	 */
	public static function ForModelGET($modelName,$name=null,$setValuesFromVar=true){
		$elt=new static('get');
		$elt->setModelName($modelName,$name,$setValuesFromVar);
		return $elt;
	}
	
	/**
	 * Create a new HElementForm with POST method
	 * @return HElementForm
	 */
	public static function Post(){ return new static('post'); }
	/**
	 * Create a new HElementForm with GET method
	 * @return HElementForm
	 */
	public static function Get(){ return new static('get'); }
	/**
	 * Create a new HElementForm for File upload
	 * @return HElementForm
	 */
	public static function File(){
		$elt= new static('post');
		return $elt->fileEnctype();
	}
	
	/**
	 * set enctype to multipart/form-data
	 * 
	 * @return HElementForm
	 */
	public function fileEnctype(){
		$this->attr('enctype','multipart/form-data');
		return $this;
	}
	
	/** @ignore */
	public $method,$action,$actionEntry,$urlfull,
			$defaultLabel=true,$name,$modelName,
			$tagContainer='div',$fieldsetStarted=false;
	
	/** @ignore */
	public function __construct($method='post'){
		$this->method=$method;
	}
	
	/**
	 * reset modelName and name
	 */
	public function basic(){
		$this->modelName=null;
		$this->name=null;
	}
	
	/**
	 * set a new model for an existing form
	 * 
	 * @param string the name of the Model
	 * @param string the first part of the name attribute (eg: name="<b>post</b>[title]"). if null : lcfirst($modelName)
	 * @param bool
	 */
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
	 * Set the action attribute using the Router
	 * 
	 * @return HElementForm
	 */
	public function action($action,$entry=null,$urlfull=null){
		$this->action = $action;
		$this->actionEntry = $entry;
		$this->urlfull = $urlfull;
		return $this;
	}
	
	/**
	 * Set the first part of the url (http://.../)
	 * 
	 * @return HElementForm
	 */
	public function urlfull($urlfull){
		$this->urlfull = $urlfull;
		return $this;
	}
	//public function &file(){ /* $this->method='post'; */ $this->attributes['enctype']='multipart/form-data'; return $this; }
	
	/**
	 * return if an element inside a form can be contained in a HElementFormContainer
	 * 
	 * @return bool
	 */
	public function isContainable(){ return $this->tagContainer!==false; }
	
	/**
	 * return the tag container (div, li, td, ...)
	 * 
	 * @return string|false
	 */
	public function getTagContainer(){ return $this->tagContainer; }
	
	/**
	 * set the tag container (div, li, td, ...)
	 * 
	 * @param string
	 * @return HElementForm
	 */
	public function tagContainer($tagContainer){
		$this->tagContainer=$tagContainer;
		return $this;
	}
	
	/**
	 * Prevent inside elements to be contained
	 * 
	 * @return HElementForm
	 */
	public function noContainer(){ $this->tagContainer=false; return $this; }
	
	/**
	 * Prevent inside elements to have a label by default
	 * 
	 * @return HElementForm
	 */
	public function noDefaultLabel(){ $this->defaultLabel=false; return $this; }
	
	/**
	 * Ajax form : the submit event is captured and sent in ajax
	 * 
	 * @param bool if the form send the submit event every time an element is changed 
	 * @return HElementForm
	 */
	public function ajax($saveOnChange=false){
		HHtml::jsReady('$("#'.$this->getAttr('id').'").ht5ifv().ajaxForm()'
			.($saveOnChange?'.change(function(e){$(this).submit()})':''));
		return $this;
	}
	
	/**
	 * set onsubmit attribute
	 * 
	 * @param string onsubmit value
	 * @return HElementForm
	 */
	public function onSubmit($onSubmit){
		$this->attributes['onsubmit']=$onSubmit;
		return $this;
	}
	
	public function __toString(){
		return '<form action="'.HHtml::url($this->action===null ? CRoute::getAll() : $this->action,$this->actionEntry,$this->urlfull,true).'"'
				.' method="'.$this->method.'"'.$this->_attributes().'>';
	}
	
	
	/**
	 * end the form and return the result
	 * 
	 * @param string|bool title of the submit button, true for default, false for no submit
	 * @return string
	 */
	public function end($title=true){
		if($title!==false) $res=$this->submit($title);
		else $res='';
		if($this->fieldsetStarted) $res.=$this->fieldsetStop();
		return $res.'</form>';
	}
	
	
	/**
	 * auto field for all the fields of the model except those with autoincrement and created,updated,modified,deleted
	 * 
	 * @return string
	 */
	public function all(){
		$modelName=$this->modelName; $res='';
		foreach($modelName::$__PROP_DEF as $name=>&$def){
			$infos=$modelName::$__modelInfos['columns'][$name];
			if(! $infos['autoincrement'] && !in_array($name,array('created','updated','modified','deleted'))){
				$res.=$this->autoField($name,$def);
			}
		}
		return $res;
	}
	/**
	 * @param array list of fields
	 * @return string
	 */
	public function autoFields($fields){
		if(is_string($fields)) $fields=explode(',',$fields);
		$res='';
		foreach($fields as &$field) $res.=$this->autoField($name);
		return $res;
	}
	/**
	 * 
	 * @param string
	 * @return string
	 */
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
	
	/**
	 * create a fieldset with a legend
	 * 
	 * @param legend 
	 * @return string
	 */
	public function fieldsetStart($legend=false){
		$this->fieldsetStarted = true;
		return '<fieldset>'.($legend!==false ? ('<legend>'.h($legend).'</legend>') : '');
	}
	/**
	 * end the fieldset
	 * 
	 * @return string
	 */
	public function fieldsetStop(){
		$this->fieldsetStarted=false;
		return '</fieldset>';
	}
	
	/**
	 * create a input or a textarea
	 * 
	 * @param string
	 * @return HElementFormInput|HElementFormTextarea
	 */
	public function text($name){
		$modelName=$this->modelName;
		if(isset($modelName::$__PROP_DEF[$name]['annotations']['Text'])) return $this->textarea($name);
		return $this->input($name);
	}
	
	/**
	 * create a input
	 * 
	 * @param string
	 * @param float multiplier
	 * @return HElementFormInput
	 */
	public function input($name,$largeSize=1){
		return new HElementFormInput($this,$name,$largeSize);
	}
	
	/**
	 * create a input file
	 * 
	 * @return HElementFormInput
	 */
	public function inputFile($name){
		return $this->input($name)->setType('file');
	}
	
	/**
	 * create a input file
	 * 
	 * @return HElementFormInput
	 */
	public function textarea($name){
		return new HElementFormTextarea($this,$name);
	}
	
	/**
	 * create a hidden input
	 * 
	 * @param string|bool label string or true if default (_tC("Save"))
	 * @return HElementFormInputHidden
	 */
	public function hidden($name,$value=false){
		return new HElementFormInputHidden($this,$name,$value);
	}
	
	/**
	 * create a input submit
	 * 
	 * @param string|bool label string or true if default (_tC("Save"))
	 * @return HElementFormInputSubmit
	 */
	public function submit($title=true){
		return new HElementFormInputSubmit($this,$title);
	}
	
	/**
	 * create a input submit
	 * 
	 * @param string
	 * @param string|bool label string or false if none
	 * @return HElementFormInputCheckbox
	 */
	public function checkbox($name,$label=false){
		return new HElementFormInputCheckbox($this,$name,$label);
	}
	
	/**
	 * create a select
	 * 
	 * @param string
	 * @param list array Array of key=>values or objects
	 * @param mixed the selected value
	 * @return HElementFormInputSelect
	 */
	public function select($name,$list=null,$selected=null){
		return new HElementFormInputSelect($this,$name,$list,$selected);
	}
	
	/**
	 * create stars
	 * 
	 * @param name name attribute
	 * @param nbStarts the number of stars
	 * @return HElementFormStars
	 */
	public function stars($name,$nbStars=5){
		return new HElementFormStars($this,$name,$nbStars);
	}
	
	/** @ignore */
	public function _getValue(&$name){
		$TAB=null;
		if($this->method=='post') $TAB=$_POST;
		elseif($this->method=='get') $TAB=$_GET;
		if($this->modelName === null) return isset($TAB[$name])? $TAB[$name] : null;
		else return isset($TAB[$this->name][$name]) ? $TAB[$this->name][$name] : null;
		return null;
	}
}