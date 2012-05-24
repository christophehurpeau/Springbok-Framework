<?php
class HElementFormContainer extends HElement{
	private $form,$contained,
		$tagContainer,$before,$after;
	
	public function __construct(&$form,&$contained,&$defaultClass){
		$this->form=&$form; $this->contained=&$contained;
		$this->tagContainer=$this->form->getTagContainer();
		$this->attrClass($defaultClass);
	}
	
	public function &tagContainer($tagContainer){ $this->tagContainer=&$tagContainer; return $this; }
	public function &before($content){ $this->before=&$content; return $this; }
	public function &after($content){ $this->after=&$content; return $this; }
	
	
	public function __toString(){
		return HHtml::tag($this->tagContainer,$this->attributes,
				($this->before!==null ? $this->before : '')
				.$this->contained->toString()
				.($this->after!==null ? $this->after : '')
			,false);
	}
}