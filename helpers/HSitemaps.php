<?php
/**
 * Sitemaps helper : a sitemap is limited in the number of links. This helps create a sitemap index and internally creates a new sitemap when needed
 * 
 * 
 * <code>
 * $sitemap=new HSitemaps('posts_');
		
		$tags=PostsTag::QValues()->setFields(false)->with('MainTerm','slug');
		foreach($tags as $tag) $sitemap->add('/posts/tag/'.$tag,array('priority'=>'0.6'));
		
		
		$posts=Post::QAll()->fields('id,published')->withParent('name,slug,updated')
			->where(array('status'=>Post::PUBLISHED));
		foreach($posts as $post)
			$sitemap->add($post->link(),array('priority'=>'0.9','changefreq'=>'yearly',
				'lastmod'=>date('c',strtotime($post->updated===null?$post->published:$post->updated))));
		$sitemap->end();
 * </code>
 */
class HSitemaps{
	private $prefix,$count=0,$sitemap,$sitemapNum=2;
	
	/**
	 * @param string
	 */
	public function __construct($prefix=''){
		$this->prefix=$prefix;
		$this->sitemap=$this->createNewSitemap('sitemaps/'.$prefix.'1.xml');
	}
	
	/**
	 * @param string
	 */
	protected function createNewSitemap($name){
		return new HSitemap($name);
	}
	
	/**
	 * @param string|array
	 * @param array
	 * @param string
	 */
	public function add($url,$options=array(),$entry='index'){
		if(++$this->count >49995){
			$this->count=0;
			$this->sitemap->end();
			unset($this->sitemap);
			$this->sitemap=$this->createNewSitemap('sitemaps/'.$this->prefix.($this->sitemapNum++).'.xml');
		}
		$this->sitemap->add($url,$options,$entry);
	}
	
	/**
	 * @return void
	 */
	public function end(){
		$this->sitemap->end();
		
		$sitemap=new HSitemapIndex($this->prefix.'sitemaps.xml');
		$lastMod=date('c');
		for($i=1;$i<$this->sitemapNum;$i++)
			$sitemap->add('/web/files/sitemaps/'.$this->prefix.$i.'.xml.gz',array('lastmod'=>$lastMod));
		$sitemap->end();
	}
}
