<?php
/**
 * Sitemap helper
 * 
 * @see HSitemaps
 */
class HSitemap{
	private $_file,$_fileName;
	
	/**
	 * @param string
	 * @param array
	 */
	public function __construct($file='sitemap.xml',$extensions=array()){
		$after='';
		foreach($extensions as $ext){
			if($ext==='image') $after.=' xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"';
			elseif($ext==='video') $after.=' xmlns:video="http://www.google.com/schemas/sitemap-video/1.1"';
			elseif($ext==='mobile') $after.=' xmlns:mobile="http://www.google.com/schemas/sitemap-mobile/1.0"';
			elseif($ext==='news') $after.=' xmlns:news="http://www.google.com/schemas/sitemap-news/0.9"';
			else $after.=' '.$ext;
		}
		$this->_file=/*#if DEV */fopen/*#/if*//*#if false*/&&/*#/if*//*#if PROD*/gzopen/*#/if*/(($this->_fileName=($file[0]==='/'?'':APP.'web/files/').$file/*#if DEV */),'w'/*#/if*//*#if PROD*/.'.gz').'.tmp','w9'/*#/if*/);
		/*#if DEV */fwrite/*#/if*//*#if false*/&&/*#/if*//*#if PROD*/gzwrite/*#/if*/($this->_file,'<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"'
	.' xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd"'
	.' xmlns:xhtml="http://www.w3.org/1999/xhtml"'.$after.'>'
);
	}

	/**
	 * Add a link
	 * 
	 * @param string
	 * @param array
	 * @param string
	 * @return void
	 */
	public function add($url,$options=array(),$entry='index'){
		$content='<url><loc>'.HHtml::urlEscape($url,$entry,true,false,false).'</loc>';
		foreach($options as $key=>$optionContent){
			/*if($key==='altMobile'){
				$key='xhtml:link';
				$optionsOption=array('rel'=>'alternate','media'=>'only screen and (max-width: 640px)','href'=>HHtml::url($optionContent,'mobile',true));
				$optionContent=null;
			}else{*/
				if(is_array($optionContent)){ $optionsOption=$optionsContent[0]; $optionsContent=$optionsContent[1]; }
				else $optionsOption=array();
			/*}*/
			$content.=HHtml::tag($key,$optionsOption,$optionContent);
		}
		$content.='</url>'.PHP_EOL;
		/*#if DEV */fwrite/*#/if*//*#if false*/&&/*#/if*//*#if PROD*/gzwrite/*#/if*/($this->_file,$content);
	}
	
	/**
	 * End and close the sitemap
	 */
	public function end(){
		/*#if DEV */fwrite/*#/if*//*#if false*/&&/*#/if*//*#if PROD*/gzwrite/*#/if*/($this->_file,'</urlset>');
		/*#if DEV */fclose/*#/if*//*#if false*/&&/*#/if*//*#if PROD*/gzclose/*#/if*/($this->_file);
		/*#if PROD*/rename($this->_fileName.'.tmp',$this->_fileName);/*#/if*/
	}
}
