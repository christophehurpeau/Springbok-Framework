<?php
trait BSeo{
	public function metaTitle(){ return empty($this->meta_title) ? $this->auto_meta_title() : $this->meta_title; }
	public function metaDescr(){ return empty($this->meta_descr) ? $this->auto_meta_descr() : $this->meta_descr; }
	public function metaKeywords(){ return empty($this->meta_keywords) ? $this->auto_meta_keywords() : $this->meta_keywords ; }
}
