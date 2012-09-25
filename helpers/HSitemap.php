<?php
class HSitemap{
	private $_file;
	public function __construct($file='sitemap.xml'){
		$this->_file=/* DEV */fopen/* /DEV *//* HIDE */&&/* /HIDE *//* PROD */gzopen/* /PROD */(($file[0]==='/'?'':APP.'web/files/').$file/* DEV */,'w'/* /DEV *//* PROD */.'.gz','w9'/* /PROD */);
		/* DEV */fwrite/* /DEV *//* HIDE */&&/* /HIDE *//* PROD */gzwrite/* /PROD */($this->_file,'<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns:xhtml="http://www.w3.org/1999/xhtml">
');
	}
	public function add($url,$options=array(),$entry='index'){
		$content='<url><loc>'.HHtml::urlEscape($url,$entry,true).'</loc>';
		foreach($options as $key=>$optionContent){
			if($key==='altMobile'){
				$key='xhtml:link';
				$optionsOption=array('rel'=>'alternate','media'=>'only screen and (max-width: 640px)','href'=>HHtml::url($optionContent,'mobile',true));
				$optionContent=null;
			}else{
				if(is_array($optionContent)){ $optionsOption=$optionsContent[0]; $optionsContent=$optionsContent[1]; }
				else $optionsOption=array();
			}
			$content.=HHtml::tag($key,$optionsOption,$optionContent);
		}
		$content.='</url>'.PHP_EOL;
		/* DEV */fwrite/* /DEV *//* HIDE */&&/* /HIDE *//* PROD */gzwrite/* /PROD */($this->_file,$content);
	}
	
	public function end(){
		/* DEV */fwrite/* /DEV *//* HIDE */&&/* /HIDE *//* PROD */gzwrite/* /PROD */($this->_file,'</urlset>');
		/* DEV */fclose/* /DEV *//* HIDE */&&/* /HIDE *//* PROD */gzclose/* /PROD */($this->_file);
	}
}
