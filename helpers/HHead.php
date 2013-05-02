<?php
class HHead{
	private static $head=array('title'=>'','icons'=>'','css'=>'','js'=>'','endjs'=>'','linksrel'=>'');
	
	
	public static function title($title){
		/* DEV */ self::testDisplayed(); /* /DEV */
		self::$head['title']='<title>'.h($title).'</title>';
		/* DEV */ return '<div style="color:red;font-size:12pt">Please do not echo HHead::title()</div>'; /* /DEV */
	}
	public static function favicon($imgUrl='favicon.png'){
		/* DEV */ self::testDisplayed(); /* /DEV */
		$href=STATIC_URL.'img/'.$imgUrl;
		self::$head['icons']='<link rel="icon" type="image/vnd.microsoft.icon" href="'.$href.'"/>'
			.'<link rel="shortcut icon" type="image/x-icon" href="'.$href.'"/>';
		/* DEV */ return '<div style="color:red;font-size:12pt">Please do not echo HHead::favicon()</div>'; /* /DEV */
	}
	public static function icons($imgNamePrefix='logo'){
		/* DEV */ self::testDisplayed(); /* /DEV */
		/* http://www.whatwg.org/specs/web-apps/current-work/multipage/links.html#rel-icon */
		$href=STATIC_URL.'img/'.$imgNamePrefix;
		self::$head['icons'].=
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
	
	public static function metaName($name,$content){
		/* DEV */
		self::testDisplayed();
		if($name!==h($name)) throw new Exception('Please escape name');
		//if(Springbok::$inError===null && isset(self::$_metasName[$name])) throw new Exception('Meta already defined : '.$name);
		//self::$_metasName[$name]=true;
		/* /DEV */
		self::$head['metaname.'.$name]='<meta name="'.$name.'" content="'.h($content).'"/>';
		/* DEV */ return '<div style="color:red;font-size:12pt">Please do not echo HHead::meta()</div>'; /* /DEV */
	}
	
	public static function metaNameAdd($name,$content){
		/* DEV */
		self::testDisplayed();
		if($name!==h($name)) throw new Exception('Please escape name');
		/* /DEV */
		if(!isset(self::$head['metas.name.add'])) self::$head['metas.name.add']='';
		self::$head['metas.name.add'].='<meta name="'.$name.'" content="'.h($content).'"/>';
		/* DEV */ return '<div style="color:red;font-size:12pt">Please do not echo HHead::meta()</div>'; /* /DEV */
	}
	
	public static function metaProperty($property,$content){
		/* DEV */
		self::testDisplayed();
		if($property!==h($property)) throw new Exception('Please escape property');
		//if(Springbok::$inError===null && isset(self::$_metasProperty[$property])) throw new Exception('Meta already defined : '.$property);
		//self::$_metasProperty[$property]=true;
		/* /DEV */
		self::$head['metaprop.'.$property]='<meta property="'.$property.'" content="'.h($content).'"/>';
		/* DEV */ return '<div style="color:red;font-size:12pt">Please do not echo HHead::meta()</div>'; /* /DEV */
	}
	
	public static function metaPropertyAdd($property,$content){
		/* DEV */
		self::testDisplayed();
		if($property!==h($property)) throw new Exception('Please escape property');
		/* /DEV */
		if(!isset(self::$head['metas.property.add'])) self::$head['metas.property.add']='';
		self::$head['metas.property.add'].='<meta property="'.$property.'" content="'.h($content).'"/>';
		/* DEV */ return '<div style="color:red;font-size:12pt">Please do not echo HHead::meta()</div>'; /* /DEV */
	}
	
	
	/* DEV */ private static $_IE_started=false; /* /DEV */
	public static function startIeIf($ieVersion,$operator){
		throw new Exception;
		/* DEV */ self::testDisplayed(); /* /DEV */
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
		throw new Exception;
		/* DEV */ self::testDisplayed(); /* /DEV */
		/* DEV */ if(self::$_IE_started!==true) throw new Exception('ie is not started');
		self::$_IE_started=false; /* /DEV */
		self::$head.='<![endif]-->';
		/* DEV */ return '<div style="color:red;font-size:12pt">Please do not echo HHead::endIeIf()</div>'; /* /DEV */
	}
	
	public static function linkCssAndJs($url='/index'){
		/* DEV */ self::testDisplayed(); /* /DEV */
		self::linkCss($url);
		self::linkJs($url);
		/* DEV */ return '<div style="color:red;font-size:12pt">Please do not echo HHead::linkCssAndJs()</div>'; /* /DEV */
	}
	
	public static function linkCss($url='/main',$media=false){
		/* DEV */ self::testDisplayed(); /* /DEV */
		/* DEV */ if(self::$_IE_started===true) throw new Exception('ie is started. Css is not added in IE if'); /* /DEV */
		/* Keep css up */
		self::$head['css'].='<link rel="stylesheet" type="text/css" href="'.HHtml::staticUrl(strpos($url,'?')?$url:($url.'.css'),'css').'"'.($media?' media="'.$media.'"':'').'/>';
		/* DEV */ return '<div style="color:red;font-size:12pt">Please do not echo HHead::linkCss()</div>'; /* /DEV */
	}
	
	public static function linkJs($url='/global'){
		/* DEV */ self::testDisplayed(); /* /DEV */
		if(CHttpUserAgent::isIElt9() && $url!=='/es5-compat'){
			if(substr($url,-4)!=='.min') $url.='.oldIe';
		}
		self::$head['js'].='<script type="text/javascript" src="'.HHtml::staticUrl($url.'.js','js').'"></script>';
		/* DEV */ return '<div style="color:red;font-size:12pt">Please do not echo HHead::linkJs()</div>'; /* /DEV */
	}
	public static function linkAddJs($url='/global'){
		/* DEV */ self::testDisplayed(); /* /DEV */
		self::$head['endjs'].='<script type="text/javascript" src="'.HHtml::staticUrl($url.'.js','js').'"></script>';
		/* DEV */ return '<div style="color:red;font-size:12pt">Please do not echo HHead::linkAddJs()</div>'; /* /DEV */
	}
	public static function linkJsIe($ieVersion,$operator,$url){
		/* DEV */ self::testDisplayed(); /* /DEV */
		self::startIeIf($ieVersion,$operator);
		self::linkJs($url);
		self::endIeIf();
		/* DEV */ return '<div style="color:red;font-size:12pt">Please do not echo HHead::linkJsIe()</div>'; /* /DEV */
	}
	public static function jsI18n(){
		/* DEV */ self::testDisplayed(); /* /DEV */
		self::linkJs('/i18n-'.CLang::get());
		/* DEV */ return '<div style="color:red;font-size:12pt">Please do not echo HHead::jsI18n()</div>'; /* /DEV */
	}
	
	public static function linkRel($rel,$url,$entry=null){
		/* DEV */ self::testDisplayed(); /* /DEV */
		/* DEV */if($rel!==h($rel)) throw new Exception('Please escape rel'); /* /DEV */
		self::$head['linksrel'].='<link rel="'.$rel.'" href="'.HHtml::urlEscape($url,$entry,true).'"/>';
		/* DEV */ return '<div style="color:red;font-size:12pt">Please do not echo HHead::linkRel()</div>'; /* /DEV */
	}
	public static function linkPrev($url,$entry=null){ self::linkRel('prev',$url,$entry);
		/* DEV */ return '<div style="color:red;font-size:12pt">Please do not echo HHead::linkPrev()</div>'; /* /DEV */ }
	public static function linkNext($url,$entry=null){ self::linkRel('next',$url,$entry);
		/* DEV */ return '<div style="color:red;font-size:12pt">Please do not echo HHead::linkNext()</div>'; /* /DEV */ }
	public static function linkSmallSizes($url,$entry=null){
		/* DEV */ self::testDisplayed(); /* /DEV */
		self::$head['linksrel'].='<link rel="alternate" media="only screen and (max-width: 640px)" href="'.HHtml::urlEscape($url,$entry,true).'"/>';
		/* DEV */ return '<div style="color:red;font-size:12pt">Please do not echo HHead::linkSmallSizes()</div>'; /* /DEV */
	}
	public static function linksLangs($altLangs){
		/* DEV */ self::testDisplayed(); /* /DEV */
		foreach($altLangs as $lang=>$url) self::$head['linksrel'].='<link rel="alternate" hreflang="'.$lang.'" href="'.HHtml::urlEscape($url).'"/>';
		/* DEV */ return '<div style="color:red;font-size:12pt">Please do not echo HHead::linksLangs()</div>'; /* /DEV */
	}
	
	public static function linkGoogleWebStore($itemId){
		self::$head['linksrel'].='<link rel="chrome-webstore-item" href="https://chrome.google.com/webstore/detail/'.$itemId.'"/>';
		/* DEV */ return '<div style="color:red;font-size:12pt">Please do not echo HHead::linkGoogleWebStore()</div>'; /* /DEV */
	}
	
	
	/* DEV */
		private static $_displayed=false;
		private static function testDisplayed(){
			if(Springbok::$inError===null && self::$_displayed) throw new Exception('HHead::display() has already been called');
		}
	/* /DEV */
	public static function display(){
		/* DEV */
		if(self::$_IE_started===true) throw new Exception('ie is started');
		if(Springbok::$inError===null && self::$_displayed===true) throw new Exception('Already displayed');
		self::$_displayed=true;
		/* /DEV */
		echo implode('',self::$head);
		/* DEV */ return '<div style="color:red;font-size:12pt">Please do not echo HHead::display()</div>'; /* /DEV */
	}
}

if(CHttpUserAgent::isIElt9()) HHead::linkJs('/es5-compat');
