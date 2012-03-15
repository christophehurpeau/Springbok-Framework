<?php
class UDate{
	public static function getDaysInMonth($year,$month){
		$array=array(1=>31,(self::isLeapYear($year)?29:28),31,30,31,30,31,31,30,31,30,31);
		return $array[$month];
	}
	public static function isLeapYear($year){
		return (($year % 4 === 0 && $year % 100 !== 0) || $year % 400 === 0);
	}
	
	public static function addMonths($time,$months){
		$day=date('j',$time);
		$month=date('m',$time) + $months;
		$year=date('Y',$time);
		while($month>12){
			$month-=12;
			$year++;
		}
		return strtotime($year.'-'.$month.'-'.min($day,self::getDaysInMonth($year,$month)));
	}
	
	public static function removeMonths($time,$months){
		$day=date('j',$time);
		$month=date('m',$time) - $months;
		$year=date('Y',$time);
		while($month<0){
			$month+=12;
			$year--;
		}
		return strtotime($year.'-'.$month.'-'.min($day,self::getDaysInMonth($year,$month)));
	}
}