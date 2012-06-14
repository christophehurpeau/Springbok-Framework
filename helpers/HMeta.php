<?php
/** http://www.google.com/support/webmasters/bin/answer.py?answer=79812 */
class HMeta{
	private static $metas,$canonical;
	
	public static function keywords($keywords){
		self::$metas['keywords']=$keywords;
	}
	public static function description($description){
		self::$metas['description']=$description;
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
	
	public static function canonical($url){ self::$canonical=&$url; }
	
	public static function display(){
		if(self::$metas===null)return'';
		$res='';
		foreach(self::$metas as $key=>&$content)
			$res.= '<meta name="'.h($key).'" content="'.h($content).'"/>';
		return $res;
	}
	
	public static function displayCanonical(){
		/* DEV */ if(self::$canonical===null && !Springbok::$inError) throw new Exception("canonical is not defined"); /* /DEV */
		return self::$canonical===false?'':'<link rel="canonical" href="'.HHtml::url(self::$canonical).'"/>';
	}
}
