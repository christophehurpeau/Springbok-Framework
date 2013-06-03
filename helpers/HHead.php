<?php
class HHead{
	private static $head=array('title'=>'','icons'=>'','css'=>'','js'=>'','endjs'=>'','linksrel'=>'');
	
	
	public static function title($title){
		if($title===null) return;
		/*#if DEV */ self::testDisplayed(); /*#/if*/
		self::$head['title']='<title>'.h($title).'</title>';
		/*#if DEV */ return '<div style="color:red;font-size:12pt">Please do not echo HHead::title()</div>'; /*#/if*/
	}
	public static function favicon($imgUrl='favicon.png'){
		/*#if DEV */ self::testDisplayed(); /*#/if*/
		$href=STATIC_URL.'img/'.$imgUrl;
		self::$head['icons']='<link rel="icon" type="image/vnd.microsoft.icon" href="'.$href.'"/>'
			.'<link rel="shortcut icon" type="image/x-icon" href="'.$href.'"/>';
		/*#if DEV */ return '<div style="color:red;font-size:12pt">Please do not echo HHead::favicon()</div>'; /*#/if*/
	}
	public static function icons($imgNamePrefix='logo'){
		/*#if DEV */ self::testDisplayed(); /*#/if*/
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
		/*#if DEV */ return '<div style="color:red;font-size:12pt">Please do not echo HHead::logoMobile()</div>'; /*#/if*/
	}
	
	public static function metaName($name,$content){
		/*#if DEV */
		self::testDisplayed();
		if($name!==h($name)) throw new Exception('Please escape name');
		//if(Springbok::$inError===null && isset(self::$_metasName[$name])) throw new Exception('Meta already defined : '.$name);
		//self::$_metasName[$name]=true;
		/*#/if*/
		self::$head['metaname.'.$name]='<meta name="'.$name.'" content="'.h($content).'"/>';
		/*#if DEV */ return '<div style="color:red;font-size:12pt">Please do not echo HHead::meta()</div>'; /*#/if*/
	}
	
	public static function metaNameAdd($name,$content){
		/*#if DEV */
		self::testDisplayed();
		if($name!==h($name)) throw new Exception('Please escape name');
		/*#/if*/
		if(!isset(self::$head['metas.name.add'])) self::$head['metas.name.add']='';
		self::$head['metas.name.add'].='<meta name="'.$name.'" content="'.h($content).'"/>';
		/*#if DEV */ return '<div style="color:red;font-size:12pt">Please do not echo HHead::meta()</div>'; /*#/if*/
	}
	
	public static function metaProperty($property,$content){
		/*#if DEV */
		self::testDisplayed();
		if($property!==h($property)) throw new Exception('Please escape property');
		//if(Springbok::$inError===null && isset(self::$_metasProperty[$property])) throw new Exception('Meta already defined : '.$property);
		//self::$_metasProperty[$property]=true;
		/*#/if*/
		self::$head['metaprop.'.$property]='<meta property="'.$property.'" content="'.h($content).'"/>';
		/*#if DEV */ return '<div style="color:red;font-size:12pt">Please do not echo HHead::meta()</div>'; /*#/if*/
	}
	
	public static function metaPropertyAdd($property,$content){
		/*#if DEV */
		self::testDisplayed();
		if($property!==h($property)) throw new Exception('Please escape property');
		/*#/if*/
		if(!isset(self::$head['metas.property.add'])) self::$head['metas.property.add']='';
		self::$head['metas.property.add'].='<meta property="'.$property.'" content="'.h($content).'"/>';
		/*#if DEV */ return '<div style="color:red;font-size:12pt">Please do not echo HHead::meta()</div>'; /*#/if*/
	}
	
	
	/*#if DEV */ private static $_IE_started=false; /*#/if*/
	public static function startIeIf($ieVersion,$operator){
		throw new Exception;
		/*#if DEV */ self::testDisplayed(); /*#/if*/
		/*#if DEV */ self::$_IE_started=true; /*#/if*/
		self::$head.='<!--[if IE';
		if(!empty($ieVersion)){
			switch($operator){
				case '=': break;
				case '<': self::$head.=' lt'; break;
				case '>': self::$head.=' gt'; break;
				case '<=': self::$head.=' lte'; break;
				case '>=': self::$head.=' gte'; break;
				/*#if DEV */default: throw new Exception('Unknown operator: '.$operator);/*#/if*/
			}
		}
		self::$head.=']>';
		/*#if DEV */ return '<div style="color:red;font-size:12pt">Please do not echo HHead::startIeIf()</div>'; /*#/if*/
	}
	public static function endIeIf(){
		throw new Exception;
		/*#if DEV */ self::testDisplayed(); /*#/if*/
		/*#if DEV */ if(self::$_IE_started!==true) throw new Exception('ie is not started');
		self::$_IE_started=false; /*#/if*/
		self::$head.='<![endif]-->';
		/*#if DEV */ return '<div style="color:red;font-size:12pt">Please do not echo HHead::endIeIf()</div>'; /*#/if*/
	}
	
	public static function linkCssAndJs($url='/index'){
		/*#if DEV */ self::testDisplayed(); /*#/if*/
		self::linkCss($url);
		self::linkJs($url);
		/*#if DEV */ return '<div style="color:red;font-size:12pt">Please do not echo HHead::linkCssAndJs()</div>'; /*#/if*/
	}
	
	public static function linkCss($url='/main',$media=false){
		/*#if DEV */ self::testDisplayed(); /*#/if*/
		/*#if DEV */ if(self::$_IE_started===true) throw new Exception('ie is started. Css is not added in IE if'); /*#/if*/
		/* Keep css up */
		self::$head['css'].='<link rel="stylesheet" type="text/css" href="'.HHtml::staticUrl(strpos($url,'?')?$url:($url.'.css'),'css').'"'.($media?' media="'.$media.'"':'').'/>';
		/*#if DEV */ return '<div style="color:red;font-size:12pt">Please do not echo HHead::linkCss()</div>'; /*#/if*/
	}
	
	public static function linkJs($url='/global'){
		/*#if DEV */ self::testDisplayed(); /*#/if*/
		if(CHttpUserAgent::isIElt9() && $url!=='/es5-compat'){
			if(substr($url,-4)!=='.min') $url.='.oldIe';
		}
		self::$head['js'].='<script type="text/javascript" src="'.HHtml::staticUrl($url.'.js','js').'"></script>';
		/*#if DEV */ return '<div style="color:red;font-size:12pt">Please do not echo HHead::linkJs()</div>'; /*#/if*/
	}
	public static function linkAddJs($url='/global'){
		/*#if DEV */ self::testDisplayed(); /*#/if*/
		self::$head['endjs'].='<script type="text/javascript" src="'.HHtml::staticUrl($url.'.js','js').'"></script>';
		/*#if DEV */ return '<div style="color:red;font-size:12pt">Please do not echo HHead::linkAddJs()</div>'; /*#/if*/
	}
	public static function linkJsIe($ieVersion,$operator,$url){
		/*#if DEV */ self::testDisplayed(); /*#/if*/
		self::startIeIf($ieVersion,$operator);
		self::linkJs($url);
		self::endIeIf();
		/*#if DEV */ return '<div style="color:red;font-size:12pt">Please do not echo HHead::linkJsIe()</div>'; /*#/if*/
	}
	public static function jsI18n(){
		/*#if DEV */ self::testDisplayed(); /*#/if*/
		self::linkJs('/i18n-'.CLang::get());
		/*#if DEV */ return '<div style="color:red;font-size:12pt">Please do not echo HHead::jsI18n()</div>'; /*#/if*/
	}
	
	public static function linkRel($rel,$url,$entry=null){
		/*#if DEV */ self::testDisplayed(); /*#/if*/
		/*#if DEV */if($rel!==h($rel)) throw new Exception('Please escape rel'); /*#/if*/
		self::$head['linksrel'].='<link rel="'.$rel.'" href="'.HHtml::urlEscape($url,$entry,true).'"/>';
		/*#if DEV */ return '<div style="color:red;font-size:12pt">Please do not echo HHead::linkRel()</div>'; /*#/if*/
	}
	public static function linkPrev($url,$entry=null){ self::linkRel('prev',$url,$entry);
		/*#if DEV */ return '<div style="color:red;font-size:12pt">Please do not echo HHead::linkPrev()</div>'; /*#/if*/ }
	public static function linkNext($url,$entry=null){ self::linkRel('next',$url,$entry);
		/*#if DEV */ return '<div style="color:red;font-size:12pt">Please do not echo HHead::linkNext()</div>'; /*#/if*/ }
	public static function linkSmallSizes($url,$entry=null){
		/*#if DEV */ self::testDisplayed(); /*#/if*/
		self::$head['linksrel'].='<link rel="alternate" media="only screen and (max-width: 640px)" href="'.HHtml::urlEscape($url,$entry,true).'"/>';
		/*#if DEV */ return '<div style="color:red;font-size:12pt">Please do not echo HHead::linkSmallSizes()</div>'; /*#/if*/
	}
	public static function linksLangs($altLangs){
		/*#if DEV */ self::testDisplayed(); /*#/if*/
		foreach($altLangs as $lang=>$url) self::$head['linksrel'].='<link rel="alternate" hreflang="'.$lang.'" href="'.HHtml::urlEscape($url).'"/>';
		/*#if DEV */ return '<div style="color:red;font-size:12pt">Please do not echo HHead::linksLangs()</div>'; /*#/if*/
	}
	
	public static function linkGoogleWebStore($itemId){
		self::$head['linksrel'].='<link rel="chrome-webstore-item" href="https://chrome.google.com/webstore/detail/'.$itemId.'"/>';
		/*#if DEV */ return '<div style="color:red;font-size:12pt">Please do not echo HHead::linkGoogleWebStore()</div>'; /*#/if*/
	}
	
	
	/*#if DEV */
		private static $_displayed=false;
		private static function testDisplayed(){
			if(Springbok::$inError===null && self::$_displayed) throw new Exception('HHead::display() has already been called');
		}
	/*#/if*/
	public static function display(){
		/*#if DEV */
		if(self::$_IE_started===true) throw new Exception('ie is started');
		if(Springbok::$inError===null && self::$_displayed===true) throw new Exception('Already displayed');
		self::$_displayed=true;
		/*#/if*/
		echo implode('',self::$head);
		/*#if DEV */ return '<div style="color:red;font-size:12pt">Please do not echo HHead::display()</div>'; /*#/if*/
	}
}

if(CHttpUserAgent::isIElt9()) HHead::linkJs('/es5-compat');
