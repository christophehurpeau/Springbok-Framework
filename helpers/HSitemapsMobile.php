<?php
/**
 * Same as HSitemaps but with HSitemapMobile
 * 
 * @see HSitemaps
 */
class HSitemapsMobile extends HSitemaps{
	/**
	 * @param string
	 * @return HSitemapMobile
	 */
	protected function createNewSitemap($name){
		return new HSitemapMobile($name);
	}
	/**
	 * @param string|array
	 * @param array
	 * @param string
	 * @return void
	 */
	public function add($url,$options=array(),$entry='mobile'){
		return parent::add($url,$options,$entry);
	}
}