<?php
class HElementFormInputSelect extends HElementFormContainable{
	private $list,$seleted,$style='select',$empty;
	public function __construct($form,$name,$list,$selected){
		parent::__construct($form,$name);
		if($list===null) $list=call_user_func(array($this->form->modelName,$this->name.'List'));
		if($selected===null) $selected=$this->form->_getValue($name);
		
		$this->list=$list;
		$this->seleted=$selected;
		
		$this->_setAttrId();
	}
	
	public function radio(){ $this->style='radio'; return $this; }
	public function emptyValue($value){ $this->empty=$value; return $this; }
	
	public function container(){ return new HElementFormContainer($this->form,$this,'input select '.$this->style); }
	
	public function toString(){
		$this->attributes['type']='checkbox';
		return $this->_labelToString().$this->{'render_'.$this->style}();
	}
	
	public function render_radio(){
		
	}
	
	public function render_select(){
		$contentSelect='';
		if($this->empty !== null){
			$optionAttributes=array('value'=>'','type'=>'radio');
			if($this->selected==='') $optionAttributes['selected']=true;
			$contentSelect.=HHtml::tag('input',$optionAttributes,$this->empty);
		}
		$optionName=$this->_name($this->name);
		if(!empty($list)){
			if(is_object(current($list))){
				foreach($list as $model)
					$contentSelect.=self::__radio($optionName,$model->_getPkValue(),$selected,$model->name());
			}else{
				foreach($list as $key=>$value)
					$contentSelect.=self::__radio($optionName,$key,$selected,$value);
			}
		}
		$content.=HHtml::tag('div',$options,$contentSelect,false);
	}
}