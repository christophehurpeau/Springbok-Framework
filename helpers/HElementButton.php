<?php
class HElementButton extends HElementWithContent{
	public function jsSubmit($formId=null){
		$this->attributes['onclick']=($formId===null?'$(\'#'.$formId.'\')':'$(this).closest(\'form\')').'.submit()';
		return $this;
	}
	public function __toString(){ return $this->_render('button'); }
}