<?php
/** http://ogp.me/ */
class HOpenGraphMeta{
	public static function display(){
		/* DEV */throw new Exception('Use HHead::display() now'); /* /DEV */
	}
	
	public static function title($title){
		HHead::metaProperty('og:title',$title);
		/* DEV */ return '<div style="color:red;font-size:12pt">Please do not echo HOpenGraphMeta::title()</div>'; /* /DEV */
	}
	public static function description($description){
		HHead::metaProperty('og:description',$description);
		/* DEV */ return '<div style="color:red;font-size:12pt">Please do not echo HOpenGraphMeta::description()</div>'; /* /DEV */
	}
	
	public static function siteName($siteName){
		HHead::metaProperty('og:site_name',$siteName);
		/* DEV */ return '<div style="color:red;font-size:12pt">Please do not echo HOpenGraphMeta::siteName()</div>'; /* /DEV */
	}
	
	public static function fbApp($appId){
		HHead::metaProperty('fb:app_id',$appId);
		/* DEV */ return '<div style="color:red;font-size:12pt">Please do not echo HOpenGraphMeta::fbApp()</div>'; /* /DEV */
	}
	
	public static function locale($locale){
		HHead::metaProperty('og:locale',$locale);
		/* DEV */ return '<div style="color:red;font-size:12pt">Please do not echo HOpenGraphMeta::locale()</div>'; /* /DEV */
	}
	
	public static function type($type){
		HHead::metaProperty('og:type',$type);
		/* DEV */ return '<div style="color:red;font-size:12pt">Please do not echo HOpenGraphMeta::type()</div>'; /* /DEV */
	}
	
	
	
	/** That image should be at least 50x50 in any of the usually supported image forms (JPG, PNG, etc.) */
	public static function image($url,$type=null,$width=null,$height=null){
		HHead::metaPropertyAdd('og:image',$url);
		if($type!==null) HHead::metaPropertyAdd('og:image:type',$type);
		if($width!==null) HHead::metaPropertyAdd('og:image:width',$width);
		if($height!==null) HHead::metaPropertyAdd('og:image:height',$height);
		
		/* DEV */ return '<div style="color:red;font-size:12pt">Please do not echo HOpenGraphMeta::image()</div>'; /* /DEV */
	}
	
}