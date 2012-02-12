<?php
class HSitemap{
	private $_file;
	public function __construct($file='sitemap.xml'){
		$this->_file=gzopen(($file[0]==='/'?'':APP.'web/files/').$file.'.gz', 'w9');
		gzwrite($this->_file,'<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">
');
	}
	public function add($url,$options=array()){
		$content='<url><loc>'.HHtml::url($url,Config::$site_url,true).'</loc>';
		foreach($options as $key=>&$option) $content.=HHtml::tag($key,array(),$option);
		$content.='</url>'.PHP_EOL;
		gzwrite($this->_file,$content);
	}
	
	public function end(){
		gzwrite($this->_file,'</urlset>');
		gzclose($this->_file);
	}
}
