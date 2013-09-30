<?php
/**
 * A Html Dom button
 * 
 * @see HElement::button
 */
class HElementButton extends HElementWithContent{
	/**
	 * Submit the form each time the button is clicked
	 * 
	 * @param string
	 * @return HElementButton|self
	 */
	public function jsSubmit($formId=null){
		$this->attributes['onclick']=($formId===null?'$(\'#'.$formId.'\')':'$(this).closest(\'form\')').'.submit()';
		return $this;
	}
	
	/**
	 * @return string
	 */
	public function __toString(){ return $this->_render('button'); }
}