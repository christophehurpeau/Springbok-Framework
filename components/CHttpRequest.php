<?php
class CHttpRequest{
	private static $method,$query,$pathInfo;


	public static function init(){
		self::$method=$_SERVER['REQUEST_METHOD'];
		self::$query=$_SERVER['QUERY_STRING'];
		if(!empty($_GET['url'])){
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
	public static function isAjax(){return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH']==='XMLHttpRequest') /* DEV */|| (isset($_GET['AJAX']) && $_GET['AJAX']==='force') /* /DEV */;}
	public static function isFlash(){return empty($_SERVER['HTTP_USER_AGENT'])? false : (bool)preg_match('/^(Shockwave|Adobe) Flash/',$_SERVER['HTTP_USER_AGENT']);}

	/* http://detectmobilebrowsers.com/ */
	public static function isMobile(){
		if(empty($_SERVER['HTTP_USER_AGENT'])) return false;
		return (bool)preg_match('/android.+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|meego.+mobile|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino|googlebot-mobile/i',$_SERVER['HTTP_USER_AGENT'])
			||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(di|rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($_SERVER['HTTP_USER_AGENT'],0,4));
	}
	
	public static function isMobileOrTablet(){
		if(empty($_SERVER['HTTP_USER_AGENT'])) return false;
		return (bool)preg_match('/android|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(ad|hone|od)|iris|kindle|lge |maemo|meego.+mobile|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino|playbook|silk/i',$_SERVER['HTTP_USER_AGENT'])
			||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(di|rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($_SERVER['HTTP_USER_AGENT'],0,4));
	}

	public static function isMobileAndNotTablet(){
		return self::isMobileOrTablet() && !self::isTablet();
	}
	
	/* stay private */
	private static function isTablet(){
		//if(empty($_SERVER['HTTP_USER_AGENT'])) return false;
		//return (bool)preg_match('/android.+(?!mobile)|android 3\.|opera tablet|playbook|silk|ipad|hp\-tablet|sony tablet|samsung.*tablet|galaxy.*tab/i',$_SERVER['HTTP_USER_AGENT']);
		return ( (preg_match('/android/i',$_SERVER['HTTP_USER_AGENT']) && !preg_match('/android.+mobile/i',$_SERVER['HTTP_USER_AGENT']))
			|| preg_match('/android 3\.|opera tablet|playbook|silk|ipad|hp\-tablet|sony tablet|samsung.*tablet|galaxy.*tab/i',$_SERVER['HTTP_USER_AGENT']));
	}
	
	private static $_isBot;
	public static function isBot(){
		if(self::$_isBot!==null) return self::$_isBot;
		if(empty($_SERVER['HTTP_USER_AGENT'])) return self::$_isBot=true;
		return self::$_isBot=(bool)preg_match('/'./* EVAL implode('|',array(
			'bot',
			//'Googlebot',
			'Google Web Preview', // Google - www.google.com
			'Bing Preview', // Bing
			//'msnbot',
			'Yahoo',
			//'VoilaBot',
			//'WebCrawler',
			'crawler','spider','spyder',
		 
			'Xenu',
		 
			'Scooter', // Alta Vita - www.altavista.com
			//'Ask Jeeves\/Teoma', // Ask - www.ask.com & Teoma - ww.teoma.com
			'Lycos_Spider_\(T-Rex\)', // Lycos - www.lycos.com
			'Slurp', // Inktomi - www.inktomi.com
			'HenryTheMiragorobot', // Mirago - www.mirago.com
			//'FAST\-WebCrawler', // AlltheWeb - www.alltheweb.com
			'W3C_Validator',
			
			'Teoma', 'ia_archiver', //Alexa
			'froogle', 'inktomi',
			'looksmart', 'URL_Spider_SQL', 'Firefly', 'NationalDirectory',
			'Ask Jeeves', 'TECNOSEEK', 'InfoSeek', 
			'www.galaxy.com','appie', 'FAST', 'WebBug', 'Spade', 'ZyBorg', 'rabaz',
			'Baiduspider', 'Feedfetcher-Google', 'TechnoratiSnoop',
			'Mediapartners-Google', 'Sogou web spider',
			'Butterfly','Twitturls','Me.dium','Twiceler'
		)) /EVAL */''.'/i',$_SERVER['HTTP_USER_AGENT']);
	}

	public static function isIElt8(){
		if(!isset($_SERVER['HTTP_USER_AGENT']) || !preg_match("#MSIE ([\d\.]+)#i",$_SERVER['HTTP_USER_AGENT'],$ua)) return false;
		return $ua[1] < 8;
	}

	CONST P_WINDOWS=0,P_MAC=1,P_LINUX=2,P_FREE_BSD=3,P_IPOD=10,P_IPAD=11,P_IPHONE=12,P_ANDROID=13,P_SYMBIAN=14,P_P_IMODE=15,P_NINTENDO_WII=20,P_PLAYSTATION_PORTABLE=21;
	CONST B_CRAWLER=0,B_OPERA_MINI=1,B_OPERA=2,B_IE=3,B_FIREFOX=4,B_CHROME=5,B_CHROMIUM=6,B_SAFARI=7,
		B_EPIPHANY=10,B_FENNEC=11,B_ICEWEASEL=12,B_MINEFIELD=13,B_MINIMO=14,B_FLOCK=15,B_FIREBIRD=16,B_PHOENIX=17,B_CAMINO=18,B_CHIMERA=19,B_THUNDERBIRD=20,B_NETSCAPE=21,B_OMNIWEB=22,B_IRON=23,B_ICAB=24,B_KONQUEROR=25,B_MIDORI=26,B_DOCOMO=27,B_LYNX=28,B_LINKS=29,
		B_W3C_VALIDATOR=30,B_APACHE_BENCH=31,B_LIBWWW_PERL_LIB=32,B_W3M=33,B_WGET=34;
		
	public static function parseUserAgent(){
		$ua=isset($_SERVER['HTTP_USER_AGENT'])?$_SERVER['HTTP_USER_AGENT']:'';
		$browser = array('user_agent'=>&$ua,'platform'=>null,'browser'=>null,'version'=>null);
		if(empty($ua)) return $browser;
		
		// platform
		if(preg_match('/Win/',$ua)) $browser['platform']=self::P_WINDOWS;
		elseif(preg_match('/iPod/',$ua)) $browser['platform']=self::P_IPOD;
		elseif(preg_match('/iPad/',$ua)) $browser['platform']=self::P_IPAD;
		elseif(preg_match('/iPhone/',$ua)) $browser['platform']=self::P_IPHONE;
		elseif(preg_match('/Android/',$ua)) $browser['platform']=self::P_ANDROID;
		elseif(preg_match('/Symbian/',$ua) || preg_match('/SymbOS/',$ua)) $browser['platform']=self::P_SYMBIAN;
		elseif(preg_match('/Nintendo Wii/',$ua)) $browser['platform']=self::P_NINTENDO_WII;
		elseif(preg_match('/PlayStation Portable/',$ua)) $browser['platform']=self::P_PLAYSTATION_PORTABLE;
		elseif(preg_match('/Mac/',$ua)) $browser['platform']=self::P_MAC;
		elseif(preg_match('/Linux/',$ua)) $browser['platform']=self::P_LINUX;
		elseif(preg_match('/FreeBSD/',$ua)) $browser['platform']=self::P_FREE_BSD;
		elseif(preg_match('/DoCoMo/',$ua)) $browser['platform']=self::P_IMODE;
		
		if(preg_match('/charlotte|crawl|bot|bloglines|dtaagent|feedfetcher|ia_archiver|larbin|mediapartners'
			.'|metaspinner|searchmonkey|slurp|spider|teoma|ultraseek|waypath|yacy|yandex/i',$ua)) $browser['platform']=self::B_CRAWLER;
		else{
			$sniffs = array( // name regexp, name for display, version regexp, version match
				array('Opera Mini',self::B_OPERA_MINI, "#Opera Mini( |/)([\d\.]+)#", 2 ),
				array('Opera',self::B_OPERA, "#Version/([\d\.]+)#", 1 ),
				array('Opera',self::B_OPERA, "#Opera( |/)([\d\.]+)#", 2 ),
				array('MSIE',self::B_IE, "#MSIE ([\d\.]+)#", 1 ),
				array('Epiphany',self::B_EPIPHANY, "#Epiphany/([\d\.]+)#",  1 ),
				array('Fennec',self::B_FENNEC, "#Fennec/([\d\.]+)#",  1 ),
				array('Firefox',self::B_FIREFOX, "#Firefox/([\d\.a-z]+)#",  1 ),
				array('Iceweasel',self::B_ICEWEASEL, "#Iceweasel/([\d\.]+)#",  1 ),
				array('Minefield',self::B_MINEFIELD, "#Minefield/([\d\.]+)#",  1 ),
				array('Minimo',self::B_MINIMO, "#Minimo/([\d\.]+)#",  1 ),
				array('Flock',self::B_FLOCK, "#Flock/([\d\.]+)#",  1 ),
				array('Firebird',self::B_FIREBIRD, "#Firebird/([\d\.]+)#", 1 ),
				array('Phoenix',self::B_PHOENIX, "#Phoenix/([\d\.]+)#", 1 ),
				array('Camino',self::B_CAMINO, "#Camino/([\d\.]+)#", 1 ),
				array('Chimera',self::B_CHIMERA, "#Chimera/([\d\.]+)#", 1 ),
				array('Thunderbird',self::B_THUNDERBIRD, "#Thunderbird/([\d\.]+)#",  1 ),
				array('OmniWeb',self::B_OMNIWEB, "#OmniWeb/([\d\.]+)#", 1 ),
				array('Iron',self::B_IRON, "#Iron/([\d\.]+)#", 1 ),
				array('Chrome',self::B_CHROME, "#Chrome/([\d\.]+)#", 1 ),
				array('Chromium',self::B_CHROMIUM, "#Chromium/([\d\.]+)#", 1 ),
				array('Safari',self::B_SAFARI, "#Version/([\d\.]+)#", 1 ),
				array('Safari',self::B_SAFARI, "#Safari/([\d\.]+)#", 1 ),
				array('iCab',self::B_ICAB, "#iCab/([\d\.]+)#", 1 ),
				array('Konqueror',self::B_KONQUEROR, "#Konqueror/([\d\.]+)#", 1),
				array('Midori',self::B_MIDORI, "#Midori/([\d\.]+)#",  1 ),
				array('DoCoMo',self::B_DOCOMO, "#DoCoMo/([\d\.]+)#", 1 ),
				array('Lynx',self::B_LYNX, "#Lynx/([\d\.]+)#", 1 ),
				array('Links',self::B_LINKS, "#\(([\d\.]+)#", 1 ),
				array('W3C_Validator',self::B_W3C_VALIDATOR, "#W3C_Validator/([\d\.]+)#", 1 ),
				array('ApacheBench',self::B_APACHE_BENCH, '#ApacheBench/(.*)$#', 1 ),
				array('lwp-request',self::B_LIBWWW_PERL_LIB,'#lwp-request/(.*)$#', 1 ),
				array('w3m',self::B_W3M, "#w3m/([\d\.]+)#", 1 ),
				array('Wget',self::B_WGET, "#Wget/([\d\.]+)#", 1 )
			);
			
			foreach($sniffs as &$sniff){
				if(strpos($ua,$sniff[0]) !== false){
					$browser['browser'] = $sniff[1];
					if(preg_match($sniff[2], $ua, $b))
						if(isset($b[$sniff[3]])){
							$browser['version'] = $b[ $sniff[3] ];
							break;
						}
				}
			}
		}
		
		if ( $browser['browser'] === null ) {
			if ( preg_match('#Mozilla/4#', $ua ) && strpos('compatible',$ua)===false ) {
				$browser['browser'] = 'Netscape';
				preg_match("#Mozilla/([\d\.]+)#", $ua, $b );
				$browser['version'] = $b[1];
			} elseif ( ( preg_match( '#Mozilla/5#', $ua ) && strpos('compatible',$ua)===false ) || strpos('Gecko',$ua)===false ) {
				if(stripos('Googlebot',$ua)){
					$browser['browser'] = 'Googlebot';
					preg_match( "#Googlebot/([\d\.]+)#U", $ua, $b );
					$browser['version'] = empty($b[2]) ? '' : $b[2];
				}else{
					$browser['browser'] = 'Mozilla';
					preg_match( "#rv(:| )([\d\.]+)#U", $ua, $b );
					$browser['version'] = empty($b[2]) ? '' : $b[2];
				}
			}
		}
		
		// browser version
		if ( $browser['browser'] !== null && $browser['version'] !== null ) {
			// Make sure we have at least .0 for a minor version
			if(strpos($browser['version'],'.')===false ) $browser['version'].='.0';
			preg_match( '#^([0-9]*)\.(.*)$#', $browser['version'], $v );
			$browser['majorver'] = $v[1];
			$browser['minorver'] = $v[2];
		}
		if ( empty( $browser['version'] ) || $browser['version'] == '.0' ) {
			$browser['version'] = null;
			$browser['majorver'] = null;
			$browser['minorver'] = null;
		}
		
		return $browser;
	}

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

	public static function _GETor($name,$orValue=null){/* do not change orValue ! */
		return isset($_GET[$name]) ? $_GET[$name] : $orValue;
	}
	
	public static function _GETorPOSTor($name,$orValue=null){
		return isset($_GET[$name]) ? $_GET[$name] : (isset($_POST[$name]) ? $_POST[$name] : $orValue);
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