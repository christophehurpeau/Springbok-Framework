<?php
class CHttpRequest{
	private static $method,$query,$pathInfo;


	public static function init(){
		self::$method=isset($_SERVER['REQUEST_METHOD'])?$_SERVER['REQUEST_METHOD']:null;
		self::$query=isset($_SERVER['QUERY_STRING'])?$_SERVER['QUERY_STRING']:null;
		if(isset($_GET['url'])){
			self::$pathInfo=$_GET['url'];
			unset($_GET['url']);
		}
		elseif(!empty($_SERVER['PATH_INFO'])) self::$pathInfo=$_SERVER['PATH_INFO'];
	}

	public static function getMethod(){
		return self::$method;
	}

	public static function getPathInfo(){
		return self::$pathInfo;
	}

	public static function getForwardedClientIP(){
		$ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
		if(empty($ip)) return false;
	}
	
	public static function getRealIP(){
		if (isset($_SERVER['HTTP_CLIENT_IP']))
			// Behind proxy
			return $_SERVER['HTTP_CLIENT_IP'];
		elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			// Use first IP address in list
			list($ip)=explode(',',$_SERVER['HTTP_X_FORWARDED_FOR'],2);
			return $ip;
		}
		return $_SERVER['REMOTE_ADDR'];
	}

	public static function getClientIP(){
		return $_SERVER['REMOTE_ADDR'];
	}
	
	public static function getCurrentUrl(){
		return $_SERVER['REQUEST_URI'];
	}

	public static function isHTTPS(){
		return IS_HTTPS;
	}

	public static function referer($local=false){
		if(!empty($_SERVER['HTTP_X_FORWARDED_HOST'])) $referer = $_SERVER['HTTP_X_FORWARDED_HOST'];
		elseif(isset($_SERVER['HTTP_REFERER'])) $referer=$_SERVER['HTTP_REFERER'];
		else return null;
		if(!$local) return $referer;
		$base = FULL_BASE_URL.BASE_URL;
		$baseLength=strlen($base);
		if(substr($referer,0,$baseLength)===$base) return substr($referer,$baseLength);
		return null;
	}


	public static function isGET(){return self::$method==='GET';}
	public static function isPOST(){return  self::$method==='POST';}
	public static function isPUT(){return self::$method==='PUT';}
	public static function isDELETE(){return self::$method==='DELETE';}
	public static function isHEAD(){return self::$method==='HEAD';}
	public static function isOPTIONS(){return self::$method==='OPTIONS';}
	public static function isAjax(){return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH']==='XMLHttpRequest') /*#if DEV */|| (isset($_GET['AJAX']) && $_GET['AJAX']==='force') /*#/if*/;}
	public static function isFlash(){return empty($_SERVER['HTTP_USER_AGENT'])? false : (bool)preg_match('/^(Shockwave|Adobe) Flash/',$_SERVER['HTTP_USER_AGENT']);}

	public static function parseReferer(){
		$r=( isset( $_SERVER['HTTP_REFERER'] ) ) ? $_SERVER['HTTP_REFERER'] : '';
		if(empty($r)) return false;
		$url=parse_url($r);
		return array('referer'=>$r,'referer_domain'=>empty($url['domain'])?null:preg_replace('#^www\.#','',$url['host']),'searchTerms'=>self::getSearchTerms($url));
	}

	public static function getSearchTerms($url){
		$searchTerms = '';
		
		if(isset($url['host'] ) && isset($url['query'] ) ) {
			$sniffs = array( // host regexp, query portion containing search terms, parameterised url to decode
				array( "/images\.google\./i", 'q', 'prev' ),
				array( "/google\./i", 'q' ),
				array( "/\.bing\./i", 'q' ),
				array( "/alltheweb\./i", 'q' ),
				array( "/yahoo\./i", 'p' ),
				array( "/search\.aol\./i", 'query' ),
				array( "/search\.cs\./i", 'query' ),
				array( "/search\.netscape\./i", 'query' ),
				array( "/hotbot\./i", 'query' ),
				array( "/search\.msn\./i", 'q' ),
				array( "/altavista\./i", 'q' ),
				array( "/web\.ask\./i", 'q' ),
				array( "/search\.wanadoo\./i", 'q' ),
				array( "/www\.bbc\./i", 'q' ),
				array( "/tesco\.net/i", 'q' ),
				array( "/yandex\./i", 'text' ),
				array( "/rambler\./i", 'words' ),
				array( "/aport\./i", 'r' ),
				array( "/.*/", 'query' ),
				array( "/.*/", 'q' )
			);
			
			foreach($sniffs as $sniff) {
				if( preg_match( $sniff[0], $url['host'] ) ) {
					parse_str( $url['query'], $q );
					
					if(isset($sniff[2]) && isset($q[$sniff[2]]) ){
						$decoded_url = parse_url( $q[$sniff[2]] );
						if(isset($decoded_url['query'])) parse_str( $decoded_url['query'], $q );
					}
					
					if(isset($q[$sniff[1]])){
						$searchTerms=trim(stripslashes($q[$sniff[1]]));
						break;
					}
				}
			}
		}
		
		return $searchTerms;
	}

	public static function host(){
		return $_SERVER['HTTP_HOST'];
	}


	public static function _GET($name){
		return UEncoding::convertToUtf8($_GET[$name]);
	}
	public static function _POST($name){
		return UEncoding::convertToUtf8($_POST[$name]);
	}
	public static function _GETor($name,$orValue=null){/* do not change orValue ! */
		return isset($_GET[$name]) ? self::_GET($name) : $orValue;
	}
	
	
	public static function _GETint($name){
		return (int)($_GET[$name]);
	}
	public static function _GETintOr($name,$orValue=0){
		return isset($_GET[$name]) ? (int)($_GET[$name]) : $orValue;
	}
	
	
	public static function _GETorPOSTor($name,$orValue=null){
		return isset($_GET[$name]) ? self::_GET($name) : (isset($_POST[$name]) ? self::_POST($name) : $orValue);
	}
	
	
	public static function buildQuery(){
		return empty($_GET)?'':'?'.http_build_query($_GET);
	}

	public static function accepts($type=null){
		if(empty($_SERVER['HTTP_ACCEPT'])) return false;
		$acceptTypes=explode(',',$_SERVER['HTTP_ACCEPT']);
		foreach($acceptTypes as $k => &$accept){
			if(strpos($accept, ';') !== false){
				list($accept, $prefValue) = explode(';',$accept,2);
				$acceptTypes[$k] = $accept;
			}
		}
		if(is_string($type)) return in_array($type,$acceptTypes);
		elseif(is_array($type)){
			foreach($type as $t)
				if(in_array($t,$acceptTypes)) return $t;
			return false;
		}
		return $acceptTypes;
	}

	public static function acceptsByExtOrHttpAccept(){
		$acceptTypes=func_get_args();
		$ext=CRoute::getExt();
		if($ext && in_array($ext,$acceptTypes)) return $ext;
		return CHttpRequest::accepts($acceptTypes);
	}

	public static function acceptLanguage($language=null){
		$accepts = explode(',',$_SERVER['HTTP_ACCEPT_LANGUAGE']);
		foreach($accepts as $k => &$accept){
			$accept = strtolower($accept);
			if (strpos($accept, ';') !== false){
				list($accept, $prefValue) = explode(';',$accept,2);
				$acceptTypes[$k] = $accept;
			}
			if (strpos($accept, '_') !== false){
				$accept = str_replace('_', '-', $accept);
			}
		}
		if ($language) return in_array($language, $accepts);
		return $accepts;
	}
}
CHttpRequest::init();