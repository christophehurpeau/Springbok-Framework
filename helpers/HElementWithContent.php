<?php
/**
 * Represents an element with content
 */
abstract class HElementWithContent extends HElement{
	protected $content,$contentEscape=true;
	
	/**
	 * Set the content
	 * 
	 * @param string
	 * @return HElementWithContent|self
	 */
	public function content($content){ $this->content=$content; return $this; }
	
	/**
	 * Set the html content
	 * 
	 * @param string
	 * @return HElementWithContent|self
	 */
	public function contentHtml($content){ $this->content=$content; $this->contentEscape=false; return $this; }
	
	/**
	 * @deprecated
	 * @return HElementWithContent|self
	 */
	public function noContentEscape(){ $this->contentEscape=false; return $this; }
	
	/**
	 * @return string
	 */
	protected function _render($tag){
		return HHtml::tag($tag,$this->attributes,$this->content,$this->contentEscape);
	}
}