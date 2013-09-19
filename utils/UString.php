<?php
mb_internal_encoding('utf-8');
/*ini_set('mbstring.language', 'UTF-8');
ini_set('mbstring.internal_encoding', 'UTF-8');
ini_set('mbstring.http_input', 'UTF-8');
ini_set('mbstring.http_output', 'UTF-8');*/

/** String Utils */
class UString{
	/**
	 * If the string is all in lowercase or all in uppercase, transform it into a lowercased string with the first letter uppercased
	 * 
	 * @param string
	 * @return string
	 */
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
	
	/**
	 * Normalize a string : transliterate, remove all special chars and lowercase the string 
	 * 
	 * @param string
	 * @return string
	 * @see transliterate
	 * @see normalizeWithoutTransliterate
	 */
	public static function normalize($string){
		return self::normalizeWithoutTransliterate(HString::transliterate($string));
	}
	/**
	 * Remove special chars, trim and lowercase the string
	 * 
	 * @param string
	 * @return string
	 * @see normalize
	 */
	public static function normalizeWithoutTransliterate($string){
		$string=preg_replace_callback('/\b(?:[A-Za-z]\.){2,}\b/',function($m){return str_replace('.','',$m[0]); },$string);
		return self::low(trim(preg_replace('/[ \-\'\"\_\(\)\[\]\{\}\#\~\&\*\,\.\;\:\!\?\/\\\\|\`\<\>\+]+/',' ',$string)));
	}
	
	/**
	 * For a string, call the callback for each words and replace it by the result of the callback
	 * 
	 * @param string
	 * @param function callback($word,$dot)
	 * @return string
	 */
	public static function callbackWords($string,$callback,$regexpDash=false){
		/* http://www.php.net/manual/en/regexp.reference.unicode.php */
		$arrayRegexp=array("/(\w\'|(?:[A-Z]\.){2,}|\p{L}+(\.|\b))/u"); //u=unicode != U
		if($regexpDash) $arrayRegexp[]="/(\p{L}+\-\p{L}+(\.|\b))/u";
		foreach($arrayRegexp as $regexp)
		$string=preg_replace_callback($regexp,function($m) use($callback){
			$dot=empty($m[2])?'':'.'; return $callback($dot===''?$m[1]:substr($m[1],0,-1),$dot); },$string);
		return $string;
	}
	
	/**
	 * Return the first line of a string
	 * 
	 * @param string
	 * @return string
	 */
	public static function firstLine($string){
		$line=strpos($string,"\n");
		if($line!==false) $line=substr($string,0,$line);
		return $line;
	}
	
	/**
	 * Make a string lowercase
	 * 
	 * @param string
	 * @return string
	 */
	public static function low($str){
		return mb_strtolower($str);
	}
	
	/**
	 * Make a string uppercase
	 * 
	 * @param string
	 * @return string
	 */
	public static function up($str){
		return mb_strtoupper($str);
	}
	
	/**
	 * Make a string's first character uppercase
	 * 
	 * @param string
	 * @return string
	 */
	public static function ucFirst($str){
		return mb_strtoupper(mb_substr($str,0,1)) . mb_substr($str,1);
	}
	
	/**
	 * Get string length
	 * 
	 * @param string
	 * @return string
	 */
	public static function length($str){
		return mb_strlen($str);
	}
	
	/**
	 * Find position of last occurrence of a string in a string
	 * 
	 * @param string The string being checked, for the last occurrence of needle
	 * @param string The string to find in haystack.
	 * @return int
	 */
	public static function pos($haystack,$needle){
		return mb_strrpos($haystack,$needle);
	}
	
	/**
	 * Performs a multi-byte safe substr() operation based on number of characters.
	 * 
	 * Position is counted from the beginning of str. First character's position is 0. Second character position is 1, and so on.
	 * @param string
	 * @param int
	 * @param int
	 * @return string
	 */
	public static function substr($str,$start,$length=null){
		return mb_substr($str,$start,$length);
	}
	
	/**
	 * Return an underscored string from a camelCased string
	 * 
	 * @param string
	 * @return string
	 */
	public static function underscore($string){
		return self::low(self::_underscore($string));
	}
	/**
	 * Return an underscored uppercased string from a camelCased string
	 * 
	 * @param string
	 * @return string
	 */
	public static function underscoreUp($string){
		return self::up(self::_underscore($string));
	}
	
	private static function _underscore($string){
		$string=preg_replace('/([A-Z]+|[0-9]+)([A-Z][a-z])/','$1_$2',$string);
		$string=preg_replace('/([a-z])([A-Z]|[0-9])/','$1_$2',$string);
		return $string;
	}
	
	/**
	 * Return an camelCased string from a underscored string
	 * 
	 * @param string
	 * @param bool
	 * @return string
	 */
	public static function camelize($value,$startWithLowercase=false){
		$values=explode('_',$value);
		$res=$startWithLowercase===false ? '' : array_shift($values);
		foreach($values as $value) $res.=ucfirst($value);
		return $res;
	}
	
	/**
	 * Truncates a string and add an ending if the length is too high
	 * 
	 * @param string
	 * @param int
	 * @param string
	 * @return string
	 */
	public static function truncate($str, $maxLength, $end='...'){
		if(self::length($str) <= $maxLength) return $str;
		return self::substr($str,0,$maxLength - self::length($end)).$end;
	}
}