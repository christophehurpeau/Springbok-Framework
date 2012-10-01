<?php
class HSitemaps{
	private $prefix,$count=0,$sitemap,$sitemapNum=2;
	public function __construct($prefix=''){
		$this->prefix=$prefix;
		$this->sitemap=new HSitemap('sitemaps/'.$prefix.'1.xml');
	}
	public function add($url,$options=array(),$entry='index'){
		if(++$this->count >49995){
			$this->count=0;
			$this->sitemap->end();
			unset($this->sitemap);
			$this->sitemap=new HSitemap('sitemaps/'.$this->prefix.($this->sitemapNum++).'.xml');
		}
		$this->sitemap->add($url,$options,$entry);
	}
	
	public function end(){
		$this->sitemap->end();
		
		$sitemap=new HSitemapIndex($this->prefix.'sitemaps.xml');
		$lastMod=date('c');
		for($i=1;$i<$this->sitemapNum;$i++)
			$sitemap->add('/web/files/sitemaps/'.$this->prefix.$i.'.xml.gz',array('lastmod'=>$lastMod));
		$sitemap->end();
	}
}
