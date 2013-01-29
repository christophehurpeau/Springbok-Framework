<?php
/** http://ogp.me/ */
class HOpenGraphMeta{
	private static $metas;
	
	public static function display(){
		if(self::$metas===null)return'';
		$res='';
		foreach(self::$metas as $key=>&$content)
			$res.= '<meta property="'.h($key).'" content="'.h($content).'"/>';
		return $res;
	}
	
	public static function title($title){
		self::$metas['og:title']=$title;
	}
	
	/** That image should be at least 50x50 in any of the usually supported image forms (JPG, PNG, etc.) */
	public static function image($url){
		self::$metas['og:image']=$url;
	}
	
	public static function siteName($siteName){
		self::$metas['og:site_name']=$siteName;
	}
	
	public static function fbApp($appId){
		self::$metas['fb:app_id']=$appId;
	}
}