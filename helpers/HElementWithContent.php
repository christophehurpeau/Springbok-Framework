<?php
class HElementWithContent extends HElement{
	protected $content,$contentEscape=true;
	public function content($content){ $this->content=$content; return $this; }
	public function contentHtml($content){ $this->content=$content; $this->contentEscape=false; return $this; }
	
	protected function _render($tag){
		return HHtml::tag($tag,$this->attributes,$this->content,$this->contentEscape);
	}
}