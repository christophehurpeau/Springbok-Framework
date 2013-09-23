<?php
/**
 * HttpRequest Component
 * 
 * Give infos on the client request
 * 
 * @see $_SERVER
 */
class CHttpRequest{
	private static $method,$query,$pathInfo;

	/** @ignore */
	public static function init(){
		self::$method=isset($_SERVER['REQUEST_METHOD'])?$_SERVER['REQUEST_METHOD']:null;
		self::$query=isset($_SERVER['QUERY_STRING'])?$_SERVER['QUERY_STRING']:null;
		if(isset($_GET['url'])){
			self::$pathInfo=$_GET['url'];
			unset($_GET['url']);
		}
		elseif(!empty($_SERVER['PATH_INFO'])) self::$pathInfo=$_SERVER['PATH_INFO'];
	}

	/**
	 * Which request method was used to access the page; i.e. 'GET', 'HEAD', 'POST', 'PUT'. 
	 * 
	 * @return string
	 * @see $_SERVER['REQUEST_METHOD']
	 */
	public static function getMethod(){
		return self::$method;
	}

	/**
	 * Full url, using url rewriting or $_SERVER['PATH_INFO']
	 * 
	 * @return string
	 * @see $_SERVER['PATH_INFO']
	 */
	public static function getPathInfo(){
		return self::$pathInfo;
	}
	
	/**
	 * The IP address from which the user is viewing the current page.
	 * If the user come from a proxy, checks if it is a trusted one using ACHttpRequest::isTrustedProxy($ip)
	 * 
	 * @return string
	 * @see $_SERVER['HTTP_X_FORWARDED_FOR']
	 * @see $_SERVER['HTTP_CLIENT_IP']
	 * @see $_SERVER['REMOTE_ADDR']
	 */
	public static function getRealClientIP(){
		if(isset($_SERVER['HTTP_X_FORWARDED_FOR']) && ACHttpRequest::isTrustedProxy($_SERVER['REMOTE_ADDR'])){
			$ip = strstr($_SERVER['HTTP_X_FORWARDED_FOR'],',',true);
			return $ip === false ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $ip;
		}elseif(isset($_SERVER['HTTP_CLIENT_IP']) && ACHttpRequest::isTrustedProxy($_SERVER['REMOTE_ADDR'])){
			$ip = strstr($_SERVER['HTTP_CLIENT_IP'],',',true);
			return $ip === false ? $_SERVER['HTTP_CLIENT_IP'] : $ip;
		}
		
		return $_SERVER['REMOTE_ADDR'];
	}
	
	/**
	 * The IP address from which the user is viewing the current page.
	 * 
	 * @return string
	 * @see $_SERVER['REMOTE_ADDR']
	 */
	public static function getClientIP(){
		return $_SERVER['REMOTE_ADDR'];
	}
	
	/**
	 * The URI which was given in order to access this page; for instance, '/index.html'. 
	 * 
	 * @return string
	 * @see $_SERVER['REQUEST_URI']
	 */
	public static function getCurrentUrl(){
		return $_SERVER['REQUEST_URI'];
	}
	
	/**
	 * The request is made with HTTPS
	 * 
	 * @return bool
	 * @see $_SERVER['HTTPS']
	 */
	public static function isHTTPS(){
		return IS_HTTPS;
	}

	/**
	 * The referer of this request
	 * 
	 * @param bool returns only if it's comming from an internal link
	 * @return string
	 * @see $_SERVER['HTTPS']
	 */
	public static function referer($local=false){
		if(!empty($_SERVER['HTTP_X_FORWARDED_HOST'])) $referer = $_SERVER['HTTP_X_FORWARDED_HOST'];
		elseif(isset($_SERVER['HTTP_REFERER'])) $referer = $_SERVER['HTTP_REFERER'];
		else return null;
		if(!$local) return $referer;
		$base = FULL_BASE_URL.BASE_URL;
		$baseLength=strlen($base);
		if(substr($referer,0,$baseLength)===$base) return substr($referer,$baseLength);
		return null;
	}
	
	/**
	 * Returns if it's a GET method
	 * 
	 * @return bool
	 */
	public static function isGET(){return self::$method==='GET';}
	/**
	 * Returns if it's a GET method
	 * 
	 * @return bool
	 */
	public static function isPOST(){return  self::$method==='POST';}
	/**
	 * Returns if it's a POST method
	 * 
	 * @return bool
	 */
	public static function isPUT(){return self::$method==='PUT';}
	/**
	 * Returns if it's a PUT method
	 * 
	 * @return bool
	 */
	public static function isDELETE(){return self::$method==='DELETE';}
	/**
	 * Returns if it's a HEAD method
	 * 
	 * @return bool
	 */
	public static function isHEAD(){return self::$method==='HEAD';}
	/**
	 * Returns if it's a OPTIONS method
	 * 
	 * @return bool
	 */
	public static function isOPTIONS(){return self::$method==='OPTIONS';}
	/**
	 * Returns if it's a Ajax request
	 * 
	 * @return bool
	 */
	public static function isAjax(){return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH']==='XMLHttpRequest') /*#if DEV */|| (isset($_GET['AJAX']) && $_GET['AJAX']==='force') /*#/if*/;}
	/**
	 * Returns if it's a Flash request
	 * 
	 * @return bool
	 */
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

	/**
	 * Contents of the Host: header from the current request, if there is one. 
	 * 
	 * @return string
	 * @see $_SERVER['HTTP_HOST']
	 */
	public static function host(){
		return $_SERVER['HTTP_HOST'];
	}

	/**
	 * $_GET param converted to UTF-8
	 * 
	 * @param string
	 * @return string
	 */
	public static function _GET($name){
		return UEncoding::convertToUtf8($_GET[$name]);
	}
	/**
	 * $_POST param converted to UTF-8
	 * 
	 * @param string
	 * @return string
	 */
	public static function _POST($name){
		return UEncoding::convertToUtf8($_POST[$name]);
	}
	/**
	 * $_GET param converted to UTF-8 or value if !isset
	 * 
	 * @param string
	 * @param mixed
	 * @return mixed
	 */
	public static function _GETor($name,$orValue=null){/* do not change orValue ! */
		return isset($_GET[$name]) ? self::_GET($name) : $orValue;
	}
	
	
	/**
	 * $_GET param converted to int
	 * 
	 * @param string
	 * @return int
	 */
	public static function _GETint($name){
		return (int)($_GET[$name]);
	}
	/**
	 * $_GET param converted to int or value if !isset
	 * 
	 * @param string
	 * @param mixed
	 * @return mixed
	 */
	public static function _GETintOr($name,$orValue=0){
		return isset($_GET[$name]) ? (int)($_GET[$name]) : $orValue;
	}
	
	
	/**
	 * $_GET param converted to UTF-8 or $_POST param converted to UTF-8 or value if !isset
	 * 
	 * @param string
	 * @param mixed
	 * @return mixed
	 */
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