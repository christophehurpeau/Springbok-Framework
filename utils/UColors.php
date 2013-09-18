<?php
/* http://www.barelyfitz.com/projects/csscolor/ */
/** Colors utils */
class UColors{
	const minBrightDiff=126;
	const minColorDiff=500;
	
	/**
	 * Convert a rgb color to hex color
	 * 
	 * @param array|string $rgb array RGB or R
	 * @param string G
	 * @param string B
	 */
	public static function rgb2hex(){
		if(func_num_args() === 1) $rgb=func_get_arg(0); 
		else $rgb=func_get_args();
		$hex='';
		foreach($rgb as $color){
			$color=dechex($color);
			$hex .= strlen($color) == 2 ? $color : '0'.$color; // '0' ou $color ??? .$color
		}
		return $hex;
	}
	
	/**
	 * Convert a hex color to rgb color
	 * 
	 * @param string hex color
	 * @return string
	 */
	public static function hex2rgb($hexColor){
		$hexColor = strtolower(trim($hexColor,'#;&Hh'));
		//return array_map('hexdec',explode('.',wordwrap($hex_color, ceil(strlen($hex_color)/3),'.',1)));
		
		$d = '[a-fA-F0-9]'; $rgb=array();
		if(preg_match("/^($d{2})($d{2})($d{2})\$/",$hexColor,$rgb))
			return array(hexdec($rgb[1]),hexdec($rgb[2]),hexdec($rgb[3]));
		if(preg_match("/^($d)($d)($d)\$/",$hexColor,$rgb))
			return array(hexdec($rgb[1].$rgb[1]),hexdec($rgb[2].$rgb[2]),hexdec($rgb[3].$rgb[3]));
		throw new Exception('Cannot convert hex "'.$hexColor.'" to RGB');
	}
	
	/**
	 * Return the opposite hex color
	 * 
	 * @param string hex color
	 * @return string
	 */
	public static function getOppositeHex($hex_color){
		$rgb = self::hex2rgb($hex_color);
		foreach($rgb as $k=>$color)
			$rgb[$k] = (255-$color < 0 ? 0 : 255-$color);
		return self::rgb2hex($rgb);
	}
	
	/**
	 * Return a random color
	 * 
	 * @return string
	 */
	public static function randomHex(){
		return self::rgb2hex(rand(0,255),rand(0,255),rand(0,255));
	}
	
	/**
	 * Return shades of an hex color from black to white
	 * 
	 * @return array
	 */
	public static function darkerAndLighterShades($hexColor){
		/*#if DEV */
		if(!self::isHex($hexColor)) throw new Exception($hexColor.' is not a valid HEX color');
		/*#/if */
		
		return array(
			-10=>'000000',
			-9=>self::_darken($hexColor, .1),
			-8=>self::_darken($hexColor, .2),
			-7=>self::_darken($hexColor, .3),
			-6=>self::_darken($hexColor, .4),
			-5=>self::_darken($hexColor, .5),
			-4=>self::_darken($hexColor, .6),
			-3=>self::_darken($hexColor, .7),
			-2=>self::_darken($hexColor, .8),
			-1=>self::_darken($hexColor, .9),
			0=>$hexColor,
			1=>self::_lighten($hexColor, .9),
			2=>self::_lighten($hexColor, .8),
			3=>self::_lighten($hexColor, .7),
			4=>self::_lighten($hexColor, .6),
			5=>self::_lighten($hexColor, .5),
			6=>self::_lighten($hexColor, .4),
			7=>self::_lighten($hexColor, .3),
			8=>self::_lighten($hexColor, .2),
			9=>self::_lighten($hexColor, .1),
			10=>'FFFFFF',
		);
	}
	
	/**
	 * Return shades of an hex color from black to white in an array containing both foreground ('fg') and background ('bg') colors
	 * 
	 * @return array
	 */
	public static function darkerAndLighterShadesWithForeground($hexColor){
		$bg=self::darkerAndLighterShades($hexColor);
		$res=array();
		foreach($bg as $key=>$color){
			$res[$key]['bg']=$color;
			$res[$key]['fg']=self::findBestFgColor($color,$hexColor);
		}
		return $res;
	}
	
	/**
	 * Lighten an color
	 * 
	 * @param string
	 * @param float
	 * @return string
	 */
	public static function _lighten($hexColor,$percent){
		return self::_mix($hexColor,$percent,255);
	}
	
	/**
	 * Darken an color
	 * 
	 * @param string
	 * @param float
	 * @return string
	 */
	public static function _darken($hexColor,$percent){
		return self::_mix($hexColor,$percent,0);
	}
	
	private static function _mix($hexColor,$percent,$mask){
		// Make sure inputs are valid
		/*#if DEV */
		if (!is_numeric($percent) || $percent < 0 || $percent > 1)
			throw new Exception("percent=$percent is not valid");
	
		if (!is_int($mask) || $mask < 0 || $mask > 255)
			throw new Exception("mask=$mask is not valid");
		/*#/if */
	
		$rgb = self::hex2rgb($hexColor);
	
		for ($i=0; $i<3; $i++) {
			$rgb[$i] = round($rgb[$i] * $percent) + round($mask * (1-$percent));
	
			// In case rounding up causes us to go to 256
			if ($rgb[$i] > 255) $rgb[$i] = 255;
	
		}
		return self::rgb2hex($rgb);
	}
	
	/**
	 * Check if the arguement is an hex color
	 * 
	 * @param string
	 * @return bool
	 */
	public static function isHex($hexColor){
		if(preg_match("/^#?([a-fA-F0-9]{6}|[a-fA-F0-9]{3})\$/",$hexColor))
			return true;
		return false;
	}
	
	/**
	 * Find the best corresponding color for a background color
	 * 
	 * @param string
	 * @param string
	 * @return string
	 */
	public static function findBestFgColor($bgHex,$fgHex='#000000'){
		// Given a background color $bgHex and a foreground color $fgHex,
		// modifies the foreground color so it will have enough contrast
		// to be seen against the background color.
		//
		// The following parameters are used:
		// $this->minBrightDiff
		// $this->minColorDiff

		// Loop through brighter and darker versions
		// of the foreground color.
		// The numbers here represent the amount of
		// foreground color to mix with black and white.
		foreach(array(1, 0.75, 0.5, 0.25, 0) as $percent){
			$darker=self::_darken($fgHex, $percent);
			$lighter=self::_lighten($fgHex, $percent);
	
			$darkerBrightDiff=self::_brightnessDiff($bgHex, $darker);
			$lighterBrightDiff=self::_brightnessDiff($bgHex, $lighter);

			if($lighterBrightDiff > $darkerBrightDiff){
				$newFG = $lighter;
				$newFGBrightDiff = $lighterBrightDiff;
		  	}else{
				$newFG = $darker;
				$newFGBrightDiff = $darkerBrightDiff;
			}
			$newFGColorDiff=self::_colorDiff($bgHex, $newFG);

			if ($newFGBrightDiff >= self::minBrightDiff && $newFGColorDiff >= self::minColorDiff)
				break;
		}

		return $newFG;
	}

	private static function _brightness($hexColor){
		// Returns the brightness value for a color,
		// a number between zero and 178.
		// To allow for maximum readability, the difference between
		// the background brightness and the foreground brightness
		// should be greater than 125.

		$rgb=self::hex2rgb($hexColor);
		
		return( (($rgb[0] * 299) + ($rgb[1] * 587) + ($rgb[2] * 114)) / 1000 );
	}
	
	private static function _brightnessDiff($hex1,$hex2){
		// Returns the brightness value for a color,
		// a number between zero and 178.
		// To allow for maximum readability, the difference between
		// the background brightness and the foreground brightness
		// should be greater than 125.
		
		return abs(self::_brightness($hex1) - self::_brightness($hex2));
	}
	
	private static function _colorDiff($hex1,$hex2){
		// Returns the contrast between two colors,
		// an integer between 0 and 675.
		// To allow for maximum readability, the difference between
		// the background and the foreground color should be > 500.
		
		$rgb1 = self::hex2rgb($hex1);
		$rgb2 = self::hex2rgb($hex2);
		
		$r1 =& $rgb1[0]; $g1 =& $rgb1[1]; $b1 =& $rgb1[2];
		$r2 =& $rgb2[0]; $g2 =& $rgb2[1]; $b2 =& $rgb2[2];
		
		return(abs($r1-$r2) + abs($g1-$g2) + abs($b1-$b2));
	}
}