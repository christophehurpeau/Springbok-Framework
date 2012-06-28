<?php
class CLocale{
	private static $instances;
	
	/** @return CLocale */
	public static function get($locale){
		if(isset(self::$instances[$locale])) return self::$instances[$locale];
		return self::$instances[$locale]=new CLocale($locale);
	}
	
	private $locale,$data;
	private function __construct($locale){
		$this->locale=$locale;
		$this->data=include CORE.'i18n'.DS.$locale.'.php';
	}
	
	public function data($name){
		return $this->data[$name];
	}
	
	public function formatDate($time,$type='simple'){
		return $this->data['dates']['formats']['date'][$type]($this,$time);
	}
	
	public function formatTime($time,$type='simple'){
		return $this->data['dates']['formats']['time'][$type]($this,$time);
	}
	
	public function formatDateTime($time,$type='simple'){
		return $this->data['dates']['formats']['datetime'][$type]($this,$time);
	}
	
	public function formatMonthAndYear($time,$type='simple'){
		return $this->data['dates']['formats']['monthAndYear'][$type]($this,$time);
	}
	
	public function monthName($month,$type='full'){
		return $this->data['dates']['monthNames'][$type][$month];
	}
	public function weekDayName($week,$type='full'){
		return $this->data['dates']['weekDayNames'][$type][$week];
	}
	public function periodName($num,$type='full'){
		return $this->data['dates']['periodNames'][$type][$num];
	}
	
	public function monthNum($month,$type='full'){
		return array_search($month,$this->data['dates']['monthNames'][$type]);
	}
	
	public function isPlural($number){
		return $this->data['isPlural']($number);
	}
}
