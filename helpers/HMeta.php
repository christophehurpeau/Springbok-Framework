<?php
/** http://www.google.com/support/webmasters/bin/answer.py?answer=79812 */
class HMeta{
	private static $canonical,$canonicalEntry,$canonicalFullUrl=true,$prev,$next,$smallSizes,$altLangs;
	
	public static function keywords($keywords){
		HHead::metaName('keywords',$keywords);
		/* DEV */ return '<div style="color:red;font-size:12pt">Please do not echo HMeta::keywords()</div>'; /* /DEV */
	}
	public static function description($description){
		HHead::metaName('description',$description);
		/* DEV */ return '<div style="color:red;font-size:12pt">Please do not echo HMeta::description()</div>'; /* /DEV */
	}
	
	public static function set($metas){
		HHead::metaName('keywords',$metas['keywords']);
		HHead::metaName('description',$metas['description']);
		/* DEV */ return '<div style="color:red;font-size:12pt">Please do not echo HMeta::set()</div>'; /* /DEV */
	}
	
	public static function robots($content){
		HHead::metaName('robots',$content);
		/* DEV */ return '<div style="color:red;font-size:12pt">Please do not echo HMeta::robots()</div>'; /* /DEV */
	}
	public static function googlebot($content){
		HHead::metaName('googlebot',$content);
		/* DEV */ return '<div style="color:red;font-size:12pt">Please do not echo HMeta::googlebot()</div>'; /* /DEV */
	}
	
	public static function noindex_follow(){
		HHead::metaName('robots','noindex, follow');
		/* DEV */ return '<div style="color:red;font-size:12pt">Please do not echo HMeta::noindex_follow()</div>'; /* /DEV */
	}
	public static function noindex_nofollow(){
		HHead::metaName('robots','noindex, nofollow');
		/* DEV */ return '<div style="color:red;font-size:12pt">Please do not echo HMeta::noindex_nofollow()</div>'; /* /DEV */
	}
	
	public static function nosnippet(){
		HHead::metaName('robots','nosnippet');
		/* DEV */ return '<div style="color:red;font-size:12pt">Please do not echo HMeta::nosnippet()</div>'; /* /DEV */
	}
	public static function noarchive(){
		HHead::metaName('robots','noarchive');
		/* DEV */ return '<div style="color:red;font-size:12pt">Please do not echo HMeta::noarchive()</div>'; /* /DEV */
	}
	
	
	public static function google_notranslate(){
		HHead::metaName('google','notranslate');
		/* DEV */ return '<div style="color:red;font-size:12pt">Please do not echo HMeta::google_notranslate()</div>'; /* /DEV */
	}
	
	public static function viewport(){
		HHead::metaName('viewport','target-densitydpi=device-dpi, width=device-width, initial-scale=1');
		/* DEV */ return '<div style="color:red;font-size:12pt">Please do not echo HMeta::viewport()</div>'; /* /DEV */
	}
	
	public static function msApp($color,$entryStart=null){
		HHead::metaName("application-name",Config::$projectName);
		HHead::metaName("msapplication-starturl",Config::$siteUrl[$entryStart===null ? Springbok::$scriptname : 'index']);
		HHead::metaName("msapplication-window","width=1024;height=768");
		HHead::metaName("msapplication-navbutton-color",$color);
		/* DEV */ return '<div style="color:red;font-size:12pt">Please do not echo HMeta::msApp()</div>'; /* /DEV */
	}
	public static function msAppAction($name,$url,$entry=null,$icon='favicon.ico'){
		HHead::metaNameAdd("msapplication-task",'name='.$name.'; action-uri='.HHtml::url($url,$entry,true).'; icon-uri=/web/img/'.$icon);
		/* DEV */ return '<div style="color:red;font-size:12pt">Please do not echo HMeta::msAppAction()</div>'; /* /DEV */
	}

	/* Windows8 */
	public static function msTile($color,$image='logo-144.png'){
		HHead::metaName("msapplication-TileColor",$color);
		HHead::metaName("msapplication-TileImage",'/web/img/'.$image);
	}

	public static function position($lat,$lng,$placename=null,$region=null){
		/* http://en.wikipedia.org/wiki/Geotagging */
		HHead::metaName("ICBM",$lat.', '.$lng);
		HHead::metaProperty('place:location:latitude',$lat);
		HHead::metaProperty('place:location:longitude',$lng);
		HHead::metaName("geo.position",$lat.';'.$lng);
		if($placename!==null) HHead::metaName("geo.placename",$placename);
		if($region!==null) HHead::metaName("geo.region",$region);
	}
	
	public static function canonical($url){ self::$canonical=$url; }
	public static function canonicalEntry($entry){ self::$canonicalEntry=$entry; }
	public static function canonicalFullUrl($full){ self::$canonicalFullUrl=$full; }
	public static function prev($url){ self::$prev=$url; }
	public static function next($url){ self::$next=$url; }
	public static function smallSizes($url,$entry){ self::$smallSizes=HHtml::urlEscape($url,$entry,true); }
	public static function smallSizesUrl($url){ self::$smallSizes=h($url); }
	
	public static function altlangs($urls){ self::$altLangs=$urls; }
	
	public static function display(){
		/* DEV */throw new Exception('Use HHead::display() now'); /* /DEV */
	}
	
	public static function displayCanonical(){
		/* DEV */ if(self::$canonical===null && Springbok::$inError===null) throw new Exception("canonical is not defined"); /* /DEV */
		if(self::$canonical===false) return '';
		echo '<link rel="canonical" href="'.($href=HHtml::urlEscape(self::$canonical,self::$canonicalEntry,self::$canonicalFullUrl)).'"/>'
				.'<meta property="og:url" content="'.$href.'"/>';
		if(self::$prev!==null) echo '<link rel="prev" href="'.HHtml::urlEscape(self::$prev,self::$canonicalEntry,self::$canonicalFullUrl).'"/>';
		if(self::$next!==null) echo '<link rel="next" href="'.HHtml::urlEscape(self::$next,self::$canonicalEntry,self::$canonicalFullUrl).'"/>';
		if(self::$smallSizes!==null) echo '<link rel="alternate" media="only screen and (max-width: 640px)" href="'.self::$smallSizes.'"/>';
		/* DEV */ return '<div style="color:red;font-size:12pt">Please do not echo HMeta::displayCanonical()</div>'; /* /DEV */
	}
	
	public static function getCanonicalRaw(){ return self::$canonical; }
	public static function getCanonical($fullUrl=true){
		if(empty(self::$canonical)) return false;
		return HHtml::url(self::$canonical,self::$canonicalEntry,$fullUrl);
	}
	public static function getSmallSizesEscapedUrl(){
		return self::$smallSizes;
	}
}
