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
}