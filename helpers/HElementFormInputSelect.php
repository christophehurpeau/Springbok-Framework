<?php
class HElementFormInputSelect extends HElementFormContainable{
	private $list,$selected,$style='select',$empty;
	public function __construct($form,$name,$list,$selected){
		parent::__construct($form,$name);
		if($list===null) $list=call_user_func(array($this->form->modelName,$this->name.'List'));
		if($selected===null) $selected=$this->form->_getValue($name);
		
		$this->list=$list;
		$this->selected=$selected;
		
		$this->_setAttrId();
	}
	
	public function radio(){ $this->style='radio'; return $this; }
	public function emptyValue($value){ $this->empty=$value; return $this; }
	
	public function container(){ return new HElementFormContainer($this->form,$this,'input '.$this->type.' '.$this->style); }
	
	public function toString(){
		return $this->_labelToString().$this->{'render_'.$this->style}();
	}
	
	public function render_radio(){
		$contentSelect='';
		if($this->empty !== null){
			$optionAttributes=array('value'=>'','type'=>'radio');
			if($this->selected==='') $optionAttributes['selected']=true;
			$contentSelect.=HHtml::tag('input',$optionAttributes,$this->empty);
		}
		$optionName=$this->_name($this->name);
		if(!empty($this->list)){
			if(is_object(current($this->list))){
				foreach($this->list as $model)
					$contentSelect.=self::__radio($optionName,$model->_getPkValue(),$this->selected,$model->name());
			}else{
				foreach($this->list as $key=>$value)
					$contentSelect.=self::__radio($optionName,$key,$this->selected,$value);
			}
		}
		return $contentSelect;
	}
	
	public function render_select(){
		$contentSelect=''; $options=array();
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
		return 	HHtml::tag('select',$options,$contentSelect,false);
	}
	
	
	public static function __radio($name,$value,$selected,$label=null,$attributes=array()){
		$attributes['type']='radio';
		$attributes['name']=$name;
		$attributes['value']=$value;
		if(!isset($attributes['id'])) $attributes['id']=$name.$value;
		if($selected==$value) $attributes['checked']=true;
		return HHtml::tag('input',$attributes).($label===null?'':HHtml::tag('label',array('for'=>$attributes['id']),$label));
	}
}