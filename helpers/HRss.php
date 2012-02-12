<?php
class HRss{
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
}
