<?php
class HSitemapIndex{
	private $_file;
	public function __construct($file='sitemap-index.xml'){
		$this->_file=gzopen(($file[0]==='/'?'':APP.'web/files/').$file.'.gz', 'w9');
		gzwrite($this->_file,'<?xml version="1.0" encoding="UTF-8"?>
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
');
	}
	public function add($url,$options=array()){
		$content='<sitemap><loc>'.HHtml::url($url,Config::$site_url,true).'</loc>';
		foreach($options as $key=>&$option) $content.=HHtml::tag($key,array(),$option);
		$content.='</sitemap>'.PHP_EOL;
		gzwrite($this->_file,$content);
	}
	
	public function end(){
		gzwrite($this->_file,'</sitemapindex>');
		gzclose($this->_file);
	}
}