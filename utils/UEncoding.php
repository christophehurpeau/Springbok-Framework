<?php
class UEncoding{
	public static function &convertToUtf8($str){
		if(($enc=mb_detect_encoding($str,'UTF-8, ISO-8859-15, ASCII, GBK'))!=='UTF-8')
			$str=iconv($enc,'UTF-8',$str); 
		return $str;
	}
	
	public static function isUtf8($str){
		return mb_detect_encoding($str,'UTF-8');
	}
	
	/** ISO-8859-1 to UTF-8 */
	public static function toUtf8($str){
		return iconv('ISO-8859-1','UTF-8//TRANSLIT',$str);
	}
	
	/** UTF-8 to ISO-8859-1 */
	public static function fromUtf8($str){
		return iconv('UTF-8','ISO-8859-1//TRANSLIT',$str);
	}
	
	public static function fromUtf8_Sign($str){
		return str_replace('¥',chr(165),str_replace('£',chr(163),str_replace('€', chr(128),$str)));
	}
}
