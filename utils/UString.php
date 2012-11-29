<?php
class UString{
	public static function checkAllLowerOrAllUpperCase($string){
		if(!empty($string)){
			if($string===strtolower($string)) $string=ucfirst($string);
			elseif($string===strtoupper($string)) $string=ucfirst(strtolower($string));
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
		return strtolower(trim(preg_replace('/[ \-\'\"\_\(\)\[\]\{\}\#\~\&\*\,\.\;\:\!\?\/\\\\|\`\<\>\+]+/',' ',
																HString::transliterate($string))));
	}
	
	public static function callbackWords($string,$callback){
		return preg_replace_callback("/(\w\'|(?:[A-Z]\.){2,}|[A-Za-z0-9ÉÈÊËÂÄÔÖéèêëâäôöçïî]+(\.|\b))/",
					function($m) use($callback){ $dot=empty($m[2])?'':'.';
						return $callback($dot===''?$m[1]:substr($m[1],0,-1),$dot); },$string);
	}
	
	
	
	public static function firstLine($string){
		$line=strpos($string,"\n");
		if($line!==false) $line=substr($string,0,$line);
		return $line;
	}
}