<?php
class HSitemapsMobile extends HSitemaps{
	protected function createNewSitemap($name){
		return new HSitemapMobile($name);
	}
	public function add($url,$options=array(),$entry='mobile'){
		return parent::add($url,$options,$entry);
	}
}