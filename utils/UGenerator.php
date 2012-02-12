<?php
class UGenerator{
	/**
	 * Generate a random UUID
	 *
	 * @see http://www.ietf.org/rfc/rfc4122.txt
	 * @return RFC 4122 UUID
	 * @static
	 */
	public static function uuid(){
		$node = isset($_SERVER['SERVER_ADDR'])?$_SERVER['SERVER_ADDR']:null;
		$pid = null;

		if (strpos($node, ':') !== false){
			if (substr_count($node,'::'))
				$node = str_replace('::',str_repeat(':0000',8-substr_count($node, ':')).':',$node);
			$node=explode(':', $node) ;
			$ipv6='' ;

			foreach($node as $id) $ipv6.=str_pad(base_convert($id,16,2),16,0,STR_PAD_LEFT);
			$node=base_convert($ipv6, 2, 10);

			$node=strlen($node)<38?null:crc32($node);
		}elseif(empty($node)) $node = null;
		elseif($node !== '127.0.0.1') $node = ip2long($node);
		else $node = null;

		if(empty($node)) $node = crc32(CSecure::getSalt());

		if(function_exists('zend_thread_id')) $pid = zend_thread_id();
		else $pid = getmypid();

		if (!$pid || $pid > 65535) $pid = mt_rand(0, 0xfff) | 0x4000;

		list($timeMid, $timeLow)=explode(' ',microtime(),2);
		return sprintf(
			"%08x-%04x-%04x-%02x%02x-%04x%08x", (int)$timeLow, (int)substr($timeMid, 2) & 0xffff,
			mt_rand(0, 0xfff) | 0x4000, mt_rand(0, 0x3f) | 0x80, mt_rand(0, 0xff), $pid, $node
		);
	}
	
	public static function &randomCode($size,$chars=array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z','0','1','2','3','4','5','6','7','8','9')){
		shuffle($chars);
		$finalWord=$lastChar=$charBeforeLast='';
		$i=0;
		while($i++ < $size){
			while(($char=$chars[array_rand($chars)])===$lastChar || $char===$charBeforeLast) ;
			$charBeforeLast=$lastChar;
			$finalWord.=($lastChar=$char);
		}
		return $finalWord;
	}
	
	public static function &randomLetters($size){
		return self::randomCode($size,array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','p','q','r','s','t','u','v','x','y','z'));
	}

	public static function &numbers($size){
		return self::randomCode($size,array(1,2,3,4,5,6,7,8));
	}
	
	public static function pronounceablePassword($size){
		return self::pronounceableWord(($size=($size/3))*2).self::numbers($size);
	}
	
	public static function &pronounceableWord($size){
		$consonnes=array('l','m','n','p','r');
		$chars=array(
			'a' => array('d','m','t'),
			'au' => array('d'),
			'e'=> array('l','m','p'),
			'o' => &$consonnes,
			'ou'=> &$consonnes,
			'u'=> &$consonnes,
	
			'c' => array('l','r'),
			'd' => array('r','e','ou'),
			'h' => array('a','e','u'),
			'p' => array('au','a'),
			'l' => array('a', 'au'),
			'm' => array('au','u'),
			'n' => array('e','o'),
			'r' => array('e'),
			't' => array('au','ou'),
		);
		$start=array('a','au','e','c','d','h','l','m','n','t'); shuffle($start);
		
		$charBeforeLast='';
		$finalWord=$firstChar=$lastChar=$start[array_rand($start)];
		
		$finalWord.=$lastChar=$chars[$lastChar][array_rand($chars[$lastChar])];
		unset($chars[$lastChar][$firstChar]); // prevent repetitions
		
		while(strlen($finalWord) < $size){
			$newChar=$chars[$lastChar][array_rand($chars[$lastChar])];
			if($newChar!==$charBeforeLast){
				$charBeforeLast=$lastChar;
				$finalWord.=$lastChar=$newChar;
			}
		}
		return $finalWord;
	}
}
