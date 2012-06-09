<?php
class HElementFormContainer extends HElement{
	private $form,$contained,
		$tagContainer,$before,$after,$error;
	
	public function __construct(&$form,&$contained,$defaultClass){
		$this->form=&$form; $this->contained=&$contained;
		$this->tagContainer=$this->form->getTagContainer();
		$this->attrClass($defaultClass);
	}
	
	public function &tagContainer($tagContainer){ $this->tagContainer=&$tagContainer; return $this; }
	public function &before($content){ $this->before=&$content; return $this; }
	public function &after($content){ $this->after=&$content; return $this; }
	public function &error($message){ $this->error=&$message; return $this; }
	public function &noError(){ $this->error=false; return $this; }
	
	public function __toString(){
		if($hasError=$this->error!==false && CValidation::hasError($key=($this->form->modelName === NULL ? $this->contained->name : $this->form->name.'.'.$this->contained->name)))
			$this->addClass('invalid');
		return HHtml::tag($this->tagContainer,$this->attributes,
				($this->before!==null ? $this->before : '')
				.$this->contained->toString()
				.($hasError ? HHtml::tag('div',array('class'=>'validation-advice'),$this->error===null?CValidation::getError($key):$this->error) : '')
				.($this->after!==null ? $this->after : '')
			,false);
	}
}