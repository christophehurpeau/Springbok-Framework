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
	
	/**
	 * @param string
	 * @return mixed
	 */
	public function data($name){
		return $this->data[$name];
	}
	
	/**
	 * Format a date for this locale
	 * 
	 * @param int
	 * @param string
	 * @return string
	 */
	public function formatDate($time,$type='simple'){
		return $this->data['formatDate'.ucfirst($type)]($this,$time);
	}
	
	/**
	 * Format a time for this locale
	 * 
	 * @param int
	 * @param string
	 * @return string
	 */
	public function formatTime($time,$type='simple'){
		return $this->data['formatTime'.ucfirst($type)]($this,$time);
	}
	
	/**
	 * Format a datetime for this locale
	 * 
	 * @param int
	 * @param string
	 * @return string
	 */
	public function formatDateTime($time,$type='simple'){
		return $this->data['formatDatetime'.ucfirst($type)]($this,$time);
	}
	
	/**
	 * Format a month and year
	 * 
	 * @param int
	 * @param string
	 * @return string
	 */
	public function formatMonthAndYear($time,$type='simple'){
		return $this->data['formatMonthAndYear'.ucfirst($type)]($this,$time);
	}
	
	
	/**
	 * Format a date for this locale
	 * 
	 * @param int
	 * @return string
	 */
	public function formatDateNice($time){return $this->data['formatDateNice']($this,$time);}
	/**
	 * Format a date for this locale
	 * 
	 * @param int
	 * @return string
	 */
	public function formatDateShort($time){return $this->data['formatDateNice']($this,$time);}
	/**
	 * Format a date for this locale
	 * 
	 * @param int
	 * @return string
	 */
	public function formatDateSimple($time){return $this->data['formatDateSimple']($this,$time);}
	/**
	 * Format a date for this locale
	 * 
	 * @param int
	 * @return string
	 */
	public function formatDateCompact($time){return $this->data['formatDateCompact']($this,$time);}
	/**
	 * Format a date for this locale
	 * 
	 * @param int
	 * @return string
	 */
	public function formatDateComplete($time){return $this->data['formatDateComplete']($this,$time);}
	/**
	 * Format a date for this locale
	 * 
	 * @param int
	 * @return string
	 */
	public function formatTimeSimple($time){return $this->data['formatTimeSimple']($this,$time);}
	/**
	 * Format a date for this locale
	 * 
	 * @param int
	 * @return string
	 */
	public function formatTimeComplete($time){return $this->data['formatTimeComplete']($this,$time);}
	/**
	 * Format a date for this locale
	 * 
	 * @param int
	 * @return string
	 */
	public function formatDatetimeNice($time){return $this->data['formatDatetimeNice']($this,$time);}
	/**
	 * Format a date for this locale
	 * 
	 * @param int
	 * @return string
	 */
	public function formatDatetimeShort($time){return $this->data['formatDatetimeShort']($this,$time);}
	/**
	 * Format a date for this locale
	 * 
	 * @param int
	 * @return string
	 */
	public function formatDatetimeSimple($time){return $this->data['formatDatetimeSimple']($this,$time);}
	/**
	 * Format a date for this locale
	 * 
	 * @param int
	 * @return string
	 */
	public function formatDatetimeCompact($time){return $this->data['formatDatetimeCompact']($this,$time);}
	/**
	 * Format a date for this locale
	 * 
	 * @param int
	 * @return string
	 */
	public function formatDatetimeComplete($time){return $this->data['formatDatetimeComplete']($this,$time);}
	
	/**
	 * Format a month
	 * 
	 * @param int
	 * @param string
	 * @return string
	 */
	public function monthName($month,$type='full'){
		return $this->data['dates']['monthNames'][$type][$month];
	}
	
	/**
	 * Format a week day
	 * 
	 * @param int
	 * @param string
	 * @return string
	 */
	public function weekDayName($week,$type='full'){
		return $this->data['dates']['weekDayNames'][$type][$week];
	}

	/**
	 * Format a period name
	 * 
	 * @param int
	 * @param string
	 * @return string
	 */
	public function periodName($num,$type='full'){
		return $this->data['dates']['periodNames'][$type][$num];
	}
	
	/**
	 * Format a month
	 * 
	 * @param int
	 * @param string
	 * @return string
	 */
	public function monthNum($month,$type='full'){
		return array_search($month,$this->data['dates']['monthNames'][$type]);
	}
	
	/**
	 * Tells if a number is a plural (in english, 0 is a plural but not in french)
	 * 
	 * @param int
	 * @param string
	 * @return string
	 */
	public function isPlural($number){
		return $this->data['isPlural']($number);
	}
}
