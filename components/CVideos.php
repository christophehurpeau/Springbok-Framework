<?php
/**
 * Videos components
 */
class CVideos{
	const TYPE_YOUTUBE=1;
	const TYPE_DAILYMOTION=2;
	const TYPE_VIMEO=3;
	
	/**
	 * Parse a URL and return the type and id of the video, or null if not found
	 * 
	 * @param string
	 * @return array
	 */
	public static function parseVideo($url){
		extract(parse_url($url)); $queryArray=array(); parse_str(parse_url($url,PHP_URL_QUERY),$queryArray);
		if(!isset($host)) return NULL;
		
		if($host=='youtu.be'){ $type=self::TYPE_YOUTUBE; $id=substr($path,1); }
		elseif(strpos($host,'youtube')!==false){
			$type=self::TYPE_YOUTUBE;
			$id=isset($queryArray['v'])?$queryArray['v']:NULL;
		}elseif(strpos($host,'dailymotion')!==false){
			$type=self::TYPE_DAILYMOTION; $matches=array();

			if(preg_match('/^\/playlist\/([^_]+)_(.+)/',$path,$matches)){
				if(preg_match('/^videoId=(.+)/',$fragment,$matches)) $id=$matches[1];
				else $id=NULL;
			}elseif(preg_match('/^\/video\/([^_\/]+)(?:_.*)?/',$path,$matches)){
				$id=$matches[1];
			}elseif(preg_match('/^\/([^_\/]+)_/',$path,$matches)){
				$id=$matches[1];
			}else $id=NULL;
		}elseif(substr($host,-9)==='vimeo.com'){
			$type=self::TYPE_VIMEO; $matches=array();
			if(preg_match('/^\/([0-9]+)$/',$path,$matches)) $id=$matches[1];
		}/*elseif(substr($host,0,12)==='video.google'){
			$host='google';
			if($path=='/videoplay' && isset($queryArray['docid'])) $id=$queryArray['docid'];
		}*/else return NULL;
		if($id===NULL) return NULL;

		return array($type,$id);
	}
	
	/**
	 * Replace videos url by their shorter version
	 * 
	 * @param string
	 * @return string
	 */
	public static function replaceVideos($content){
		//tester
		$content=preg_replace('/(?:http:\/\/(?:www\.)?)?youtube\.[a-z]{2,3}\/watch\?(?:[^\s]+&)?v=([a-z0-9\-_]+)(?:&[^\s,\.]+)?(,|\.|\s|$)/Ui','http://youtu.be/$1$2',$content);
		//dai.ly
		$content=preg_replace('/(?:http:\/\/(?:www\.)?)?dailymotion\.[a-z]{2,3}\/playlist\/[^\#]+\#videoId=([a-z0-9\-_]+)(,|\.|\s|$)/Ui','http://www.dailymotion.com/video/$1$2',$content);
		return $content;
	}
	
	
	/* APIs */
	
	/**
	 * Get info of a video
	 * 
	 * @param int
	 * @param int|string
	 * @return mixed
	 */
	public static function infos($type,$id){
		switch ($type){
			case self::TYPE_YOUTUBE: return self::youtubeInfos($id);
			case self::TYPE_DAILYMOTION: return self::dailymotionInfos($id);
			case self::TYPE_VIMEO: return self::vimeoInfos($id);
		}
	}
	
	/**
	 * Return infos of a youtube video
	 * 
	 * @param int
	 * @return array
	 */
	public static function youtubeInfos($id){
		$api=simplexml_load_file('http://gdata.youtube.com/feeds/api/videos/'.$id);
		if(empty($api)) return false;
		return array(
			'api'=>&$api,
			'title'=>(string)$api->title,
			'description'=>(string)$api->content,
			'author'=>(string)$api->author->name,
			'published'=>(string)$api->published,
			'tiny_url'=>'http://youtu.be/'.$id,
			'image'=>'http://img.youtube.com/vi/'.$id.'/default.jpg'
		);
	}
	
	/**
	 * Return infos of a dailymotion video
	 * 
	 * @param int
	 * @return array
	 */
	public static function dailymotionInfos($id){
		// http://www.dailymotion.com/doc/api/rest-api-reference.html
		$api=json_decode(file_get_contents('https://api.dailymotion.com/video/'.$id.'?fields=title,description,created_time,owner,tiny_url,thumbnail_url'));
		if(empty($api)) return false;
		return array(
			'title'=>$api->title,
			'description'=>$api->description,
			'author'=>$api->owner,
			'published'=>$api->created_time,
			'tiny_url'=>$api->tiny_url,
			'image'=>$api->thumbnail_url
		);
	}
	/*
	public static function googleInfos($id){
		//http://video.google.com/videofeed
	}*/
	
	/**
	 * Return infos of a vimeo video
	 * 
	 * @param int
	 * @return array
	 */
	public static function vimeoInfos($id){
		// http://vimeo.com/api/docs/simple-api
		$api=json_decode(file_get_contents('http://vimeo.com/api/v2/video/'.$id.'.json'));
		if(empty($api)) return false;
		$api=$api[0];
		return array(
			'title'=>$api->title,
			'description'=>$api->description,
			'author'=>$api->user_name,
			'published'=>$api->upload_date,
			'tiny_url'=>$api->url,
			'image'=>$api->thumbnail_large
		);
	}
}
/*
include dirname(__DIR__).'/base/base.php';

$videos=array('http://www.youtube.com/watch?v=1ZW_lTlyoLo&feature=hp_SLN&list=SL','http://youtu.be/w1ZW_lTlyoLo',
	'http://www.dailymotion.com/playlist/x1lzaj_ManganewsTV-Nostalgie_la-legende-de-blanche-nei#videoId=ximixw',
	'http://www.dailymotion.com/video/ximixwazeijaziejalkzj',
	'http://www.vimeo.com/22439234',
	//'http://video.google.com/videoplay?docid=1012112401888779719#'
);

foreach($videos as $v){
	$video=CVideos::parseVideo($v);
	if($video){
		list($host,$id)=$video;
		switch($host){
			case 'youtube':
				//debug(CVideos::youtubeInfos($id));
			break;
			case 'dailymotion':
				//debug(CVideos::dailymotionInfos($id));
			break;
			case 'vimeo':
				debug(CVideos::vimeoInfos($id));
			break;
		}
	}
}
*/


//include dirname(__DIR__).'/base/base.php';
/*
$videos=array('http://www.youtube.com/watch?v=1ZW_lTlyoLo&feature=hp_SLN&list=SL','http://youtu.be/w1ZW_lTlyoLo',
	'http://www.dailymotion.com/playlist/x1lzaj_ManganewsTV-Nostalgie_la-legende-de-blanche-nei#videoId=ximixw',
	'http://www.dailymotion.com/video/ximixwazeijaziejalkzj',
	'http://www.vimeo.com/22439234',
	//'http://video.google.com/videoplay?docid=1012112401888779719#'
);

foreach($videos as $v){
	print_r(CVideos::replaceVideos($v));
}
print_r(CVideos::replaceVideos(implode(' ',$videos)));
 
*/