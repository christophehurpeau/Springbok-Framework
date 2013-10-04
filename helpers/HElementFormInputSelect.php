<?php
/**
 * A selectable input : <select> with <option>, or <input type="radio">
 * 
 * @see HElementForm::select
 */
class HElementFormInputSelect extends HElementFormContainable{
	private $list,$selected,$style='select',$empty,$addBr=false,$autoAutocomplete=false;
	
	/**
	 * @internal
	 * @param HElementForm
	 * @param string
	 * @param array
	 * @param mixed
	 */
	public function __construct($form,$name,$list,$selected){
		parent::__construct($form,$name);
		if($list===null) $list=call_user_func(array($this->form->modelName,$this->name.'List'));
		if($selected===null) $selected=$this->form->_getValue($name);
		
		$this->list=$list;
		$this->selected=$selected;
		
		
		if($this->form->modelName !== null){
			$modelName=$this->form->modelName;
			if(isset($modelName::$__PROP_DEF[$name])){
				$propDef=$modelName::$__PROP_DEF[$name];
				if(isset($propDef['annotations']['Required'])) $this->attributes['required']=true;
				else $this->empty = '';
			}
		}
		$this->_setAttrId();
	}
	
	/**
	 * Set the style to <input type="radio"> instead of <select>
	 * 
	 * @return HElementFormInputSelect|self
	 */
	public function radio(){ $this->style='radio'; return $this; }
	
	/**
	 * Set the empty value
	 * 
	 * @return HElementFormInputSelect|self
	 */
	public function emptyValue($value){ $this->empty=$value; return $this; }
	
	/**
	 * Adds a br between elements
	 * 
	 * @return HElementFormInputSelect|self
	 */
	public function addBr(){ $this->addBr=true; return $this; }
	
	/**
	 * Add a javascript autocomplete with datalist
	 * 
	 * @see render_autocompleteSelect()
	 */
	public function autoAutocomplete(){ $this->autoAutocomplete=true; return $this; }
	
	/**
	 * Returns the container
	 * 
	 * @return HElementFormContainer
	 */
	public function container(){ return new HElementFormContainer($this->form,$this,'input '.$this->style); }
	
	/**
	 * @return string
	 */
	public function toString(){
		return $this->_labelToString().$this->{'render_'.$this->style}();
	}
	
	/**
	 * Render radios list
	 * 
	 * @return string
	 */
	public function render_radio(){
		$contentSelect='';
		if($this->empty !== null){
			$optionAttributes=array('value'=>'','type'=>'radio');
			if($this->selected==='') $optionAttributes['selected']=true;
			$contentSelect.=HHtml::tag('input',$optionAttributes,$this->empty);
		}
		$optionName=$this->_name($this->name);
		if(!empty($this->list)){
			$end=($this->addBr===true) ? '<br/>' :'' ;
			if(is_object(current($this->list))){
				foreach($this->list as $model)
					$contentSelect.=self::__radio($optionName,$model->id(),$this->selected,$model->name()).$end;
			}else{
				foreach($this->list as $key=>$value)
					$contentSelect.=self::__radio($optionName,$key,$this->selected,$value).$end;
			}
		}
		return $contentSelect;
	}
	
	/**
	 * render <select><option></option>...</select>
	 * 
	 * @return string
	 */
	public function render_select(){
		if(($listNotEmpty=!empty($this->list)) && $this->autoAutocomplete===true && count($this->list)>15)
			return $this->render_autocompleteSelect();
		$contentSelect=''; $options=$this->attributes;
		if($this->empty !== null){
			$optionAttributes=array('value'=>'');
			if($this->selected==='') $optionAttributes['selected']=true;
			$contentSelect.=HHtml::tag('option',$optionAttributes,$this->empty);
		}
		if($listNotEmpty){
			if(is_object(current($this->list))){
				foreach($this->list as $model)
					$contentSelect.=HHtml::_option($model->_getPkValue(),$model->name(),$this->selected);
			}else{
				foreach($this->list as $key=>$value)
					$contentSelect.=HHtml::_option($key,$value,$this->selected);
			}
		}
		$options['name']=$this->_name($this->name);
		return 	HHtml::tag('select',$options,$contentSelect,false);
	}
	
	/**
	 * Render <datalist></datalist><input type="text"><input type="hidden">
	 * 
	 * @return string
	 */
	public function render_autocompleteSelect(){
		$contentDatalist=''; $options=$this->attributes; $selectedValue=null;
		if($this->empty !== null){
			$optionAttributes=array('value'=>$this->empty);
			if(empty($this->selected) && $this->selected!=='0') $selectedValue=$this->empty;
			$contentDatalist.=HHtml::tag('option',$optionAttributes);
		}
		if(is_object(current($this->list))){
			foreach($this->list as $model){
				$contentDatalist.=HHtml::tag('option',array('data-key'=>$key=$model->id(),'value'=>$value=$model->name()));
				if($key===$this->selected) $selectedValue=$value;
			}
		}else{
			foreach($this->list as $key=>$value)
				$contentDatalist.=HHtml::tag('option',array('data-key'=>$key,'value'=>$value));
			if(isset($this->list[$this->selected])) $selectedValue=$this->list[$this->selected];
		}
		$options['list']=$options['id'].'_datalist';
		if(!empty($selectedValue)) $options['value']=$selectedValue;
		if($this->empty===null) $options['required']=true;
		return HHtml::tag('datalist',array('id'=>$options['list']),$contentDatalist,false)
			.HHtml::tag('input',$options)
			.HHtml::tag('input',array('id'=>$options['id'].'_hidden',
				'type'=>'hidden','name'=>$this->_name($this->name),'value'=>$this->selected));
	}
	
	/**
	 * Render a radio input
	 * 
	 * @param string
	 * @param string
	 * @param mixed
	 * @param null|string
	 * @param array
	 * @return string
	 */
	public static function __radio($name,$value,$selected,$label=null,$attributes=array()){
		$attributes['type']='radio';
		$attributes['name']=$name;
		$attributes['value']=$value;
		if(!isset($attributes['id'])) $attributes['id']=str_replace(array('[',']'),'-',$name).$value;
		if($value==='00'){ if($selected=='0') $attributes['checked']=true; }
		elseif($selected!==null && $selected==$value) $attributes['checked']=true;
		return HHtml::tag('input',$attributes).($label===null?'':HHtml::tag('label',array('for'=>$attributes['id'],'class'=>'radioLabel'),$label));
	}
}