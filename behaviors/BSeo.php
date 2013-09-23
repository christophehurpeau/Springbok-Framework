<?php
/**
 * Add SEO fields and methods.
 * 
 * @property string $meta_title
 * @property string $meta_descr
 * @property string $meta_keywords
 */
trait BSeo{
	public function auto_meta_title(){ return $this->name; }
	
	public function metaTitle(){ return empty($this->meta_title) ? $this->auto_meta_title() : $this->meta_title; }
	public function metaDescr(){ return empty($this->meta_descr) ? $this->auto_meta_descr() : $this->meta_descr; }
	public function metaKeywords(){ return empty($this->meta_keywords) ? $this->auto_meta_keywords() : $this->meta_keywords ; }
	
	public function checkMetasSet(){
		foreach(array('meta_title','meta_descr','meta_keywords') as $metaName)
			if(empty($this->$metaName)) $this->$metaName=null;
	}
	
	protected function _normalizeMetas(){
		foreach(array('meta_title','meta_descr','meta_keywords') as $metaName)
			if(!empty($this->$metaName)) $this->$metaName=preg_replace('/[\t ]+/u',' ',str_replace(' ,',', ',trim($this->$metaName,",; \t\n\r\0\x0B")));
		return true;
	}
}
