<?php
/**
 * Open Graph Metas
 * 
 * @see http://ogp.me/
 * @uses HHead
 */
class HOpenGraphMeta{
	/** @deprecated */
	public static function display(){
		/*#if DEV */throw new Exception('Use HHead::display() now'); /*#/if*/
	}
	
	/**
	 * Set the og:title meta
	 * 
	 * @param string
	 * @return void
	 */
	public static function title($title){
		HHead::metaProperty('og:title',$title);
		/*#if DEV */ return '<div style="color:red;font-size:12pt">Please do not echo HOpenGraphMeta::title()</div>'; /*#/if*/
	}
	
	/**
	 * Set the og:description meta
	 * 
	 * @param string
	 * @return void
	 */
	public static function description($description){
		HHead::metaProperty('og:description',$description);
		/*#if DEV */ return '<div style="color:red;font-size:12pt">Please do not echo HOpenGraphMeta::description()</div>'; /*#/if*/
	}
	
	/**
	 * Set the og:site_name meta
	 * 
	 * @param string
	 * @return void
	 */
	public static function siteName($siteName){
		HHead::metaProperty('og:site_name',$siteName);
		/*#if DEV */ return '<div style="color:red;font-size:12pt">Please do not echo HOpenGraphMeta::siteName()</div>'; /*#/if*/
	}
	
	/**
	 * Set the fb:app_id meta
	 * 
	 * @param string
	 * @return void
	 */
	public static function fbApp($appId){
		HHead::metaProperty('fb:app_id',$appId);
		/*#if DEV */ return '<div style="color:red;font-size:12pt">Please do not echo HOpenGraphMeta::fbApp()</div>'; /*#/if*/
	}
	
	/**
	 * Set the og:locale meta
	 * 
	 * @param string
	 * @return void
	 */
	public static function locale($locale){
		HHead::metaProperty('og:locale',$locale);
		/*#if DEV */ return '<div style="color:red;font-size:12pt">Please do not echo HOpenGraphMeta::locale()</div>'; /*#/if*/
	}
	
	/**
	 * Set the og:type meta
	 * 
	 * @param string
	 * @return void
	 */
	public static function type($type){
		HHead::metaProperty('og:type',$type);
		/*#if DEV */ return '<div style="color:red;font-size:12pt">Please do not echo HOpenGraphMeta::type()</div>'; /*#/if*/
	}
	
	
	
	/**
	 * Set a og:image meta
	 * All the images referenced by og:image should be at least 200px in both dimensions, with 1500x1500 preferred. (Maximum image size is 5MB.)
	 * Please check all the images with tag og:image in the given url and ensure that it meets the recommended specification.
	 * 
	 * @param string
	 * @param string|null
	 * @param string|null
	 * @param string|null
	 * @return void
	 */
	public static function image($url,$type=null,$width=null,$height=null){
		HHead::metaPropertyAdd('og:image',$url);
		if($type!==null) HHead::metaPropertyAdd('og:image:type',$type);
		if($width!==null) HHead::metaPropertyAdd('og:image:width',$width);
		if($height!==null) HHead::metaPropertyAdd('og:image:height',$height);
		
		/*#if DEV */ return '<div style="color:red;font-size:12pt">Please do not echo HOpenGraphMeta::image()</div>'; /*#/if*/
	}
	
}