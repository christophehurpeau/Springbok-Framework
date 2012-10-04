<?php
/** http://www.google.com/support/webmasters/bin/answer.py?answer=79812 */
class HMeta{
	private static $metas,$canonical,$canonicalEntry,$prev,$next,$smallSizes,$altLangs;
	
	public static function keywords($keywords){
		self::$metas['keywords']=$keywords;
	}
	public static function description($description){
		self::$metas['description']=$description;
	}
	
	public static function set($metas){
		self::$metas['keywords']=$metas['keywords'];
		self::$metas['description']=$metas['description'];
	}
	
	public static function robots($content){
		self::$metas['robots']=$content;
	}
	public static function googlebot($content){
		self::$metas['googlebot']=$content;
	}
	
	public static function google_notranslate(){
		self::$metas['google']='notranslate';
	}
	
	public static function canonical($url){ self::$canonical=$url; }
	public static function canonicalEntry($entry){ self::$canonicalEntry=$entry; }
	public static function prev($url){ self::$prev=$url; }
	public static function next($url){ self::$next=$url; }
	public static function smallSizes($url,$entry){ self::$smallSizes=HHtml::urlEscape($url,$entry,true); }
	public static function smallSizesUrl($url){ self::$smallSizes=h($url); }
	
	public static function altlangs($urls){ self::$altLangs=$urls; }
	
	public static function display(){
		if(self::$metas===null)return'';
		$res='';
		foreach(self::$metas as $key=>&$content)
			$res.= '<meta name="'.h($key).'" content="'.h($content).'"/>';
		return $res;
	}
	
	public static function displayCanonical(){
		/* DEV */ if(self::$canonical===null && !Springbok::$inError) throw new Exception("canonical is not defined"); /* /DEV */
		if(self::$canonical===false) return '';
		$result='<link rel="canonical" href="'.HHtml::urlEscape(self::$canonical,self::$canonicalEntry,true).'"/>';
		if(self::$prev!==null) $result.='<link rel="prev" href="'.HHtml::urlEscape(self::$prev,self::$canonicalEntry,true).'"/>';
		if(self::$next!==null) $result.='<link rel="next" href="'.HHtml::urlEscape(self::$next,self::$canonicalEntry,true).'"/>';
		if(self::$smallSizes!==null) $result.='<link rel="alternate" media="only screen and (max-width: 640px)" href="'.self::$smallSizes.'"/>';
		return $result;
	}
	
	public static function getCanonicalRaw(){ return self::$canonical; }
	public static function getCanonical($fullUrl=true){
		if(empty(self::$canonical)) return false;
        return HHtml::url(self::$canonical,self::$canonicalEntry,$fullUrl);
	}
	public static function getSmallSizesEscapedUrl(){
		return self::$smallSizes;
	}
	
	public static function displayAltLangs(){
		if(empty(self::$altLangs)) return '';
		$result='';
		foreach(self::$altLangs as $lang=>$url) '<link rel="alternate" hreflang="'.$lang.'" href="'.HHtml::urlEscape($url).'"/>';
		
	}
}
