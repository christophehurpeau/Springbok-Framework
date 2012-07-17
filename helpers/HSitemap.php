<?php
class HSitemap{
	private $_file;
	public function __construct($file='sitemap.xml'){
		$this->_file=/* DEV */fopen/* /DEV *//* HIDE */&&/* /HIDE *//* PROD */gzopen/* /PROD */(($file[0]==='/'?'':APP.'web/files/').$file/* DEV */,'w'/* /DEV *//* PROD */.'.gz','w9'/* /PROD */);
		/* DEV */fwrite/* /DEV *//* HIDE */&&/* /HIDE *//* PROD */gzwrite/* /PROD */($this->_file,'<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">
');
	}
	public function add($url,$options=array(),$entry='index'){
		$content='<url><loc>'.HHtml::urlEscape($url,$entry,true).'</loc>';
		foreach($options as $key=>&$option) $content.=HHtml::tag($key,array(),$option);
		$content.='</url>'.PHP_EOL;
		/* DEV */fwrite/* /DEV *//* HIDE */&&/* /HIDE *//* PROD */gzwrite/* /PROD */($this->_file,$content);
	}
	
	public function end(){
		/* DEV */fwrite/* /DEV *//* HIDE */&&/* /HIDE *//* PROD */gzwrite/* /PROD */($this->_file,'</urlset>');
		/* DEV */fclose/* /DEV *//* HIDE */&&/* /HIDE *//* PROD */gzclose/* /PROD */($this->_file);
	}
}
