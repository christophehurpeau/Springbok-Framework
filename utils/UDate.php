<?php
class UDate{
	public static function getDaysInMonth($year,$month){
		$array=array(1=>31,(self::isLeapYear($year)?29:28),31,30,31,30,31,31,30,31,30,31);
		return $array[$month];
	}
	public static function isLeapYear($year){
		return (($year % 4 === 0 && $year % 100 !== 0) || $year % 400 === 0);
	}
	
	public static function addMonths_Time($time,$months){
		list($day,$month,$year)=explode('-',date('j-m-Y',$time),3);
		list($month,$year)=self::addMonths((int)$month,(int)$year,$months);
		return strtotime($year.'-'.$month.'-'.min($day,self::getDaysInMonth($year,$month)));
	}
	
	public static function removeMonths_Time($time,$months){
		$day=date('j',$time);
		$month=date('m',$time) - $months;
		$year=date('Y',$time);
		while($month<1){
			$month+=12;
			$year--;
		}
		return strtotime($year.'-'.$month.'-'.min($day,self::getDaysInMonth($year,$month)));
	}
	
	public static function addMonths($month,$year,$months){
		$month+=$months;
		while($month>12){ $month-=12; $year++; }
		return array($month,$year);
	}
	
	
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
	public static function removeDays($time,$days){
		list($day,$month,$year)=explode('-',date('j-m-Y',$time),3);
		$day=(int)$day; $month=(int)$month; $year=(int)$year;
		$day-=$days;
		while($day < 1){
			$month--;
			if($month===0){ $month=12; $year--; }
			$day=cal_days_in_month(CAL_GREGORIAN,$month,$year)-$day;
		}
		return array($year,$month,$day);
	}
	public static function removeDaysTime($time,$days){
		$date=self::removeDays($time,$days);
		return strtotime(implode('-',$date));
	}
}