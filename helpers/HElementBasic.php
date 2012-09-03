<?php
class HElementBasic extends HElementWithContent{
	private $tag;
	public function __construct($tag){
		$this->tag=$tag;
	}
	
	public function __toString(){
		return HHtml::tag($this->tag,$this->attributes,$this->content,$this->contentEscape);
	}
}