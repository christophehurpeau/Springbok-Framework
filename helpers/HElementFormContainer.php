<?php
/**
 * An element container is a div, a td, or another tag which wraps an element inside
 * 
 * @see HElementFormContainable
 */
class HElementFormContainer extends HElement{
	private $form,$contained,
		$tagContainer,$before,$after,$error;
	
	/**
	 * @internal
	 * @param HElementForm
	 * @param HElement
	 * @param string
	 */
	public function __construct($form,$contained,$defaultClass){
		$this->form=$form; $this->contained=$contained;
		$this->tagContainer=$this->form->getTagContainer();
		$this->setClass($defaultClass);
	}
	
	/**
	 * Set the tag container
	 * 
	 * @param string
	 * @return HElementContainer|self
	 */
	public function tagContainer($tagContainer){ $this->tagContainer=$tagContainer; return $this; }
	
	/**
	 * Set the content before
	 * 
	 * @param string
	 * @return HElementContainer|self
	 */
	public function before($content){ $this->before=$content; return $this; }
	
	/**
	 * Set the content after
	 * 
	 * @param string
	 * @return HElementContainer|self
	 */
	public function after($content){ $this->after=$content; return $this; }
	
	/**
	 * Add content after
	 * 
	 * @param string
	 * @return HElementContainer|self
	 */
	public function addAfter($content){ $this->after.=$content; return $this; }
	
	/**
	 * Set the error div
	 * 
	 * @param string
	 * @return HElementContainer|self
	 */
	public function error($message){ $this->error=$message; return $this; }
	
	/**
	 * Removes the error div
	 * 
	 * @return HElementContainer|self
	 */
	public function noError(){ $this->error=false; return $this; }
	
	/**
	 * @return string
	 */
	public function __toString(){
		/*#if DEV */ if(Springbok::$inError) return '[HElementFormContainer]'; /*#/if*/
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