<?php
/** 
 * Return infos on User Agent
 */
class CHttpUserAgent{
	private static $ieVersion,$isBot,$isMobileOrTablet,$isMobile,$isTablet;
	
	public static function _reset(){
		self::$ieVersion=self::$isBot=self::$isMobileOrTablet=self::$isMobile=self::$isTablet=null;
	}
	
	/** 
	 * Return if the User Agent is a known or guessable bot
	 * 
	 * @return bool
	 */
	public static function isBot(){
		if(self::$isBot!==null) return self::$isBot;
		if(empty($_SERVER['HTTP_USER_AGENT'])) return self::$isBot=true;
		return self::$isBot=(bool)preg_match('/'./* EVAL implode('|',array(
			'bot',
			//'Googlebot',
			'Google Web Preview', // Google - www.google.com
			'Bing Preview', // Bing
			'msnptc',//'msnbot',
			'Yahoo',
			//'VoilaBot',
			//'WebCrawler',
			'crawler','spider','spyder',
			'facebookexternalhit',
		 
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
			'Butterfly','Twitturls','Me.dium','Twiceler',
			'SiteSucker'
		)) /EVAL */''.'/i',$_SERVER['HTTP_USER_AGENT']);
	}
	
	/**
	 * Return the major IE version or false if this is not IE
	 * 
	 * @return int
	 */
	public static function IE_version(){
		if(self::$ieVersion!==null) return self::$ieVersion;
		if(!isset($_SERVER['HTTP_USER_AGENT']) || !preg_match("#MSIE ([\d\.]+)#i",$_SERVER['HTTP_USER_AGENT'],$ua)) return self::$ieVersion=false;
		return self::$ieVersion=$ua[1];
	}
	
	/**
	 * Return if this is IE < 8
	 * 
	 * @return bool
	 */
	public static function isIElt8(){ return self::IE_version()===false ? false : self::$ieVersion < 8; }
	/**
	 * Return if this is IE < 9
	 * 
	 * @return bool
	 */
	public static function isIElt9(){ return self::IE_version()===false ? false : self::$ieVersion < 9; }
	/**
	 * Return if this is IE < 10
	 * 
	 * @return bool
	 */
	public static function isIElt10(){ return self::IE_version()===false ? false : self::$ieVersion < 10; }
	
	
	
	/**
	 * Try to detect if it is a mobile browser
	 * 
	 * @return bool
	 * @see http://detectmobilebrowsers.com/
	 */
	private static function isMobile(){
		if(self::$isMobile!==null) return self::$isMobile;
		if(empty($_SERVER['HTTP_USER_AGENT'])) return self::$isMobile=false;
		
		/* https://github.com/serbanghita/Mobile-Detect/blob/master/Mobile_Detect.php */
		/* Http Headers detection */
		
		if(
			isset($_SERVER['HTTP_ACCEPT']) &&
				(strpos($_SERVER['HTTP_ACCEPT'], 'application/x-obml2d') !== false || // Opera Mini; @reference: http://dev.opera.com/articles/view/opera-binary-markup-language/
				 strpos($_SERVER['HTTP_ACCEPT'], 'application/vnd.rim.html') !== false || // BlackBerry devices.
				 strpos($_SERVER['HTTP_ACCEPT'], 'text/vnd.wap.wml') !== false ||
				 strpos($_SERVER['HTTP_ACCEPT'], 'application/vnd.wap.xhtml+xml') !== false) ||
			isset($_SERVER['HTTP_X_WAP_PROFILE']) || // @todo: validate
			isset($_SERVER['HTTP_X_WAP_CLIENTID']) ||
			isset($_SERVER['HTTP_WAP_CONNECTION']) ||
			isset($_SERVER['HTTP_PROFILE']) ||
			isset($_SERVER['HTTP_X_OPERAMINI_PHONE_UA']) || // Reported by Nokia devices (eg. C3)
			isset($_SERVER['HTTP_X_NOKIA_IPADDRESS']) ||
			isset($_SERVER['HTTP_X_NOKIA_GATEWAY_ID']) ||
			isset($_SERVER['HTTP_X_ORANGE_ID']) ||
			isset($_SERVER['HTTP_X_VODAFONE_3GPDPCONTEXT']) ||
			isset($_SERVER['HTTP_X_HUAWEI_USERID']) ||
			isset($_SERVER['HTTP_UA_OS']) || // Reported by Windows Smartphones.
			isset($_SERVER['HTTP_X_MOBILE_GATEWAY']) || // Reported by Verizon, Vodafone proxy system.
			isset($_SERVER['HTTP_X_ATT_DEVICEID']) || // Seend this on HTC Sensation. @ref: SensationXE_Beats_Z715e
			//HTTP_X_NETWORK_TYPE = WIFI
			( isset($_SERVER['HTTP_UA_CPU']) &&
					$_SERVER['HTTP_UA_CPU'] == 'ARM' // Seen this on a HTC.
			)
		)
		return self::$isMobile=true;
		
		$ua=$_SERVER['HTTP_USER_AGENT'];
		
		// need to be first : Nexus 7 must be detected before Android
		if(preg_match(/* EVAL '/'.implode('|',array(
			'\biPhone.*Mobile|\biPod|\biTunes', //iPhone
			'Nexus (One|S|7)|Galaxy.*Nexus|Android.*Nexus.*Mobile', // Nexus
		)).'/i' /EVAL */'',$ua,$m)){
			self::$isTablet=!empty($m[1]) && $m[1]==='7';
			return self::$isMobile=true;
		}
		
		// mozilla.+(\s+|\()mobile; : firefox os
		if(preg_match(/* EVAL '/'.implode('|',array(
				'android;? (tablet|3\.)?',
				'mozilla.+(?:\s+|\()(mobile|tablet);',
				'opera (m(?:ob|in)i|tablet)'
		)).'/i' /EVAL */'',$ua,$m)){
			if(!empty($m[1])
				 || (isset($m[2])&&$m[2]==='tablet')
				 || (isset($m[3])&&$m[3]==='tablet')
			) self::$isTablet=true;
			return self::$isMobile=true;
		}
		
		// detect non-mobile browsers
		if(preg_match('/(compatible;)? MSIE ([6-9]|10).*Windows NT|(like )?(Mac OS|Mac_PowerPC|Macintosh|Windows NT|Linux).*(Firefox|Chrome)/i',$ua,$m)
						&& empty($m[1]) && empty($m[3]) && !preg_match('/mobile/i',$ua)) return self::$isMobile=false;
		
		
		$tabletDetection=self::_detectTablet($ua);
		if($tabletDetection!==null) return $tabletDetection;
		
		// os detections | browsers
		if(preg_match(/* EVAL '#'.implode('|',array(
				'blackberry|\bBB10\b|rim tablet os|rim[0-9]+',
				'PalmOS|avantgo|blazer|elaine|hiptop|palm|plucker|xiino',
				'Symbian|SymbOS|Series60|Series40|SYB-[0-9]+|\bS60\b',
				'Windows CE.*(PPC|Smartphone|Mobile|[0-9]{3}x[0-9]{3})|Window Mobile|Windows Phone [0-9.]+|WCE;',
				'Windows Phone OS|XBLWP7|ZuneWP7',
				'MeeGo',
				'Maemo',
				'J2ME/|Java/|\bMIDP\b|\bCLDC\b',
				'webOS|hpwOS',
				'\bBada\b',
				'BREW',
				
				'\bCrMo\b|CriOS',
				'\bDolfin\b',
				'\bOPR/[0-9.]+',
				'Skyfire',
				'IEMobile|MSIEMobile',
				'bolt',
				'teashark',
				'Blazer',
				'Version.*Mobile.*Safari|Safari.*Mobile',
				'Tizen',
				'UC.*Browser|UCWEB',
				'DiigoBrowser',
				'MobileExplorer',
				'Puffin',
				'\bMercury\b',
				'NokiaBrowser|OviBrowser|OneBrowser|TwonkyBeamBrowser|SEMC.*Browser|FlyFlow|Minimo|NetFront|Novarra-Vision',
		)).'#i' /EVAL */'',$ua))
			return self::$isMobile=true;
		
		// phone quick detections
		if(preg_match('/'./* EVAL implode('|',array(
			'\bHTC[\b_]',
			'Dell.*(Streak|Aero|Venue|Flash|Smoke|Mini 3iX)\b', // Dell
			'Samsung',
			'\bLG[;\-]',
			'sony',
			'Asus.*Galaxy',
			'Palm',
			'Vertu',
			'Pantech',
			//GenericPhone
			'Tapatalk|PDA;|SAGEM|mmp|pocket|psp|Smartphone|smartfon|treo|up.browser|up.link|vodafone|wap|nokia|MAUI.*WAP.*Browser'
		)) /EVAL */''.'/i',$ua))
			return self::$isMobile=true;
		
		// phone detections complex
		if(preg_match(/* EVAL '/'.implode('|',array(
			'APX515CKT|Qtek9090|APA9292KT|HD_mini|Sensation.*Z710e|PG86100|Z715e|Desire.*(A8181|HD)|ADR6200|ADR6425|001HT|Inspire 4G', //HTC
			'Nexus (One|S)|Galaxy.*Nexus|Android.*Nexus.*Mobile', // Nexus
			'XCD28|XCD35|\b001DL\b|\b101DL\b|\bGS01\b', // Dell
			// Motorola
			'\bDroid\b.*Build|DROIDX|HRI39|MOT-|A1260|A1680|A555|A853|A855|A953|A955|A956|Motorola.*ELECTRIFY|Motorola.*i1|i867|i940|MB200|MB300|MB501|MB502|MB508|MB511|MB520|MB525|MB526|MB611|MB612|MB632|MB810|MB855|MB860|MB861|MB865|MB870|ME501|ME502|ME511|ME525|ME600|ME632|ME722|ME811|ME860|ME863|ME865|MT620|MT710|MT716|MT720|MT810|MT870|MT917|Motorola.*TITANIUM|WX435|WX445|XT300|XT301|XT311|XT316|XT317|XT319|XT320|XT390|XT502|XT530|XT531|XT532|XT535|XT603|XT610|XT611|XT615|XT681|XT701|XT702|XT711|XT720|XT800|XT806|XT860|XT862|XT875|XT882|XT883|XT894|XT909|XT910|XT912|XT928',
			// Samsung
			'BGT-S5230|GT-B2100|GT-B2700|GT-B2710|GT-B3210|GT-B3310|GT-B3410|GT-B3730|GT-B3740|GT-B5510|GT-B5512|GT-B5722|GT-B6520|GT-B7300|GT-B7320|GT-B7330|GT-B7350|GT-B7510|GT-B7722|GT-B7800|GT-C3010|GT-C3011|GT-C3060|GT-C3200|GT-C3212|GT-C3212I|GT-C3262|GT-C3222|GT-C3300|GT-C3300K|GT-C3303|GT-C3303K|GT-C3310|GT-C3322|GT-C3330|GT-C3350|GT-C3500|GT-C3510|GT-C3530|GT-C3630|GT-C3780|GT-C5010|GT-C5212|GT-C6620|GT-C6625|GT-C6712|GT-E1050|GT-E1070|GT-E1075|GT-E1080|GT-E1081|GT-E1085|GT-E1087|GT-E1100|GT-E1107|GT-E1110|GT-E1120|GT-E1125|GT-E1130|GT-E1160|GT-E1170|GT-E1175|GT-E1180|GT-E1182|GT-E1200|GT-E1210|GT-E1225|GT-E1230|GT-E1390|GT-E2100|GT-E2120|GT-E2121|GT-E2152|GT-E2220|GT-E2222|GT-E2230|GT-E2232|GT-E2250|GT-E2370|GT-E2550|GT-E2652|GT-E3210|GT-E3213|GT-I5500|GT-I5503|GT-I5700|GT-I5800|GT-I5801|GT-I6410|GT-I6420|GT-I7110|GT-I7410|GT-I7500|GT-I8000|GT-I8150|GT-I8160|GT-I8320|GT-I8330|GT-I8350|GT-I8530|GT-I8700|GT-I8703|GT-I8910|GT-I9000|GT-I9001|GT-I9003|GT-I9010|GT-I9020|GT-I9023|GT-I9070|GT-I9100|GT-I9103|GT-I9220|GT-I9250|GT-I9300|GT-I9300 |GT-M3510|GT-M5650|GT-M7500|GT-M7600|GT-M7603|GT-M8800|GT-M8910|GT-N7000|GT-S3110|GT-S3310|GT-S3350|GT-S3353|GT-S3370|GT-S3650|GT-S3653|GT-S3770|GT-S3850|GT-S5210|GT-S5220|GT-S5229|GT-S5230|GT-S5233|GT-S5250|GT-S5253|GT-S5260|GT-S5263|GT-S5270|GT-S5300|GT-S5330|GT-S5350|GT-S5360|GT-S5363|GT-S5369|GT-S5380|GT-S5380D|GT-S5560|GT-S5570|GT-S5600|GT-S5603|GT-S5610|GT-S5620|GT-S5660|GT-S5670|GT-S5690|GT-S5750|GT-S5780|GT-S5830|GT-S5839|GT-S6102|GT-S6500|GT-S7070|GT-S7200|GT-S7220|GT-S7230|GT-S7233|GT-S7250|GT-S7500|GT-S7530|GT-S7550|GT-S7562|GT-S8000|GT-S8003|GT-S8500|GT-S8530|GT-S8600|SCH-A310|SCH-A530|SCH-A570|SCH-A610|SCH-A630|SCH-A650|SCH-A790|SCH-A795|SCH-A850|SCH-A870|SCH-A890|SCH-A930|SCH-A950|SCH-A970|SCH-A990|SCH-I100|SCH-I110|SCH-I400|SCH-I405|SCH-I500|SCH-I510|SCH-I515|SCH-I600|SCH-I730|SCH-I760|SCH-I770|SCH-I830|SCH-I910|SCH-I920|SCH-LC11|SCH-N150|SCH-N300|SCH-R100|SCH-R300|SCH-R351|SCH-R400|SCH-R410|SCH-T300|SCH-U310|SCH-U320|SCH-U350|SCH-U360|SCH-U365|SCH-U370|SCH-U380|SCH-U410|SCH-U430|SCH-U450|SCH-U460|SCH-U470|SCH-U490|SCH-U540|SCH-U550|SCH-U620|SCH-U640|SCH-U650|SCH-U660|SCH-U700|SCH-U740|SCH-U750|SCH-U810|SCH-U820|SCH-U900|SCH-U940|SCH-U960|SCS-26UC|SGH-A107|SGH-A117|SGH-A127|SGH-A137|SGH-A157|SGH-A167|SGH-A177|SGH-A187|SGH-A197|SGH-A227|SGH-A237|SGH-A257|SGH-A437|SGH-A517|SGH-A597|SGH-A637|SGH-A657|SGH-A667|SGH-A687|SGH-A697|SGH-A707|SGH-A717|SGH-A727|SGH-A737|SGH-A747|SGH-A767|SGH-A777|SGH-A797|SGH-A817|SGH-A827|SGH-A837|SGH-A847|SGH-A867|SGH-A877|SGH-A887|SGH-A897|SGH-A927|SGH-B100|SGH-B130|SGH-B200|SGH-B220|SGH-C100|SGH-C110|SGH-C120|SGH-C130|SGH-C140|SGH-C160|SGH-C170|SGH-C180|SGH-C200|SGH-C207|SGH-C210|SGH-C225|SGH-C230|SGH-C417|SGH-C450|SGH-D307|SGH-D347|SGH-D357|SGH-D407|SGH-D415|SGH-D780|SGH-D807|SGH-D980|SGH-E105|SGH-E200|SGH-E315|SGH-E316|SGH-E317|SGH-E335|SGH-E590|SGH-E635|SGH-E715|SGH-E890|SGH-F300|SGH-F480|SGH-I200|SGH-I300|SGH-I320|SGH-I550|SGH-I577|SGH-I600|SGH-I607|SGH-I617|SGH-I627|SGH-I637|SGH-I677|SGH-I700|SGH-I717|SGH-I727|SGH-i747M|SGH-I777|SGH-I780|SGH-I827|SGH-I847|SGH-I857|SGH-I896|SGH-I897|SGH-I900|SGH-I907|SGH-I917|SGH-I927|SGH-I937|SGH-I997|SGH-J150|SGH-J200|SGH-L170|SGH-L700|SGH-M110|SGH-M150|SGH-M200|SGH-N105|SGH-N500|SGH-N600|SGH-N620|SGH-N625|SGH-N700|SGH-N710|SGH-P107|SGH-P207|SGH-P300|SGH-P310|SGH-P520|SGH-P735|SGH-P777|SGH-Q105|SGH-R210|SGH-R220|SGH-R225|SGH-S105|SGH-S307|SGH-T109|SGH-T119|SGH-T139|SGH-T209|SGH-T219|SGH-T229|SGH-T239|SGH-T249|SGH-T259|SGH-T309|SGH-T319|SGH-T329|SGH-T339|SGH-T349|SGH-T359|SGH-T369|SGH-T379|SGH-T409|SGH-T429|SGH-T439|SGH-T459|SGH-T469|SGH-T479|SGH-T499|SGH-T509|SGH-T519|SGH-T539|SGH-T559|SGH-T589|SGH-T609|SGH-T619|SGH-T629|SGH-T639|SGH-T659|SGH-T669|SGH-T679|SGH-T709|SGH-T719|SGH-T729|SGH-T739|SGH-T746|SGH-T749|SGH-T759|SGH-T769|SGH-T809|SGH-T819|SGH-T839|SGH-T919|SGH-T929|SGH-T939|SGH-T959|SGH-T989|SGH-U100|SGH-U200|SGH-U800|SGH-V205|SGH-V206|SGH-X100|SGH-X105|SGH-X120|SGH-X140|SGH-X426|SGH-X427|SGH-X475|SGH-X495|SGH-X497|SGH-X507|SGH-X600|SGH-X610|SGH-X620|SGH-X630|SGH-X700|SGH-X820|SGH-X890|SGH-Z130|SGH-Z150|SGH-Z170|SGH-ZX10|SGH-ZX20|SHW-M110|SPH-A120|SPH-A400|SPH-A420|SPH-A460|SPH-A500|SPH-A560|SPH-A600|SPH-A620|SPH-A660|SPH-A700|SPH-A740|SPH-A760|SPH-A790|SPH-A800|SPH-A820|SPH-A840|SPH-A880|SPH-A900|SPH-A940|SPH-A960|SPH-D600|SPH-D700|SPH-D710|SPH-D720|SPH-I300|SPH-I325|SPH-I330|SPH-I350|SPH-I500|SPH-I600|SPH-I700|SPH-L700|SPH-M100|SPH-M220|SPH-M240|SPH-M300|SPH-M305|SPH-M320|SPH-M330|SPH-M350|SPH-M360|SPH-M370|SPH-M380|SPH-M510|SPH-M540|SPH-M550|SPH-M560|SPH-M570|SPH-M580|SPH-M610|SPH-M620|SPH-M630|SPH-M800|SPH-M810|SPH-M850|SPH-M900|SPH-M910|SPH-M920|SPH-M930|SPH-N100|SPH-N200|SPH-N240|SPH-N300|SPH-N400|SPH-Z400|SWC-E100|SCH-i909|GT-N7100',
			// LG
			'C800|C900|E400|E610|E900|E-900|F160|F180K|F180L|F180S|[A-Z]730|[A-Z]855|L160|LS840|LS970|LU6200|MS690|MS695|MS770|MS840|MS870|MS910|P500|P700|P705|VM696|AS680|AS695|AX840|C729|E970|GS505|272|C395|E739BK|E960|L55C|L75C|LS696|LS860|P769BK|P350|P870|UN272|US730|VS840|VS950|LN272|LN510|LS670|LS855|LW690|MN270|MN510|P509|P769|P930|UN200|UN270|UN510|UN610|US670|US740|US760|UX265|UX840|VN271|VN530|VS660|VS700|VS740|VS750|VS910|VS920|VS930|VX9200|VX11000|AX840A|LW770|P506|P925|P999',
			// Pantech
			'IM-A850S|IM-A840S|IM-A830L|IM-A830K|IM-A830S|IM-A820L|IM-A810K|IM-A810S|IM-A800S|IM-T100K|IM-A725L|IM-A780L|IM-A775C|IM-A770K|IM-A760S|IM-A750K|IM-A740S|IM-A730S|IM-A720L|IM-A710K|IM-A690L|IM-A690S|IM-A650S|IM-A630K|IM-A600S|VEGA PTL21|PT003|P8010|ADR910L|P6030|P6020|P9070|P4100|P9060|P5000|CDM8992|TXT8045|ADR8995|IS11PT|P2030|P6010|P8000|PT002|IS06|CDM8999|P9050|PT001|TXT8040|P2020|P9020|P2000|P7040|P7000|C790',
			// Fly 
			'IQ230|IQ444|IQ450|IQ440|IQ442|IQ441|IQ245|IQ256|IQ236|IQ255|IQ235|IQ245|IQ275|IQ240|IQ285|IQ280|IQ270|IQ260|IQ250',
		)).'/i' /EVAL */'',$ua))
			return self::$isMobile=true;
		
		return self::$isMobile=false;
		
		//return (bool)preg_match('/android.+mobile|mozilla.+(\s+|\()mobile;|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|meego.+mobile|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino|googlebot\-mobile/i',$_SERVER['HTTP_USER_AGENT'])
		//	||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(di|rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($_SERVER['HTTP_USER_AGENT'],0,4));
		
	}

	private static function _detectTablet($ua){
		// tablet quick detections
		if(preg_match(/* EVAL '/'.implode('|',array(
			'iPad',
			'SAMSUNG.*Tablet|Galaxy.*Tab',
			'Kindle|Silk.*Accelerated', //Kindle
			'Windows NT [0-9.]+; ARM;', //SurfaceTablet
			'Transformer|TF101', //AsusTablet
			'PlayBook|RIM Tablet', //BlackBerryTablet
			'Novo7', //AinolTablet
			'Sony Tablet',
			'T-Hub2', //TelstraTablet
			'Fly Vision', //FlyTablet
			'MediaPad', //HuaweiTablet
			'TOUCHPAD', //VersusTablet
			'Playstation.*(Portable|Vita)',
			'Tablet(?!.*PC)',
			'hp-tablet',
		)).'/i' /EVAL */'',$ua,$m)){
			self::$isTablet=true;
			return self::$isMobile=true;
		}
		// tablet complex detections
		if(preg_match(/* EVAL '/'.implode('|',array(
			'HTC Flyer|HTC Jetstream|HTC-P715a|HTC EVO View 4G|PG41200', //HTCtablet
			'xoom|sholest|MZ615|MZ605|MZ505|MZ601|MZ602|MZ603|MZ604|MZ606|MZ607|MZ608|MZ609|MZ615|MZ616|MZ617', //MotorolaTablet
			'\bL-06C|LG-V900|LG-V909\b', //LGTablet
			'LIFE.*(P9212|P9514|P9516|S9512)|LIFETAB', //MedionTablet
			'AN10G2|AN7bG3|AN7fG3|AN8G3|AN8cG3|AN7G3|AN9G3|AN7dG3|AN7dG3ST|AN7dG3ChildPad|AN10bG3|AN10bG3DT', //ArnovaTablet
			'101G9|80G9', //ArchosTablet
			'CUBE U8GT', //CubeTablet
			'MID1042|MID1045|MID1125|MID1126|MID7012|MID7014|MID7034|MID7035|MID7036|MID7042|MID7048|MID7127|MID8042|MID8048|MID8127|MID9042|MID9740|MID9742|MID7022|MID7010', //CobyTablet
			'RK2738|RK2808A', //RockChipTablet
			'\bIQ310\b',
			'bq.*(Elcano|Curie|Edison|Maxwell|Kepler|Pascal|Tesla|Hypatia|Platon|Newton|Livingstone|Cervantes|Avant)', //bqTablet
			'IDEOS S7|S7-201c|S7-202u|S7-101|S7-103|S7-104|S7-105|S7-106|S7-201|S7-Slim', //HuaweiTablet
			'\bN-06D|\bN-08D', //NecTablet
			'Pantech.*P4100', //PantechTablet
			'Broncho.*(N701|N708|N802|a710)', //BronchoTablet
			'z1000|Z99 2G|z99|z930|z999|z990|z909|Z919|z900', //ZyncTablet
			'TB07STA|TB10STA|TB07FTA|TB10FTA', // PositivoTablet
			'ViewPad7|MID7015|BNTV250A|LogicPD Zoom2|\bA7EB\b|CatNova8|A1_07|CT704|CT1002|\bM721\b', //Others
		)).'/i' /EVAL */'',$ua,$m)){
			self::$isTablet=true;
			return self::$isMobile=true;
		}
		
		return null;
	}

	
	/**
	 * Try to detect if it is a tablet
	 * 
	 * @return bool
	 */
	public static function isTablet(){
		if(self::$isTablet!==null && self::$isTablet!==0) return self::$isTablet;
		if(!self::isMobile()) return false;
		
		$ua=$_SERVER['HTTP_USER_AGENT'];
		
		if(self::$isTablet===null){
			$tabletDetection=self::_detectTablet($ua);
			if($tabletDetection!==null) return self::$isTablet;
		}
		
		if(preg_match('/android/i',$ua)){
			// tablet complex detections
			if(preg_match(/* EVAL '/'.implode('|',array(
				'Android.*Nook|NookColor|nook browser|BNTV250A|LogicPD Zoom2', //NookTablet
				'Android.*\b(A100|A101|A110|A200|A210|A211|A500|A501|A510|A511|A700|A701|W500|W500P|W501|W501P|W510|W511|W700|G100|G100W|B1-A71)\b',
				'Android.*(AT100|AT105|AT200|AT205|AT270|AT275|AT300|AT305|AT1S5|AT500|AT570|AT700|AT830)', //ToshibaTablet
				'Android.*(TAB210|TAB211|TAB224|TAB250|TAB260|TAB264|TAB310|TAB360|TAB364|TAB410|TAB411|TAB420|TAB424|TAB450|TAB460|TAB461|TAB464|TAB465|TAB467|TAB468)', //YarvikTablet
				'Android.*\bOYO\b', //MedionTablet
				'Android.*ARCHOS',
				'Android.*(K8GT|U9GT|U10GT|U16GT|U17GT|U18GT|U19GT|U20GT|U23GT|U30GT)', //CubeTablet
				'Android.*(\bMID\b|MID-560|MTV-T1200|MTV-PND531|MTV-P1101|MTV-PND530)', //SMiTTablet
				'Android.*(RK2818|RK2808A|RK2918|RK3066)', //RockChipTablet
				'Android.*\bNabi', //NabiTablet
				'Android.*\b97D\b',
			)).'/i' /EVAL */'',$ua,$m))
				return self::$isTablet=true;
		}
		
		
		//if(empty($_SERVER['HTTP_USER_AGENT'])) return false;
		//return (bool)preg_match('/android.+(?!mobile)|android 3\.|opera tablet|playbook|silk|ipad|hp\-tablet|sony tablet|samsung.*tablet|galaxy.*tab/i',$_SERVER['HTTP_USER_AGENT']);
		/*return ( (preg_match('/android/i',$_SERVER['HTTP_USER_AGENT']) && !preg_match('/android.+mobile|opera mobi/i',$_SERVER['HTTP_USER_AGENT']))
			|| preg_match('/android 3\.|opera tablet|playbook|silk|ipad|hp\-tablet|sony tablet|samsung.*tablet|galaxy.*tab/i',$_SERVER['HTTP_USER_AGENT']));*/
	}
	
	
	
	/**
	 * @return bool
	 */
	public static function isMobileOrTablet(){
		return self::isMobile();// || self::isTablet();
		/*if(empty($_SERVER['HTTP_USER_AGENT'])) return false;
		return (bool)preg_match('/android|mozilla.+(\s+|\()(mobile|tablet);|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(ad|hone|od)|iris|kindle|lge |maemo|meego.+mobile|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino|playbook|silk|googlebot\-mobile/i',$_SERVER['HTTP_USER_AGENT'])
			||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(di|rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($_SERVER['HTTP_USER_AGENT'],0,4));
		*/
	}

	/**
	 * @return bool
	 */
	public static function isMobileAndNotTablet(){
		//debug(self::isMobileOrTablet() && !self::isTablet(),self::$isMobile,self::$isTablet);
		return self::isMobileOrTablet() && !self::isTablet();
	}
	
	
	CONST P_WINDOWS=0,P_MAC=1,P_LINUX=2,P_FREE_BSD=3,P_IPOD=10,P_IPAD=11,P_IPHONE=12,P_ANDROID=13,P_SYMBIAN=14,P_P_IMODE=15,P_NINTENDO_WII=20,P_PLAYSTATION_PORTABLE=21;
	CONST B_CRAWLER=0,B_OPERA_MINI=1,B_OPERA=2,B_IE=3,B_FIREFOX=4,B_CHROME=5,B_CHROMIUM=6,B_SAFARI=7,
		B_EPIPHANY=10,B_FENNEC=11,B_ICEWEASEL=12,B_MINEFIELD=13,B_MINIMO=14,B_FLOCK=15,B_FIREBIRD=16,B_PHOENIX=17,B_CAMINO=18,B_CHIMERA=19,B_THUNDERBIRD=20,B_NETSCAPE=21,B_OMNIWEB=22,B_IRON=23,B_ICAB=24,B_KONQUEROR=25,B_MIDORI=26,B_DOCOMO=27,B_LYNX=28,B_LINKS=29,
		B_W3C_VALIDATOR=30,B_APACHE_BENCH=31,B_LIBWWW_PERL_LIB=32,B_W3M=33,B_WGET=34;
	
	/**
	 * @return array ['user_agent'=>,'platform'=>,'browser'=>,'version']
	 */
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
	
}