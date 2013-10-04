<?php
/**
 * Rss file creator
 * 
 * <code>
 * $posts=Post::QAll()->fields('id,published,excerpt')->withParent('name,slug,updated')
			->where(array('status'=>Post::PUBLISHED))->orderBy(array('published'=>'DESC'))->fetch();
 *	$rss=new HRss('Actualités','Actualités du Site','fr','Copyright '.date('Y').' Site name.',
 *			array('url'=>HHtml::staticUrl('/logo-144.png','img'),'description'=>"Toutes les actualités"),'/posts');
 *	foreach($posts as $post){
 *		$post->excerpt=UHtml::transformInternalLinks($post->excerpt,Config::$internalLinks,'index',true);
 *		$post->excerpt=preg_replace("/<img[^>]+\>/i", '', $post->excerpt);
 *		$rss->add($post->name,$post->link(),$post->excerpt,$post->published);
 *	}
 *	$rss->end();
 * </code>
 */
class HRss{
	/**
	 * Display a RSS from somewhere
	 * 
	 * @param string
	 * @param int
	 * @param string
	 */
	public static function display($url,$size=10,$titleTagName='h3'){
		echo CCache::get()->readOrWrite('rss_'.md5($url),function() use(&$url,&$size,&$titleTagName){
			$xml=simplexml_load_file($url); $num=1;
			if(empty($xml)) return '';
			ob_start();
			$channel=$xml->channel; //$channel->ttl ; $channel->image
			if($titleTagName) HHtml::tag($titleTagName,array(),HHtml::link($channel->title,$channel->link,array('title'=>$channel->description,'target'=>'_blank')),false);
			if(!empty($channel->item)){
				echo '<ul>';
				foreach($channel->item as $item){
					echo '<li>';
					echo HHtml::link($item->title,$item->link,array('target'=>'_blank')).(empty($item->description)?'':(' : '.$item->description));
					echo '</li>';
					if(++$num>$size) break;
				}
				echo '</ul>';
			}
			return ob_get_clean();
		});
	}
	
	
	private $_file;
	
	/**
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 */
	public function __construct($title,$description,$lang,$copyright,$image,$link='/',$file='rss.xml'){//TODO atom feed 
		$this->_file=fopen(($file[0]==='/'?'':APP.'web/files/').$file,'w');
		fwrite($this->_file,'<?xml version="1.0" encoding="UTF-8"?>
<rss xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:taxo="http://purl.org/rss/1.0/modules/taxonomy/" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">'
			.'<channel><title>'.($title=h($title)).'</title><link>'.($link=HHtml::urlEscape($link,'index',true,false,false)).'</link><description>'.$description.'</description><language>'.$lang.'</language>'
						.'<copyright>'.h($copyright).'</copyright><pubDate>'.($pubDate=date('r')).'</pubDate><lastBuildDate>'.$pubDate.'</lastBuildDate>'
						.'<image><title>'.$title.'</title><url>'.h($image['url']).'</url><link>'.$link.'</link>'
							.'<description>'.h($image['description']).'</description></image>'
				.'<atom:link href="'.HHtml::urlEscape('/','index',true,false,false).'web/files/'.$file.'" rel="self" type="application/rss+xml"/>');
	}
	
	/**
	 * Add a link
	 * 
	 * @param string
	 * @param string|array
	 * @param string
	 * @param string
	 * @param null
	 * 
	 * @return void
	 */
	public function add($title,$link,$description,$pubDate,$enclosure=null){
		fwrite($this->_file,'<item>'
			.'<title>'.h($title).'</title>'
			.'<link>'.($link=h(HHtml::url($link,'index',true,false,false))).'</link>'
			.'<description>'.h($description).'</description>'
			//.'<enclosure url="http://s1.lemde.fr/image/2012/01/05/87x0/1626126_7_0813_l-espagne-est-un-pays-solvable-a-reaffirme_b7eff2a082c6d8ec4a9745c29a62dd2a.jpg" length="1642" type="image/jpeg" />
			.'<pubDate>'.date('r',strtotime($pubDate)).'</pubDate>'
			.'<guid>'.$link.'</guid>'
		.'</item>');
	}
	
	/**
	 * Ends the file and close
	 * 
	 * @return void
	 */
	public function end(){
		fwrite($this->_file,'</channel></rss>');
		fclose($this->_file);
	}
}
