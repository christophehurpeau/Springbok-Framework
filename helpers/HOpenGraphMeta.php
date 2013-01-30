<?php
/** http://ogp.me/ */
class HOpenGraphMeta{
	public static function display(){
		/* DEV */throw new Exception('Use HHead::display() now'); /* DEV */
	}
	
	public static function title($title){
		HHead::meta('og:title',$title);
		/* DEV */ return '<div style="color:red;font-size:12pt">Please do not echo HOpenGraphMeta::title()</div>'; /* /DEV */
	}
	
	/** That image should be at least 50x50 in any of the usually supported image forms (JPG, PNG, etc.) */
	public static function image($url){
		HHead::meta('og:image',$url);
		/* DEV */ return '<div style="color:red;font-size:12pt">Please do not echo HOpenGraphMeta::image()</div>'; /* /DEV */
	}
	
	public static function siteName($siteName){
		HHead::meta('og:site_name',$siteName);
		/* DEV */ return '<div style="color:red;font-size:12pt">Please do not echo HOpenGraphMeta::siteName()</div>'; /* /DEV */
	}
	
	public static function fbApp($appId){
		HHead::meta('fb:app_id',$appId);
		/* DEV */ return '<div style="color:red;font-size:12pt">Please do not echo HOpenGraphMeta::fbApp()</div>'; /* /DEV */
	}
}