<?php
/**
 * Mobile Sitemap helper
 */
class HSitemapMobile extends HSitemap{
	/**
	 * @param string
	 * @param array
	 */
	public function __construct($file='sitemap-mobile.xml',$extensions=array()){
		$extensions[]='mobile';
		parent::__construct($file,$extensions);
	}
	
	/**
	 * @param string|array
	 * @param string
	 * @param string
	 * @return void
	 */
	public function add($url,$options=array(),$entry='mobile'){
		$options['mobile:mobile']=null;
		parent::add($url,$options,$entry);
	}
}