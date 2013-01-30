<?php
class HHead{
	private static $head;
	
	
	public static function title($title){
		self::$head='<title>'.h($title).'</title>'.self::$head;
		/* DEV */ return '<div style="color:red;font-size:12pt">Please do not echo HHead::title()</div>'; /* /DEV */
	}
	public static function favicon($imgUrl='favicon.png'){
		$href=STATIC_URL.'img/'.$imgUrl;
		self::$head.='<link rel="icon" type="image/vnd.microsoft.icon" href="'.$href.'"/>'
			.'<link rel="shortcut icon" type="image/x-icon" href="'.$href.'"/>';
		/* DEV */ return '<div style="color:red;font-size:12pt">Please do not echo HHead::favicon()</div>'; /* /DEV */
	}
	public static function logoMobile($imgNamePrefix='logo'){
		$href=STATIC_URL.'img/'.$imgNamePrefix;
		self::$head.=
			//<!-- For third-generation iPad with high-resolution Retina display: -->
			 '<link rel="apple-touch-icon-precomposed" sizes="144x144" href="'.$href.'-144.png">'
			//<!-- For iPhone with high-resolution Retina display: -->
			.'<link rel="apple-touch-icon-precomposed" sizes="114x114" href="'.$href.'-114.png">'
			//<!-- For first- and second-generation iPad: -->
			.'<link rel="apple-touch-icon-precomposed" sizes="72x72" href="'.$href.'-72.png">'
			//<!-- For non-Retina iPhone, iPod Touch, and Android 2.1+ devices: -->
			.'<link rel="apple-touch-icon-precomposed" href="'.$href.'-57.png">'
			.'<link rel="apple-touch-icon" href="'.$href.'-57.png"/>';
		/* DEV */ return '<div style="color:red;font-size:12pt">Please do not echo HHead::logoMobile()</div>'; /* /DEV */
	}
	
	public static function meta($property,$content){
		/* DEV */if($property!==h($property)) throw new Exception('Please escape property'); /* /DEV */
		self::$head.='<meta property="'.$property.'" content="'.h($content).'"/>';
		/* DEV */ return '<div style="color:red;font-size:12pt">Please do not echo HHead::meta()</div>'; /* /DEV */
	}
	
	/* DEV */ private static $_IE_started=false; /* /DEV */
	public static function startIeIf($ieVersion,$operator){
		/* DEV */ self::$_IE_started=true; /* /DEV */
		self::$head.='<!--[if IE';
		if(!empty($ieVersion)){
			switch($operator){
				case '=': break;
				case '<': self::$head.=' lt'; break;
				case '>': self::$head.=' gt'; break;
				case '<=': self::$head.=' lte'; break;
				case '>=': self::$head.=' gte'; break;
				/* DEV */default: throw new Exception('Unknown operator: '.$operator);/* /DEV */
			}
		}
		self::$head.=']>';
		/* DEV */ return '<div style="color:red;font-size:12pt">Please do not echo HHead::startIeIf()</div>'; /* /DEV */
	}
	public static function endIeIf(){
		/* DEV */ if(self::$_IE_started!==true) throw new Exception('ie is not started');
		self::$_IE_started=false; /* /DEV */
		self::$head.='<![endif]-->';
		/* DEV */ return '<div style="color:red;font-size:12pt">Please do not echo HHead::endIeIf()</div>'; /* /DEV */
	}
	
	public static function linkCssAndJs($url='/index'){
		self::linkCss($url);
		self::linkJs($url);
		/* DEV */ return '<div style="color:red;font-size:12pt">Please do not echo HHead::linkCssAndJs()</div>'; /* /DEV */
	}
	
	public static function linkCss($url='/main',$media=false){
		/* DEV */ if(self::$_IE_started===true) throw new Exception('ie is started. Css is not added in IE if'); /* /DEV */
		/* Keep css up */
		self::$head='<link rel="stylesheet" type="text/css" href="'.HHtml::staticUrl(strpos($url,'?')?$url:($url.'.css'),'css').'"'.($media?' media="'.$media.'"':'').'/>'
			.self::$head;
		/* DEV */ return '<div style="color:red;font-size:12pt">Please do not echo HHead::linkCss()</div>'; /* /DEV */
	}
	
	public static function linkJs($url='/global'){
		self::$head.='<script type="text/javascript" src="'.HHtml::staticUrl($url.'.js','js').'"></script>';
		/* DEV */ return '<div style="color:red;font-size:12pt">Please do not echo HHead::linkJs()</div>'; /* /DEV */
	}
	public static function linkJsIe($ieVersion,$operator,$url){
		self::startIeIf($ieVersion,$operator);
		self::linkJs($url);
		self::endIeIf();
		/* DEV */ return '<div style="color:red;font-size:12pt">Please do not echo HHead::linkJsIe()</div>'; /* /DEV */
	}
	public static function jsI18n(){
		self::linkJs('/i18n-'.CLang::get());
		/* DEV */ return '<div style="color:red;font-size:12pt">Please do not echo HHead::jsI18n()</div>'; /* /DEV */
	}
	
	public static function linkRel($rel,$url,$entry=null){
		/* DEV */if($rel!==h($rel)) throw new Exception('Please escape rel'); /* /DEV */
		self::$head.='<link rel="'.$rel.'" href="'.HHtml::urlEscape($url,$entry,true).'"/>';
		/* DEV */ return '<div style="color:red;font-size:12pt">Please do not echo HHead::linkRel()</div>'; /* /DEV */
	}
	public static function linkPrev($url,$entry=null){ self::linkRel('prev',$url,$entry);
		/* DEV */ return '<div style="color:red;font-size:12pt">Please do not echo HHead::linkPrev()</div>'; /* /DEV */ }
	public static function linkNext($url,$entry=null){ self::linkRel('next',$url,$entry);
		/* DEV */ return '<div style="color:red;font-size:12pt">Please do not echo HHead::linkNext()</div>'; /* /DEV */ }
	public static function linkSmallSizes($url,$entry=null){
		self::$head.='<link rel="alternate" media="only screen and (max-width: 640px)" href="'.HHtml::urlEscape($url,$entry,true).'"/>';
		/* DEV */ return '<div style="color:red;font-size:12pt">Please do not echo HHead::linkSmallSizes()</div>'; /* /DEV */
	}
	public static function linksLangs($altLangs){
		foreach($altLangs as $lang=>$url) self::$head.='<link rel="alternate" hreflang="'.$lang.'" href="'.HHtml::urlEscape($url).'"/>';
		/* DEV */ return '<div style="color:red;font-size:12pt">Please do not echo HHead::linksLangs()</div>'; /* /DEV */
	}
	
	
	/* DEV */private static $_displayed=false;/* /DEV */
	public static function display(){
		/* DEV */
		if(self::$_IE_started===true) throw new Exception('ie is started');
		if(self::$_displayed===true) throw new Exception('Already displayed');
		self::$_displayed=true;
		/* /DEV */
		echo self::$head;
		/* DEV */ return '<div style="color:red;font-size:12pt">Please do not echo HHead::display()</div>'; /* /DEV */
	}
}