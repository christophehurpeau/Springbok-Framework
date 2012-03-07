<?php
class HTime{
	private static $locale;
	
	public static function init(){
		self::$locale=App::getLocale();
	}
	
	public static function fromString($dateString){
		if(empty($dateString)) return false;
		/*if(is_integer($dateString) || is_numeric($dateString)) $date=(int)$dateString;
		else */$date = strtotime($dateString);
		return $date;
	}
	
	public static function nice($dateString,$hourAndMinutes=true){
		return self::niceTime(strtotime($dateString),$hourAndMinutes);
	}
	
	public static function niceTime($date=NULL,$hourAndMinutes=true){
		return $hourAndMinutes?self::$locale->formatDateTime($date,'nice'):self::$locale->formatDate($date,'nice');
	}
	
	public static function short($dateString,$hourAndMinutes=true){
		return self::shortTime(strtotime($dateString),$hourAndMinutes);
	}
	
	public static function shortTime($date=NULL,$hourAndMinutes=true){
		return $hourAndMinutes?self::$locale->formatDateTime($date,'short'):self::$locale->formatDate($date,'short');
	}
	
	public static function simple($dateString,$hourAndMinutes=true){
		return self::simpleTime(strtotime($dateString),$hourAndMinutes);
	}
	
	public static function simpleTime($date=NULL,$hourAndMinutes=true){
		return $hourAndMinutes?self::$locale->formatDateTime($date,'simple'):self::$locale->formatDate($date,'simple');
	}
	
	public static function compact($dateString=false,$hourAndMinutes=true){
		if($dateString===false) return '';
		return self::compactTime(strtotime($dateString),$hourAndMinutes);
	}
	
	public static function compactTime($date=NULL,$hourAndMinutes=true){
		return $hourAndMinutes?self::$locale->formatDateTime($date,'compact'):self::$locale->formatDate($date,'compact');
	}
	
	public static function complete($dateString=false,$hourAndMinutes=true){
		if($dateString===false) return '';
		return self::completeTime(strtotime($dateString),$hourAndMinutes);
	}
	
	public static function completeTime($date=NULL,$hourAndMinutes=true){
		return $hourAndMinutes?self::$locale->formatDateTime($date,'complete'):self::$locale->formatDate($date,'complete');
	}
	
	public static function simpleMonthAndYear($date=NULL){
		return self::$locale->formatMonthAndYear(strtotime($date),'simple');
	}
	
	public static function hoursAndMinutes($date=null){
		return self::$locale->formatTime(strtotime($date));
	}
	
	
	public static function toRSS($dateString){ return self::toRSSTime(strtotime($date)); }
	public static function toRSSTime($date){ return date('r',$date); }
	
	public static function toAtom($dateString){ return self::toAtomTime(strtotime($date));  }
	public static function toAtomTime($date){ return date('Y-m-d\TH:i:s\Z',$date); }
}
HTime::init();