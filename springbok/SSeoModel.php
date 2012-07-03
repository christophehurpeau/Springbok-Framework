<?php
class SSeoModel extends SSqlModel{
	
	public function auto_slug(){ return HString::slug($this->name); }
	
	public function auto_meta_title(){ return $this->name; }
	public function metaTitle(){ return empty($this->meta_title) ? $this->auto_meta_title() : $this->meta_title; }
	public function metaDescr(){ return empty($this->meta_descr) ? $this->auto_meta_descr() : $this->meta_descr; }
	public function metaKeywords(){ return empty($this->meta_keywords) ? $this->auto_meta_keywords() : $this->meta_keywords ; }
	
	public function beforeSave(){
		if(!empty($this->name) && empty($this->slug)) $this->slug=$this->auto_slug();
		return true;
	}
	
}
