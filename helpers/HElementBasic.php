<?php
/**
 * A basic Dom Element
 * 
 * @see HElement::create
 */
class HElementBasic extends HElementWithContent{
	private $tag;
	
	/**
	 * @internal
	 * 
	 * @see HElement::create
	 */
	public function __construct($tag){
		$this->tag=$tag;
	}
	
	/**
	 * @return string
	 */
	public function __toString(){
		return HHtml::tag($this->tag,$this->attributes,$this->content,$this->contentEscape);
	}
}