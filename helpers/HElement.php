<?php
abstract class HElement{
	protected $attributes=array();
	
	public function &attr($attrName,$value){ $this->attributes[$attrName]=&$value; return $this; }
	public function &attrId($id){ $this->attributes['id']=&$id; return $this; }
	public function &attrClass($class){ $this->attributes['class']=&$class; return $this; }
	public function &attrOnClick($onClick){ $this->attributes['onclick']=&$onClick; return $this; }
	public function &unsetAttr($attrName){ unset($this->attributes[$attrName]); return $this; }
	
	protected function _attributes(){ return HHtml::_attributes($this->attributes); }
}