<?php
/** Utils for Dates */
class UDate{
	
	/**
	 * Return the number of days in the month of a specific year
	 * 
	 * @param int a year
	 * @param int a month in the previous year
	 * @return int
	 */
	public static function getDaysInMonth($year,$month){
		$array=array(1=>31,(self::isLeapYear($year)?29:28),31,30,31,30,31,31,30,31,30,31);
		return $array[$month];
	}
	
	/**
	 * Return if the specified year is a leap year
	 * 
	 * @param int
	 * @return bool
	 */
	public static function isLeapYear($year){
		return (($year % 4 === 0 && $year % 100 !== 0) || $year % 400 === 0);
	}
	
	/**
	 * Add months to a time
	 * 
	 * @param int a time
	 * @param int a number of months
	 * @return int a time
	 */
	public static function addMonths_Time($time,$months){
		list($day,$month,$year)=explode('-',date('j-m-Y',$time),3);
		list($month,$year)=self::addMonths((int)$month,(int)$year,$months);
		return strtotime($year.'-'.$month.'-'.min($day,self::getDaysInMonth($year,$month)));
	}
	
	/**
	 * Remove months to a time
	 * 
	 * @param int a time
	 * @param int a number of months
	 * @return array [year, month, day]
	 */
	public static function removeMonths($time,$months){
		$day=date('j',$time);
		$month=date('m',$time) - $months;
		$year=date('Y',$time);
		while($month<1){
			$month+=12;
			$year--;
		}
		return array($year,$month,min($day,self::getDaysInMonth($year,$month)));
	}
	
	/**
	 * Remove months to a time
	 * 
	 * @param int a time
	 * @param int a number of months
	 * @return int a time
	 */
	public static function removeMonths_Time($time,$months){
		return strtotime(implode('-',self::removeMonths($time,$months)));
	}
	
	/**
	 * Add months
	 * 
	 * @param int
	 * @param int
	 * @param int a number of months
	 * @return array [year, month]
	 */
	public static function addMonths($month,$year,$months){
		$month+=$months;
		while($month>12){ $month-=12; $year++; }
		return array($month,$year);
	}
	
	
	/**
	 * Add days to a time
	 * 
	 * @param int
	 * @param int a number of days
	 * @return array [year, month, days]
	 */
	public static function addDays($time,$days){
		list($day,$month,$year,$thisMonthMaxDays)=explode('-',date('j-m-Y-t',$time),4);
		$day=(int)$day; $month=(int)$month; $year=(int)$year; $thisMonthMaxDays=(int)$thisMonthMaxDays;
		$day+=$days;
		while($day > $thisMonthMaxDays){
			$month++;
			if($month===12){ $month=1; $year++; }
			$day-=$thisMonthMaxDays;
			$thisMonthMaxDays=cal_days_in_month(CAL_GREGORIAN,$month,$year);
		}
		return array($year,$month,$day);
	}
	
	/**
	 * Remove days to a time
	 * 
	 * @param int
	 * @param int a number of days
	 * @return array [year, month, day]
	 */
	public static function removeDays($time,$days){
		list($day,$month,$year)=explode('-',date('j-m-Y',$time),3);
		$day=(int)$day; $month=(int)$month; $year=(int)$year;
		$day-=$days;
		while($day < 1){
			$month--;
			if($month===0){ $month=12; $year--; }
			$day=cal_days_in_month(CAL_GREGORIAN,$month,$year)+$day;
		}
		return array($year,$month,$day);
	}
	
	/**
	 * Add days to a time
	 * 
	 * @param int
	 * @param int a number of days
	 * @return int a time
	 */
	public static function removeDaysTime($time,$days){
		$date=self::removeDays($time,$days);
		return strtotime(implode('-',$date));
	}
}