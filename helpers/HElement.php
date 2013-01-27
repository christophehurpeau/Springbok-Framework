<?php
abstract class HElement{
	protected $attributes=array();
	
	public function attrs($attributes){ $this->attributes=$attributes+$this->attributes; return $this; }
	public function attr($attrName,$value){ $this->attributes[$attrName]=$value; return $this; }
	public function id($id){ $this->attributes['id']=$id; return $this; }
	public function attrClass($class){ $this->attributes['class']=$class; return $this; }
	public function addClass($class){ $this->attributes['class'].=' '.$class; return $this; }
	public function style($style){ $this->attributes['style']=$style; return $this; }
	public function onClick($onClick){ $this->attributes['onclick']=$onClick; return $this; }
	public function unsetAttr($attrName){ unset($this->attributes[$attrName]); return $this; }
	public function rmAttr($attrName){ unset($this->attributes[$attrName]); return $this; }
	public function getAttr($attrName){ return $this->attributes[$attrName]; }
	public function hasAttr($attrName){ return isset($this->attributes[$attrName]); }
	
	protected function _attributes(){ return HHtml::_attributes($this->attributes); }
	
	public static function create($tag){ return new HElementBasic($tag); }
	public static function button(){ return new HElementButton(); }
}