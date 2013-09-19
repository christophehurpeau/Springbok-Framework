<?php
/** Time utils */
class UTime{
	/**
	 * Parse a duration and returns a time
	 * 
	 * @param string
	 * @return int
	 */
	public static function parseDuration($duration=null){
		if ($duration === NULL) return 60 * 60 * 24 * 30;
		$toAdd = -1; $matches=array();
		if(preg_match('^(\d+)d$',$duration,$matches))
			$toAdd=((int)$matches[1]) * (60 * 60) * 24;
		elseif(preg_match('^(\d+)h$',$duration,$matches))
			$toAdd=((int)$matches[1]) * (60 * 60);
		elseif(preg_match('^(\d+)mi?n$',$duration,$matches))
			$toAdd=((int)$matches[1]) * (60);
		elseif(preg_match('^(\d+)s$',$duration,$matches))
			$toAdd=((int)$matches[1]);
		if ($toAdd == -1)
			throw new Exception("Invalid duration pattern : " . $duration);
		return $toAdd;
	}
	
	const REG_EXPR_HOURS_MINUTES='((?:(?:(\d+)(?:hours?|h))(?:\s*(\d+)(?:mins?|m)?)?)|(?:(\d+)(?:mins?|m))|(?:(\d+(?:[\.,]\d+)?)(?:hours?|h)))';
	
	/**
	 * Parse duration in hours and minutes
	 * 
	 * @param string
	 * @return float hours
	 */
	public static function parseHoursDuration($hours){
		if(preg_match('/(\d+[\.,]\d+)(?:hours?|h)/i',$hours,$m)) return (float)str_replace(',','.',$m[1]);
		if(preg_match('/(?:(\d+)(?:hours?|h))(?:\s*(\d+)(?:mins?|m)?)?/i',$hours,$m)) return $m[1]+(empty($m[2])?0:($m[2]/60));
		if(preg_match('/(\d+)(?:mins?|m)/i',$hours,$m)) return ($m[1]/60);
		return false;
	}
}
