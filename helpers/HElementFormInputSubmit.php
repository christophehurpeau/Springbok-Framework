<?php
class HElementFormInputSubmit extends HElementFormContainable{
	public function __construct($form,$title){
		$this->form=$form;
		$this->error=false;
		if($title===true) $title=_tC('Save');
		$this->attributes['value']=$title;
		$this->attributes['type']=$this->attributes['class']='submit';
	}
	
	public function container(){
		$container=new HElementFormContainer($this->form,$this,'submit');
		return $container->noError();
	}
	
	public function toString(){
		return HHtml::tag('input',$this->attributes);
	}
	
}