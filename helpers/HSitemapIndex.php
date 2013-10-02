<?php
/**
 * Sitemap Index helper
 * 
 * @see HSitemap
 */
class HSitemapIndex{
	private $_file;
	
	/**
	 * @param string
	 */
	public function __construct($file='sitemap-index.xml'){
		$this->_file=gzopen(($file[0]==='/'?'':APP.'web/files/').$file.'.gz', 'w9');
		gzwrite($this->_file,'<?xml version="1.0" encoding="UTF-8"?>
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
');
	}
	
	/**
	 * @param string
	 * @param array
	 * @param string
	 * @return void
	 */
	public function add($url,$options=array(),$entry='index'){
		$content='<sitemap><loc>'.HHtml::urlEscape('/',$entry,true).substr($url,1).'</loc>';
		foreach($options as $key=>&$option) $content.=HHtml::tag($key,array(),$option);
		$content.='</sitemap>'.PHP_EOL;
		gzwrite($this->_file,$content);
	}
	
	/**
	 * @return void
	 */
	public function end(){
		gzwrite($this->_file,'</sitemapindex>');
		gzclose($this->_file);
	}
}