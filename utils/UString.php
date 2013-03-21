<?php
mb_internal_encoding('utf-8');
/*ini_set('mbstring.language', 'UTF-8');
ini_set('mbstring.internal_encoding', 'UTF-8');
ini_set('mbstring.http_input', 'UTF-8');
ini_set('mbstring.http_output', 'UTF-8');*/

class UString{
	public static function checkAllLowerOrAllUpperCase($string){
		if(!empty($string)){
			if($string===self::low($string)) $string=self::ucFirst($string);
			elseif($string===self::up($string)) $string=self::ucFirst(self::low($string));
			// http://stackoverflow.com/questions/1649015/check-if-at-least-75-of-a-string-is-uppercase
			/*else{
				preg_replace('/\p{Lu}/', '', $str,-1,$countUppercaseLetters);
				if($countUppercaseLetters / strlen($string)) $string=ucfirst(strtolower($string));
			}
			if(preg_match('/^[\p{Lu}\s]*$/', $subject))
			*/
		}
		return $string;
	}
	
	public static function normalize($string){
		return self::normalizeWithoutTransliterate(HString::transliterate($string));
	}
	public static function normalizeWithoutTransliterate($string){
		$string=preg_replace_callback('/\b(?:[A-Za-z]\.){2,}\b/',function($m){return str_replace('.','',$m[0]); },$string);
		return self::low(trim(preg_replace('/[ \-\'\"\_\(\)\[\]\{\}\#\~\&\*\,\.\;\:\!\?\/\\\\|\`\<\>\+]+/',' ',$string)));
	}
	
	public static function callbackWords($string,$callback,$regexpDash=false){
		/* http://www.php.net/manual/en/regexp.reference.unicode.php */
		$arrayRegexp=array("/(\w\'|(?:[A-Z]\.){2,}|\p{L}+(\.|\b))/u"); //u=unicode != U
		if($regexpDash) $arrayRegexp[]="/(\p{L}+\-\p{L}+(\.|\b))/u";
		foreach($arrayRegexp as $regexp)
		$string=preg_replace_callback($regexp,function($m) use($callback){
			$dot=empty($m[2])?'':'.'; return $callback($dot===''?$m[1]:substr($m[1],0,-1),$dot); },$string);
		return $string;
	}
	
	public static function firstLine($string){
		$line=strpos($string,"\n");
		if($line!==false) $line=substr($string,0,$line);
		return $line;
	}
	
	
	public static function low($str){ return mb_strtolower($str); }
	public static function up($str){ return mb_strtoupper($str); }
	public static function ucFirst($str){ return mb_strtoupper(mb_substr($str,0,1)) . mb_substr($str,1); }
	public static function length($str){ return mb_strlen($str); }
}