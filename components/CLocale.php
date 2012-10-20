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
		return $this->data['formatDate'.ucfirst($type)]($this,$time);
	}
	
	public function formatTime($time,$type='simple'){
		return $this->data['formatTime'.ucfirst($type)]($this,$time);
	}
	
	public function formatDateTime($time,$type='simple'){
		return $this->data['formatDatetime'.ucfirst($type)]($this,$time);
	}
	
	public function formatMonthAndYear($time,$type='simple'){
		return $this->data['formatMonthAndYear'.ucfirst($type)]($this,$time);
	}
	
	
	public function formatDateNice($time){return $this->data['formatDateNice']($this,$time);}
	public function formatDateShort($time){return $this->data['formatDateNice']($this,$time);}
	public function formatDateSimple($time){return $this->data['formatDateSimple']($this,$time);}
	public function formatDateCompact($time){return $this->data['formatDateCompact']($this,$time);}
	public function formatDateComplete($time){return $this->data['formatDateComplete']($this,$time);}
	public function formatTimeSimple($time){return $this->data['formatTimeSimple']($this,$time);}
	public function formatTimeComplete($time){return $this->data['formatTimeComplete']($this,$time);}
	public function formatDatetimeNice($time){return $this->data['formatDatetimeNice']($this,$time);}
	public function formatDatetimeShort($time){return $this->data['formatDatetimeShort']($this,$time);}
	public function formatDatetimeSimple($time){return $this->data['formatDatetimeSimple']($this,$time);}
	public function formatDatetimeCompact($time){return $this->data['formatDatetimeCompact']($this,$time);}
	public function formatDatetimeComplete($time){return $this->data['formatDatetimeComplete']($this,$time);}
	
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
