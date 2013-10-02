<?php
/**
 * Time helper
 * 
 * @see App::getLocale()
 * @see CLocale
 */
class HTime{
	private static $locale;
	
	public static function init(){
		self::$locale=App::getLocale();
	}
	
	/**
	 * @param string
	 * @return int
	 */
	public static function fromString($dateString){
		if(empty($dateString)) return false;
		/*if(is_integer($dateString) || is_numeric($dateString)) $date=(int)$dateString;
		else */$date = strtotime($dateString);
		return $date;
	}
	
	/**
	 * @param string
	 * @param bool
	 * @return string
	 */
	public static function nice($dateString,$hourAndMinutes=true){
		return self::niceTime(strtotime($dateString),$hourAndMinutes);
	}
	
	/**
	 * @param int
	 * @param bool
	 * @return string
	 */
	public static function niceTime($date=NULL,$hourAndMinutes=true){
		return $hourAndMinutes?self::$locale->formatDateTime($date,'nice'):self::$locale->formatDate($date,'nice');
	}
	
	/**
	 * @param string
	 * @param bool
	 * @return string
	 */
	public static function short($dateString,$hourAndMinutes=true){
		return self::shortTime(strtotime($dateString),$hourAndMinutes);
	}
	
	/**
	 * @param int
	 * @param bool
	 * @return string
	 */
	public static function shortTime($date=NULL,$hourAndMinutes=true){
		return $hourAndMinutes?self::$locale->formatDateTime($date,'short'):self::$locale->formatDate($date,'short');
	}
	
	/**
	 * @param string
	 * @param bool
	 * @return string
	 */
	public static function simple($dateString,$hourAndMinutes=true){
		return self::simpleTime(strtotime($dateString),$hourAndMinutes);
	}
	
	/**
	 * @param int
	 * @param bool
	 * @return string
	 */
	public static function simpleTime($date=NULL,$hourAndMinutes=true){
		return $hourAndMinutes?self::$locale->formatDateTime($date,'simple'):self::$locale->formatDate($date,'simple');
	}
	
	/**
	 * @param string
	 * @param bool
	 * @return string
	 */
	public static function compact($dateString=false,$hourAndMinutes=true){
		if($dateString===false) return '';
		return self::compactTime(strtotime($dateString),$hourAndMinutes);
	}
	
	/**
	 * @param int
	 * @param bool
	 * @return string
	 */
	public static function compactTime($date=NULL,$hourAndMinutes=true){
		return $hourAndMinutes?self::$locale->formatDateTime($date,'compact'):self::$locale->formatDate($date,'compact');
	}
	
	/**
	 * @param string
	 * @param bool
	 * @return string
	 */
	public static function complete($dateString=false,$hourAndMinutes=true){
		if($dateString===false) return '';
		return self::completeTime(strtotime($dateString),$hourAndMinutes);
	}
	
	/**
	 * @param int
	 * @param bool
	 * @return string
	 */
	public static function completeTime($date=NULL,$hourAndMinutes=true){
		return $hourAndMinutes?self::$locale->formatDateTime($date,'complete'):self::$locale->formatDate($date,'complete');
	}
	
	/**
	 * @param string|null
	 * @return string
	 */
	public static function simpleMonthAndYear($date=NULL){
		return self::$locale->formatMonthAndYear(strtotime($date),'simple');
	}
	
	/**
	 * @param string|null
	 * @return string
	 */
	public static function hoursAndMinutes($date=null){
		return self::$locale->formatTime(strtotime($date));
	}
	
	
	/**
	 * @param string|null
	 * @return string
	 */
	public static function toRSS($dateString){ return self::toRSSTime(strtotime($dateString)); }
	
	
	/**
	 * @param int
	 * @return string
	 */
	public static function toRSSTime($date){ return date('r',$date); }
	
	/**
	 * @param string|null
	 * @return string
	 */
	public static function toAtom($dateString){ return self::toAtomTime(strtotime($dateString));  }
	
	/**
	 * Atom format : Y-m-d\TH:i:s\Z
	 * 
	 * @param int
	 * @return string
	 */
	public static function toAtomTime($date){ return date('Y-m-d\TH:i:s\Z',$date); }
	
	/**
	 * TFC3339 format : Y-m-d\TH:i:sP
	 * 
	 * @param int
	 * @return string
	 */
	public static function toRFC3339Time($date){ return date('Y-m-d\TH:i:sP',$date); }
}
HTime::init();