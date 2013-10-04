<?php
/**
 * Represents a Dom Element
 */
abstract class HElement{
	/**
	 * List of attributes
	 * 
	 * @var array
	 */
	protected $attributes=array();
	
	/**
	 * Set several attributes
	 * 
	 * @param array
	 * @return HElement|self
	 */
	public function attrs($attributes){ $this->attributes=$attributes+$this->attributes; return $this; }
	
	/**
	 * Set one attribute
	 * 
	 * @param string
	 * @param string
	 * @return HElement|self
	 */
	public function attr($attrName,$value){ $this->attributes[$attrName]=$value; return $this; }
	
	/**
	 * Set one attribute, prefixed by "data-"
	 * 
	 * @param string
	 * @param string
	 * @return HElement|self
	 */
	public function dataattr($attrName,$value){ $this->attributes['data-'.$attrName]=$value; return $this; }
	
	/**
	 * Set the id attribute
	 * 
	 * @param string
	 * @return HElement|self
	 */
	public function id($id){ $this->attributes['id']=$id; return $this; }
	
	/**
	 * Set the title attribute
	 * 
	 * @param string
	 * @return HElement|self
	 */
	public function title($title){ $this->attributes['title']=$title; return $this; }
	
	/**
	 * Set the rel attribute
	 * 
	 * @param string
	 * @return HElement|self
	 */
	public function rel($rel){ $this->attributes['rel']=$rel; return $this; }
	
	/**
	 * Set the class attribute
	 * 
	 * Prefer setClass.
	 * 
	 * @param string
	 * @return HElement|self
	 * 
	 * @see setClass
	 */
	public function attrClass($class){ $this->attributes['class']=$class; return $this; }
	
	/**
	 * Set the class attribute
	 * 
	 * @param string
	 * @return HElement|self
	 */
	public function setClass($class){ $this->attributes['class']=$class; return $this; }
	
	/**
	 * Add a class or several classes to the already existing class attribute
	 * 
	 * @param string
	 * @return HElement|self
	 */
	public function addClass($class){ $this->attributes['class'].=' '.$class; return $this; }
	
	/**
	 * Set the style attribute
	 * 
	 * @param string
	 * @return HElement|self
	 */
	public function style($style){ $this->attributes['style']=$style; return $this; }
	
	/**
	 * Set the onclick attribute
	 * 
	 * @param string
	 * @return HElement|self
	 */
	public function onClick($onClick){ $this->attributes['onclick']=$onClick; return $this; }
	
	/**
	 * Unset an attribute
	 * 
	 * Prefer rmAttr.
	 * 
	 * @param string
	 * @return HElement|self
	 */
	public function unsetAttr($attrName){ unset($this->attributes[$attrName]); return $this; }
	
	/**
	 * Unset an attribute
	 * 
	 * @param string
	 * @return HElement|self
	 */
	public function rmAttr($attrName){ unset($this->attributes[$attrName]); return $this; }
	
	/**
	 * Return the value of an existing attribute
	 * 
	 * @param string
	 * @return string
	 */
	public function getAttr($attrName){ return $this->attributes[$attrName]; }
	
	/**
	 * Return if an attribute exists in this element
	 * 
	 * @param string
	 * @return bool
	 */
	public function hasAttr($attrName){ return isset($this->attributes[$attrName]); }
	
	/**
	 * Returns the attributes in the html string form
	 */
	protected function _attributes(){ return HHtml::_attributes($this->attributes); }
	
	/**
	 * Create a new Basic Element
	 * 
	 * @param string
	 * @return HElementBasic
	 */
	public static function create($tag){ return new HElementBasic($tag); }
	
	/**
	 * Create a new Button
	 * 
	 * @return HElementButton
	 */
	public static function button(){ return new HElementButton(); }
	
	/**
	 * Create a new link element, with its text content
	 * 
	 * @param string
	 * @return HElementLink
	 */
	public static function link($text){ $e=new HElementLink(); return $e->content($text); }
	
	/**
	 * Create a new link element, with its text content in html
	 * 
	 * @param string
	 * @return HElementLink
	 */
	public static function linkHtml($html){ $e=new HElementLink(); return $e->contentHtml($text); }
	
	/**
	 * Create a new icon link
	 * 
	 * @param string
	 * @param string
	 * @return HElementLink
	 */
	public static function iconLink($icon,$text){ $e=new HElementLink(); return $e->icon($icon,$text); }
	
	/**
	 * Create a new icon link
	 * 
	 * @param string
	 * @param string
	 * @return HElementLink
	 */
	public static function iconLinkHtml($icon,$html){ $e=new HElementLink(); return $e->iconHtml($icon,$html); }
}