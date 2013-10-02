<?php
/**
 * String helper.
 * 
 * Mostly these functions are utils, but...
 */
class HString{
	private static $_transliteration = array(
/*		'/ä|æ|ǽ/' => 'ae',
		'/ö|œ/' => 'oe',
		'/ü/' => 'ue',
		'/Ä/' => 'Ae',
		'/Ü/' => 'Ue',
		'/Ö/' => 'Oe',
*/
		'/æ|ǽ/' => 'ae',
		'/œ/' => 'oe',
		'/Ä|À|Á|Â|Ã|Ä|Å|Ǻ|Ā|Ă|Ą|Ǎ/' => 'A',
		'/ä|à|á|â|ã|å|ǻ|ā|ă|ą|ǎ|ª/' => 'a',
		'/Ç|Ć|Ĉ|Ċ|Č/' => 'C',
		'/ç|ć|ĉ|ċ|č/' => 'c',
		'/Ð|Ď|Đ/' => 'D',
		'/ð|ď|đ/' => 'd',
		'/È|É|Ê|Ë|Ē|Ĕ|Ė|Ę|Ě|€/' => 'E',
		'/è|é|ê|ë|ē|ĕ|ė|ę|ě/' => 'e',
		'/Ĝ|Ğ|Ġ|Ģ/' => 'G',
		'/ĝ|ğ|ġ|ģ/' => 'g',
		'/Ĥ|Ħ/' => 'H',
		'/ĥ|ħ/' => 'h',
		'/Ì|Í|Î|Ï|Ĩ|Ī|Ĭ|Ǐ|Į|İ/' => 'I',
		'/ì|í|î|ï|ĩ|ī|ĭ|ǐ|į|ı/' => 'i',
		'/Ĵ/' => 'J',
		'/ĵ/' => 'j',
		'/Ķ/' => 'K',
		'/ķ/' => 'k',
		'/Ĺ|Ļ|Ľ|Ŀ|Ł/' => 'L',
		'/ĺ|ļ|ľ|ŀ|ł/' => 'l',
		'/Ñ|Ń|Ņ|Ň/' => 'N',
		'/ñ|ń|ņ|ň|ŉ/' => 'n',
		'/Ö|Ò|Ó|Ô|Õ|Ō|Ŏ|Ǒ|Ő|Ơ|Ø|Ǿ/' => 'O',
		'/ö|ò|ó|ô|õ|ō|ŏ|ǒ|ő|ơ|ø|ǿ|º|°/' => 'o',
		'/Ŕ|Ŗ|Ř/' => 'R',
		'/ŕ|ŗ|ř/' => 'r',
		'/Ś|Ŝ|Ş|Š/' => 'S',
		'/ś|ŝ|ş|š|ſ/' => 's',
		'/Ţ|Ť|Ŧ/' => 'T',
		'/ţ|ť|ŧ/' => 't',
		'/Ü|Ù|Ú|Û|Ũ|Ū|Ŭ|Ů|Ű|Ų|Ư|Ǔ|Ǖ|Ǘ|Ǚ|Ǜ/' => 'U',
		'/ü|ù|ú|û|ũ|ū|ŭ|ů|ű|ų|ư|ǔ|ǖ|ǘ|ǚ|ǜ/' => 'u',
		'/Ý|Ÿ|Ŷ/' => 'Y',
		'/ý|ÿ|ŷ/' => 'y',
		'/Ŵ/' => 'W',
		'/ŵ/' => 'w',
		'/Ź|Ż|Ž/' => 'Z',
		'/ź|ż|ž/' => 'z',
		'/Æ|Ǽ/' => 'AE',
		'/ß/'=> 'ss',
		'/Ĳ/' => 'IJ',
		'/ĳ/' => 'ij',
		'/Œ/' => 'OE',
		'/ƒ/' => 'f',
		'/&/' => 'et',
		
		'/þ/'=>'th',
		'/Þ/'=>'TH',
		
		//'/┐|└|┴|┬|├|─|┼|�/'=>''
	);
	
	/**
 * Returns a string with all spaces converted to underscores (by default), accented
 * characters converted to non-accented characters, and non word characters removed.
 *
 * @param string $string the string you want to slug
 * @param string $replacement will replace keys in map
 * @return string
 * @access public
 * @static
 * @link http://book.cakephp.org/view/1479/Class-methods
 */
	static public function slug($string, $replacement = '-') {
		//$quotedReplacement=preg_quote($replacement, '/');

		$string=self::transliterate($string);
		$string=preg_replace('/[^\s\p{Ll}\p{Lm}\p{Lo}\p{Lt}\p{Lu}\p{Nd}]/mu', ' ', $string);
		$string=preg_replace('/\s+/',$replacement,$string);
		//return preg_replace('/^['.$quotedReplacement.']+|['.$quotedReplacement.']+$/','',$string);
		return trim($string,$replacement);
	}
	
	/**
	 * Transliterate and remove all special chars
	 * 
	 * @param string $string
	 * @return string
	 */
	static public function removeSpecialChars($string){
		$string=self::transliterate($string);
		return trim(preg_replace('/[^\s\p{Ll}\p{Lm}\p{Lo}\p{Lt}\p{Lu}\p{Nd}]+/mu',' ',$string));
	}
	
	/**
	 * Transliterate a string
	 * 
	 * @param string
	 * @return string
	 */
	public static function transliterate($string){
		//return preg_replace(array_keys(self::$_transliteration), array_values(self::$_transliteration), $string);
		foreach(self::$_transliteration as $pattern=>$replacement)
			$string=preg_replace($pattern,$replacement,$string);
		return $string;
	}
	
	/**
	 * Compute the dice coefficient
	 * 
	 * @see http://tonyarchambeau.com/blog/400-php-coefficient-de-dice/
	 * 
	 * @param string
	 * @param string
	 * @return float
	 */
	public static function dice($str1,$str2){
		$str1_length = strlen($str1);
		$str2_length = strlen($str2);
		
		// Length of the string must not be equal to zero
		if ( ($str1_length===0) OR ($str2_length===0) ) return 0;
		
		$ar1 = $ar2 = array(); $intersection = 0;
		// find the pair of characters
		for ($i=0 ; $i<($str1_length-1) ; $i++)
			$ar1[] = substr($str1, $i, 2);
		
		for ($i=0 ; $i<($str2_length-1) ; $i++)
			$ar2[] = substr($str2, $i, 2);
		
		// find the intersection between the two sets
		foreach ($ar1 as $pair1)
			foreach ($ar2 as $pair2)
				if ($pair1 == $pair2) $intersection++;
		
		$count_set = count($ar1) + count($ar2);
		
		return (2 * $intersection) / $count_set;
	}
	
	
	/** http://www.iugrina.com/files/JaroWinkler/JaroWinkler.phps
  version 1.2

  Copyright (c) 2005-2010  Ivo Ugrina <ivo@iugrina.com>

  A PHP library implementing Jaro and Jaro-Winkler
  distance, measuring similarity between strings.

  Theoretical stuff can be found in:
  Winkler, W. E. (1999). "The state of record linkage and current
  research problems". Statistics of Income Division, Internal Revenue
  Service Publication R99/04. http://www.census.gov/srd/papers/pdf/rr99-04.pdf.


  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 3 of the License, or (at
  your option) any later version.

  This program is distributed in the hope that it will be useful, but
  WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
  General Public License for more details.
  
  You should have received a copy of the GNU General Public License
  along with this program.  If not, see <http://www.gnu.org/licenses/>.

  ===

  A big thanks goes out to Pierre Senellart <pierre@senellart.com>
  for finding a small bug in the code.
	 * 
	 * @see http://en.wikipedia.org/wiki/Jaro%E2%80%93Winkler_distance
	 * Measure of similarity between two strings
	 * The higher the Jaro–Winkler distance for two strings is, the more similar the strings are.
	 * The Jaro–Winkler distance metric is designed and best suited for short strings such as person names.
	 * 
	 * @param string
	 * @param string
	 * @return float
*/
	public static function jaroWinkler($string1,$string2){
		$JaroDistance = self::jaroWinkler_Jaro( $string1, $string2 );
		$prefixLength = self::jaroWinkler_getPrefixLength( $string1, $string2 );
		return $JaroDistance + $prefixLength * .1 * (1.0 - $JaroDistance);
	}
	
	/**
	 * @param string
	 * @param string
	 * @return float
	 */
	private static function jaroWinkler_Jaro($string1, $string2){
		$str1_len = strlen( $string1 );
		$str2_len = strlen( $string2 );
		
		// theoretical distance
		$distance = floor(min( $str1_len, $str2_len ) / 2.0);
		
		// get common characters
		$commons1 = self::jaroWinkler_getCommonCharacters( $string1, $string2, $str1_len, $str2_len , $distance );
		if( ($commons1_len = strlen( $commons1 )) === 0) return 0;
		$commons2 = self::jaroWinkler_getCommonCharacters( $string2, $string1, $str2_len , $str1_len , $distance );
		if( ($commons2_len = strlen( $commons2 )) === 0) return 0;
		
		// calculate transpositions
		$transpositions = 0;
		
		$upperBound = min( $commons1_len, $commons2_len );
		for( $i = 0; $i < $upperBound; $i++)
			if( $commons1[$i] != $commons2[$i] ) $transpositions++;
		$transpositions /= 2.0;
		
		// return the Jaro distance
		return ($commons1_len/($str1_len) + $commons2_len/($str2_len) + ($commons1_len - $transpositions)/($commons1_len)) / 3.0;
	}
	
	/**
	 * @param string
	 * @param string
	 * @param int
	 * @param int
	 * @param int
	 * @return string
	 */
	private static function jaroWinkler_getCommonCharacters($string1,$string2,$str1_len,$str2_len,$allowedDistance){
		$temp_string2 = $string2;
		$commonCharacters='';
		for( $i=0; $i < $str1_len; $i++){
			$ch=$string1[$i];
			// compare if char does match inside given allowedDistance
			// and if it does add it to commonCharacters
			
			for( $j= max( 0, $i-$allowedDistance ), $minMax=min( $i + $allowedDistance + 1, $str2_len ) ; $j < $minMax; $j++){
				if( substr($temp_string2,$j,1) === $ch ){//bug if '$temp_string2['.$j.']'
					$commonCharacters .= $ch;
					substr_replace($temp_string2,"\0",$j,1);
					break;
				}
			}
		}
		return $commonCharacters;
	}
	
	/**
	 * @param string
	 * @param string
	 * @param int
	 * @return int
	 */
	private static function jaroWinkler_getPrefixLength($string1, $string2, $MINPREFIXLENGTH = 4){
		$n = min( array( $MINPREFIXLENGTH, strlen($string1), strlen($string2) ) );
		for($i = 0; $i < $n; $i++)
			if( $string1[$i] != $string2[$i] ) return $i; // return index of first occurrence of different characters
		return $n; // first n characters are the same
	}
}