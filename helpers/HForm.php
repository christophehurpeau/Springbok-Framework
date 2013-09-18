<?php
/**
 * Help easily create a form using HElementForm
 * 
 * @see HElementForm
 */
class HForm{
	/**
	 * Create a new HElementForm with POST method
	 * @return HElementForm
	 */
	public static function Post(){ return HElementForm::Post(); }
	/**
	 * Create a new HElementForm with GET method
	 * @return HElementForm
	 */
	public static function Get(){ return HElementForm::Get(); }
	/**
	 * Create a new HElementForm for File upload
	 * @return HElementForm
	 */
	public static function File(){ return HElementForm::File(); }
	/**
	 * Create a new HElementForm for Ajax File upload
	 * @return HElementAjaxFileForm
	 */
	public static function AjaxFile($formId,$action,$jsParams,$entry=null,$urlfull=null){
		return new HElementAjaxFileForm($formId,$action,$jsParams,$entry,$urlfull);
	}
	
	/* OLD WAY */
	/**
	 * @deprecated use HElementForm now
	 * @return HForm
	 */
	public static function create($modelName=null,$formOptions=array(),$tagContainer='div',$options=array()){
		if(is_bool($options)) $options=array('defaultLabel'=>$options);
		$options=$options+array('defaultLabel'=>true,'setValuesFromVar'=>true);
		
		$setValuesFromVar=true;
		if($formOptions!==null){
			$formOptions=$formOptions+array('method'=>'post','entry'=>null,'urlfull'=>null);
			if(!isset($formOptions['action'])) $formOptions['action']=CRoute::getAll();
			if(!isset($formOptions['name'])){
				if($modelName===NULL) $name=NULL;
				else $name=lcfirst($modelName);
			}else $name=$formOptions['name'];
			if($formOptions['method']==='file'){
				$formOptions['enctype']='multipart/form-data';
				$formOptions['method']='post';
			}
			echo '<form action="'.HHtml::urlEscape($formOptions['action'],$formOptions['entry'],$formOptions['urlfull']).'" method="'.($method=$formOptions['method']).'"';
			unset($formOptions['action'],$formOptions['method'],$formOptions['name'],$formOptions['urlfull'],$formOptions['entry']);
			HHtml::_echoAttributes($formOptions);
			echo '>';
		}

		return new HForm($modelName,$name,$method,$tagContainer,$options['defaultLabel'],$options['setValuesFromVar']);
	}
	
	private $modelName,$name,$method,$tagContainer,$defaultLabel;
	/** @deprecated */
	public function __construct($modelName,$name,$method,$tagContainer,$defaultLabel,$setValuesFromVar){
		$this->method=$method;
		$this->setModelName($modelName,$name,$setValuesFromVar);
		$this->tagContainer=$tagContainer;
		$this->defaultLabel=$defaultLabel;
	}
	
	/** @deprecated */
	private function _name($name){
		return $this->modelName !== NULL ? $this->name.'['.$name.']' : $name;
	}
	
	/** @deprecated */
	public function setModelName($modelName=NULL,$name=NULL,$setValuesFromVar=true){
		if($name===NULL && $modelName !== NULL) $name=lcfirst($modelName);
		$this->modelName=$modelName;$this->name=$name;

		if($setValuesFromVar && $name && Controller::_isset($name)){
			$val=Controller::get($name);

			if($val && $val!==false){
				if($this->method=='post'){ if(empty($_POST[$name])) $_POST[$name]=&$val->_getData(); }
				elseif($this->method=='get'){ if(empty($_GET[$name])) $_GET[$name]=&$val->_getData(); }
			}
		}
	}

	/** @deprecated */
	public function _setModelName($modelName){
		$this->modelName=$modelName;
	}
	
	/** @deprecated */
	public function setTagContainer($tagContainer){
		$this->tagContainer=&$tagContainer;
	}
	
	/** @deprecated */
	public function all($attributes=array(),$containerAttributes=array()){
		$modelName=$this->modelName;
		foreach($modelName::$__PROP_DEF as $name=>&$def){
			$infos=$modelName::$__modelInfos['columns'][$name];
			if(! $infos['autoincrement'] && !in_array($name,array('created','updated','modified'))){
				$this->autoField($name,$attributes,$containerAttributes,$def);
			}
		}
	}
	/** @deprecated */
	public function autoFields($fields,$attributes=array(),$containerAttributes=array()){
		if(is_string($fields)) $fields=explode(',',$fields);
		foreach($fields as $name) $this->autoField($name,$attributes,$containerAttributes);
	}
	/** @deprecated */
	public function autoField($name,$attributes=array(),$containerAttributes=array(),$def=null){
		$modelName=$this->modelName;
		if($def===null) $def=&$modelName::$__PROP_DEF[$name];
		/*if($infos['notnull']===false){
			$attr=array('id'=>'checkboxCRUD'.$modelName.$name);
			if($this->_getValue($name)===null) $attr['checked']=true;
			echo $this->checkbox($name,'NULL',$attr);
		}*/
		//foreach($data as $key=>&$val) if($val===null) $val='NULL';
		if(substr($name,-3)==='_id' && Controller::_isset($vname=UInflector::pluralize(substr($name,0,-3))))
			echo $this->select($name,Controller::get($vname),$attributes);
		elseif($def['type']==='boolean'){
			$attrs=$attributes;
			if($this->_getValue($name)==='') $attrs['checked']=true;
			echo $this->hidden($name,'').$this->checkbox($name,_tF($modelName,$name),$attrs,$containerAttributes);
		}elseif(isset($def['annotations']['Enum'])) echo $this->select($name,call_user_func($modelName.'::'.$def['annotations']['Enum'].'List'),$attributes,$containerAttributes);
		elseif(isset($def['annotations']['Text'])) echo $this->textarea($name,$attributes,$containerAttributes);
		else echo $this->input($name,$attributes,$containerAttributes);
	}
	
	/** @deprecated */
	public function end($title=true,$options=array(),$containerAttributes=NULL){
		if($title) echo $this->submit($title,$options,$containerAttributes);
		if($this->fieldsetStarted) echo $this->fieldsetStop();
		echo '</form>';
	}
	
	private $fieldsetStarted=false;
	/** @deprecated */
	public function fieldsetStart($legend=false){
		$this->fieldsetStarted=true;
		return '<fieldset>'.($legend ? ('<legend>'.h($legend).'</legend>') : '');
	}
	
	/** @deprecated */
	public function fieldsetStop(){
		$this->fieldsetStarted=false;
		return '</fieldset>';
	}
	
	/** @deprecated */
	public function text($name,$attributes=array(),$containerAttributes=array()){
		$modelName=&$this->modelName;
		if(isset($modelName::$__PROP_DEF[$name]['annotations']['Text'])) return $this->textarea($name,$attributes,$containerAttributes);
		return $this->input($name,$attributes,$containerAttributes);
	}
	
	/** @deprecated */
	public function textarea($name,$attributes=array(),$containerAttributes=array()){
		if(is_string($attributes)){
			$value=$attributes;
			$attributes=array();
		}
		
		if($this->modelName != NULL){
			if(!isset($attributes['name'])) $attributes['name']=$this->name.'['.$name.']';
			elseif($attributes['name']===false) unset($attributes['name']);
			
			$modelName=$this->modelName;
			if(isset($modelName::$__PROP_DEF[$name])){
				$propDef=$modelName::$__PROP_DEF[$name];
				if(isset($propDef['annotations']['Required'])) $attributes['required']=true;
				if(isset($propDef['annotations']['MaxLength'])){
					$attributes['maxlength']=$propDef['annotations']['MaxLength'][0];
					if(!isset($attributes['rows'])) $attributes['rows']=5;
				}
			}
		}elseif(!isset($attributes['name'])) $attributes['name']=$name;
		elseif($attributes['name']===false) unset($attributes['name']);
		
		$attributes+=array('rows'=>7,'cols'=>100);
		
		if(!isset($value)){
			$this->_setValue($name,$attributes);
			if(isset($attributes['value'])){
				$value=$attributes['value'];
				unset($attributes['value']);
			}else $value='';//close the 'textarea' tag
		}
		
		if(!isset($attributes['id'])) $attributes['id']=$this->modelName != NULL ? $this->modelName.ucfirst($name) : $name;
		$label=isset($attributes['label']) ? $attributes['label'] : ($this->defaultLabel ? ($this->modelName != NULL ? _tF($this->modelName,$name) : $name) : false); unset($attributes['label']);
		
		$content='';
		if($label) $content.=HHtml::tag('label',array('for'=>$attributes['id']),$label);
		if(isset($attributes['between'])){
			$content.=$attributes['between'];
			unset($attributes['between']);
		}
		$content.=HHtml::tag('textarea',$attributes,$value);
		
		if($hasError=(!isset($containerAttributes['error']) || $containerAttributes['error']) && CValidation::hasError($key=($this->modelName === NULL ? $name : $this->name.'.'.$name)))
			$content.=HHtml::tag('div',array('class'=>'validation-advice'),isset($containerAttributes['error'])?$containerAttributes['error']:CValidation::getError($key));
		unset($containerAttributes['error']);
		
		return $this->_inputContainer($content,'textarea'.($hasError?' invalid':''),$containerAttributes);
	}
	
	/** @deprecated */
	public function input($name,$attributes=array(),$containerAttributes=array(),$largeSize=1){
		if(is_string($attributes)) $attributes=array('value'=>$attributes);
		
		$type='text';
		if($this->modelName !== NULL){
			if(!isset($attributes['name'])) $attributes['name']=$this->name.'['.$name.']';
			elseif($attributes['name']===false) unset($attributes['name']);
			
			$modelName=$this->modelName;
			if(isset($modelName::$__PROP_DEF[$name])){
				$propDef=$modelName::$__PROP_DEF[$name];
				switch($propDef['type']){
					case 'int': $type='number'; break;
					case 'string':
						switch($name){
							case 'pwd': case 'password':
								$type='password';
								$attributes['value']='';
								break;
							case 'email': case 'mail':
								$type='email';
								break;
							case 'url': case 'site_web': case 'website':
								$type='url';
								break;
						} 
						break;
				}
				if(isset($propDef['annotations']['Required'])) $attributes['required']=true;
				if(isset($propDef['annotations']['MinSize'])) $attributes['min']=$propDef['annotations']['MinSize'][0];
				if(isset($propDef['annotations']['MaxSize'])) $attributes['max']=$propDef['annotations']['MaxSize'][0];
				if(isset($propDef['annotations']['MaxLength']) && !isset($attributes['maxlength'])){
					$attributes['maxlength']=$propDef['annotations']['MaxLength'][0];
					if(!isset($attributes['size'])){
						if($attributes['maxlength'] < 10) $attributes['size']=11;
						elseif($attributes['maxlength'] <= 30) $attributes['size']=25;
						elseif($attributes['maxlength'] < 80) $attributes['size']=30;
						elseif($attributes['maxlength'] < 120) $attributes['size']=40;
						elseif($attributes['maxlength'] < 160) $attributes['size']=50;
						elseif($attributes['maxlength'] < 200) $attributes['size']=60;
						else $attributes['size']=70;
						$attributes['size']*=$largeSize;
					}
				}
			}
		}elseif(!isset($attributes['name'])) $attributes['name']=$name;
		elseif($attributes['name']===false) unset($attributes['name']);
		
		$this->_setValue($name,$attributes);
		
		if(!isset($attributes['type'])) $attributes['type']=$type;
		if(!isset($attributes['id'])) $attributes['id']=$this->modelName != NULL ? $this->modelName.ucfirst($name) : $name;
		$label=isset($attributes['label']) ? $attributes['label'] : ($this->defaultLabel ? ($this->modelName != NULL ? _tF($this->modelName,$name) : $name): false); unset($attributes['label']);
		
		if($label) $content=HHtml::tag('label',array('for'=>$attributes['id']),$label).' ';
		else $content='';
		if(isset($attributes['between'])){ $content.=$attributes['between']; unset($attributes['between']);}
		$content.=HHtml::tag('input',$attributes);
		
		if($hasError=(!isset($containerAttributes['error']) || $containerAttributes['error']) && CValidation::hasError($key=($this->modelName === NULL ? $name : $this->name.'.'.$name)))
			$content.=HHtml::tag('div',array('class'=>'validation-advice'),isset($containerAttributes['error'])?$containerAttributes['error']:CValidation::getError($key));
		unset($containerAttributes['error']);
		
		return $this->_inputContainer($content,'input '.($type!=='text'?'text ':'').$type.($hasError?' invalid':''),$containerAttributes);
	}

	/** @deprecated */
	public function hidden($name,$value=false,$attributes=array()){
		$attributes['type']='hidden';$attributes['name']=$this->modelName === NULL ? $name : $this->name.'['.$name.']';
		if($value!==false) $attributes['value']=$value;
		return HHtml::tag('input',$attributes);
	}
	
	/** @deprecated */
	public function submit($title=true,$options=array(),$containerAttributes=NULL){
		if($title===true) $title=_tC('Save');
		$options['value']=$title; $options['type']='submit';
		if(!isset($options['class'])) $options['class']='submit';
		$res=HHtml::tag('input',$options);
		if($containerAttributes === NULL && $this->tagContainer !== 'div') $containerAttributes=array();
		return $this->_inputContainer($res,'submit',$containerAttributes);
	}
	
	/** @deprecated */
	public function checkbox($name,$label=false,$attributes=array(),$containerAttributes=array()){
		$attributes['type']='checkbox'; if($name!==false) $attributes['name']=$this->modelName === NULL ? $name : $this->name.'['.$name.']';
		if(!isset($attributes['id'])) $attributes['id']=$this->modelName != NULL ? $this->modelName.ucfirst($name) : $name;
		$res=HHtml::tag('input',$attributes);
		if($label) $res.=HHtml::tag('label',array('for'=>$attributes['id']),$label);
		return $this->_inputContainer($res,'input checkbox',$containerAttributes);
	}
	
	/** @deprecated */
	public function range($name,$label=false,$attributes=array(),$containerAttributes=array()){
		$attributes['type']='range'; if($name!==false) $attributes['name']=$this->modelName === NULL ? $name : $this->name.'['.$name.']';
		if(!isset($attributes['id'])) $attributes['id']=$this->modelName != NULL ? $this->modelName.ucfirst($name) : $name;
		$res=HHtml::tag('input',$attributes);
		if($label) $res.=HHtml::tag('label',array('for'=>$attributes['id']),$label);
		return $this->_inputContainer($res,'input range',$containerAttributes);
	}
	
	/** @deprecated
	 * options['style'] : select, radio, checkbox
	 */
	public function select($name,$list,$options=NULL,$containerAttributes=array()){
		if($options===NULL){ $selected=NULL; $options=array(); }
		elseif(is_string($options)){ $selected=$options; $options=array(); }
		else{ $selected=isset($options['selected'])?$options['selected']:NULL; unset($options['selected']); }
		
		if($selected===NULL) $selected=$this->_getValue($name);
		
		if(isset($options['style'])){ $style=$options['style']; unset($options['style']); }
		else $style=NULL;
		if(isset($options['empty'])){ $empty=$options['empty']; unset($options['empty']); }
		else $empty=null;
		
		$id= isset($options['id']) ? $options['id'] : ($options['id']=$this->modelName != NULL ? $this->modelName.ucfirst($name) : $name);
		$label=isset($options['label']) ? $options['label'] : ($this->defaultLabel ? ($this->modelName != NULL ? _tF($this->modelName,$name) : $name) : false); unset($options['label']);
		if($label) $content=HHtml::tag('label',array('for'=>$options['id']),$label).' ';
		else $content='';
		
		
		if($this->modelName !== NULL){
			$modelName=$this->modelName;
			if(isset($modelName::$__PROP_DEF[$name])){
				$propDef=$modelName::$__PROP_DEF[$name];
				if(isset($propDef['annotations']['Required'])) $options['required']=true;
			}
		}
		
		switch($style){
			/*case 'checkbox':
				$class='checkboxes';()
				if($this->modelName !== NULL) $name=$this->name.'['.$name.']';
				foreach($list as $key=>$label){
					$attributes=array('id'=>$id.$key,'name'=>$name.'[]','type'=>'checkbox','value'=>$key);
					if($selected!==NULL && in_array($key,$selected)) $attributes['checked']=true;
					$content.=
						HHtml::tag('div',array('class'=>'checkbox'),
							HHtml::tag('input',$attributes).' '.HHtml::tag('label',array('for'=>$attributes['id']),$label)
						,false);
				}

				if(isset($options['title'])) $content=HHtml::tag('div',array('class'=>'title'),$options['title']).' '.$content;
				break;*/
			case 'radio':
				$class='input radio';
				$contentSelect='';
				if($empty !== null){
					$optionAttributes=array('value'=>'','type'=>'radio');
					if($selected==='') $optionAttributes['selected']=true;
					$contentSelect.=HHtml::tag('input',$optionAttributes,$empty);
				}
				$optionName=$this->_name($name);
				if(!empty($list)){
					if(is_object(current($list))){
						foreach($list as $model)
							$contentSelect.=self::__radio($optionName,$model->id(),$selected,$model->name());
					}else{
						foreach($list as $key=>$value)
							$contentSelect.=self::__radio($optionName,$key,$selected,$value);
					}
				}
				$content.=HHtml::tag('div',$options,$contentSelect,false);
				break;
			default:
				$class='input select';
				$contentSelect='';
				if($empty !== null){
					$optionAttributes=array('value'=>'');
					if($selected==='') $optionAttributes['selected']=true;
					$contentSelect.=HHtml::tag('option',$optionAttributes,$empty);
				}
				if(!empty($list)){
					if(is_object(current($list))){
						foreach($list as $model)
							$contentSelect.=HHtml::_option($model->id(),$model->name(),$selected);
					}else{
						foreach($list as $key=>$value)
							$contentSelect.=HHtml::_option($key,$value,$selected);
					}
				}
				$options['name']=$this->_name($name);
				$content.=HHtml::tag('select',$options,$contentSelect,false);
		}
		return $this->_inputContainer($content,$class,$containerAttributes);
	}
	
	/** @deprecated */
	public function selectHour($name,$containerAttributes=array(),$options=array()){
		return $this->select($name,/* EVAL range(0,23) /EVAL */0,$options,$containerAttributes);
	}
	/** @deprecated */
	public function selectHourMorning($name,$containerAttributes=array(),$options=array()){
		return $this->select($name,/* EVAL range(0,12) /EVAL */0,$options,$containerAttributes);
	}
	/** @deprecated */
	public function selectHourAfternoon($name,$containerAttributes=array(),$options=array()){
		return $this->select($name,/* EVAL array_combine(range(12,23),range(12,23)) /EVAL */0,$options,$containerAttributes);
	}
	/** @deprecated */
	public function selectMonth($name,$containerAttributes=array(),$options=array()){
		$options+=array('empty'=>'--');
		return $this->select($name,array('01'=>'01','02'=>'02','03'=>'03','04'=>'04','05'=>'05','06'=>'06','07'=>'07','08'=>'08','09'=>'09','10'=>'10','11'=>'11','12'=>'12'),$options,$containerAttributes);
	}
	
	/** @deprecated
	 * options['style'] : radio, checkbox
	 */
	public function multiple($name,$list,$options=NULL,$containerAttributes=array()){
		if($options===NULL){ $selected=NULL; $options=array(); }
		elseif(is_string($options)){ $selected=$options; $options=array(); }
		else{ $selected=isset($options['selected'])?$options['selected']:NULL; unset($options['selected']); }
		
		if($selected===NULL) $selected=$this->_getValue($name);
		
		if(isset($options['style'])){ $style=$options['style']; unset($options['style']); }
		else $style=NULL;
		if(isset($options['empty'])){ $empty=$options['empty']; unset($options['empty']); }
		else $empty=null;
		
		$id= isset($options['id']) ? $options['id'] : ($options['id']=$this->modelName != NULL ? $this->modelName.ucfirst($name) : $name);
		$label=isset($options['label']) ? $options['label'] : ($this->defaultLabel ? ($this->modelName != NULL ? _tF($this->modelName,$name) : $name) : false); unset($options['label']);
		if($label) $content=HHtml::tag('label',array('for'=>$options['id']),$label).' ';
		else $content='';
		
		switch($style){
			case 'checkbox':
				$class='checkboxes';
				if($this->modelName !== NULL) $name=$this->name.'['.$name.']';
				foreach($list as $key=>$label){
					$attributes=array('id'=>$id.$key,'name'=>$name.'[]','type'=>'checkbox','value'=>$key);
					if($selected!==NULL && in_array($key,$selected)) $attributes['checked']=true;
					$content.=
						HHtml::tag('div',array('class'=>'checkbox'),
							HHtml::tag('input',$attributes).' '.HHtml::tag('label',array('for'=>$attributes['id']),$label)
						,false);
				}

				if(isset($options['title'])) $content=HHtml::tag('div',array('class'=>'title'),$options['title']).' '.$content;
				break;
			default:
				$class='input select';
				$contentSelect='';
				if($empty !== null){
					$optionAttributes=array('value'=>'');
					if($selected==='') $optionAttributes['selected']=true;
					$contentSelect.=HHtml::tag('option',$optionAttributes,$empty);
				}
				if(!empty($list)){
					if(is_object(current($list))){
						foreach($list as $model)
							$contentSelect.=HHtml::_option($model->_getPkValue(),$model->name(),$selected);
					}else{
						foreach($list as $key=>$value)
							$contentSelect.=HHtml::_option($key,$value,$selected);
					}
				}
				$options['name']=$this->_name($name);
				$options['multiple']=true;
				$content.=HHtml::tag('select',$options,$contentSelect,false);
		}
		return $this->_inputContainer($content,$class,$containerAttributes);
	}
	
	/** @deprecated */
	public function yesOrNo($name,$attributes,$containerAttributes=array()){
		if(!isset($attributes['id'])) $attributes['id']=$this->modelName != NULL ? $this->modelName.ucfirst($name) : $name;
		$title=isset($attributes['title']) ? $attributes['title'] : ($this->defaultLabel ? ($this->modelName != NULL ? _tF($this->modelName,$name) : $name): false); unset($attributes['title']);
		$selected=$this->_getValue($name);
		if($this->modelName !== NULL) $name=$this->name.'['.$name.']';
		
		if($title) $content=HHtml::tag('div',array('class'=>'title'),$title).' ';
		else $content='';
		
		$yesAttributes=isset($attributes['yes'])?$attributes['yes']:array();
		$yesAttributes['id']=$attributes['id'].'1';		
		$noAttributes=isset($attributes['no'])?$attributes['no']:array();
		$noAttributes['id']=$attributes['id'].'0';
		
		if(isset($attributes['yesDiv']) && $attributes['yesDiv']){
			$yesAttributes['onclick']='$(\'#'.$attributes['id'].'Div\').show()';
			$noAttributes['onclick']='$(\'#'.$attributes['id'].'Div\').hide()';
			$containerAttributes['after']=HHtml::tag('div',array('id'=>$attributes['id'].'Div','class'=>'yesDiv','style'=>'display:none'),$attributes['yesDiv'],false)
				.HHtml::jsInline('if($("#'.$yesAttributes['id'].'").prop("checked")) $(\'#'.$attributes['id'].'Div\').show()');
		}
		
		$content.=self::_radio($name,1,$selected,$yesAttributes).HHtml::tag('label',array('for'=>$yesAttributes['id']),_tC('Yes'))
					.' '.self::_radio($name,0,!$selected,$noAttributes).HHtml::tag('label',array('for'=>$noAttributes['id']),_tC('No'));
		
		return $this->_inputContainer($content,'radio',$containerAttributes);
	}

	/** @deprecated */
	public static function __radio($name,$value,$selected,$label=null,$attributes=array()){
		$attributes['type']='radio';
		$attributes['name']=$name;
		$attributes['value']=$value;
		if(!isset($attributes['id'])) $attributes['id']=$name.$value;
		if($selected==$value) $attributes['checked']=true;
		return HHtml::tag('input',$attributes).($label===null?'':HHtml::tag('label',array('for'=>$attributes['id']),$label));
	}
	
	/** @deprecated */
	public function _radio($name,$value,$selected,$attributes=array()){
		$attributes['type']='radio';
		$attributes['name']=$name;
		$attributes['value']=$value;
		if($selected) $attributes['checked']=true;
		return HHtml::tag('input',$attributes);
	}
	
	/** @deprecated */
	public function stars($name,$nbStars=5,$attributes=array(),$containerAttributes=array()){
		$title=isset($attributes['title']) ? $attributes['title'] : ($this->defaultLabel ? ($this->modelName != NULL ? _tF($this->modelName,$name) : $name): false); unset($attributes['title']);
		$value=$this->_getValue($name);
		if($this->modelName !== NULL) $name=$this->name.'['.$name.']';
		
		if($title) $content=HHtml::tag('div',array('class'=>'title'),$title).' ';
		else $content='';
		
		for($i=1;$i<=$nbStars;$i++)
			$content.=self::_radio($name,$i,$value==$i,$attributes);
		
		return $this->_inputContainer($content,'radio stars',$containerAttributes);
	}
	
	private function _setValue(&$name,&$attributes){
		if(!isset($attributes['value'])){
			$value=$this->_getValue($name);
			if($value !== NULL) $attributes['value']=&$value;
		}
	}
	private function _getValue(&$name){
		$TAB=NULL;
		if($this->method=='post') $TAB=&$_POST;
		elseif($this->method=='get') $TAB=&$_GET;
		if($this->modelName === NULL) return isset($TAB[$name])? $TAB[$name] : NULL;
		else return isset($TAB[$this->name][$name]) ? $TAB[$this->name][$name] : NULL;
		return NULL;
	}
	
	private function _inputContainer($res,$defaultClass,$attributes){
		if($this->tagContainer && $attributes !== false){
			if(!isset($attributes['class'])) $attributes['class']=$defaultClass;
			if(isset($attributes['before'])){ $res=$attributes['before'].$res; unset($attributes['before']);}
			if(isset($attributes['after'])){ $res.=$attributes['after']; unset($attributes['after']);}
			$res=HHtml::tag($this->tagContainer,$attributes,$res,false);
		}
		return $res;
	}
}
