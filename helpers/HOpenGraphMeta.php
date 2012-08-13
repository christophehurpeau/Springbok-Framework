<?php
/** http://ogp.me/ */
class HOpenGraphMeta{
	private static $metas;
	
	public static function display(){
		if(self::$metas===null)return'';
		$res='';
		foreach(self::$metas as $key=>&$content)
			$res.= '<meta property="og:'.h($key).'" content="'.h($content).'"/>';
		return $res;
	}
	
	/** That image should be at least 50x50 in any of the usually supported image forms (JPG, PNG, etc.) */
	public static function image($url){
		self::$metas['image']=$url;
	}
	
	public static function siteName($siteName){
		self::$metas['site_name']=$siteName;
	}
}