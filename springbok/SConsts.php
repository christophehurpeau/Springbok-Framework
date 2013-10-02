<?php
/**
 * Springbok Constants
 */
class SConsts{
	/* GENDER */
	/**
	 * Unknown Gender
	 */
	const UNKNOWN = 0;
	/**
	 * Man Gender
	 */
	const MAN = 1;
	/**
	 * Woman Gender
	 */
	const WOMAN = 2;
	
	/**
	 * List of available genders
	 * 
	 * @return array
	 */
	public static function gender(){
		return array(self::UNKNOWN=>'Inconnu',self::MAN=>'Homme',self::WOMAN=>'Femme');
	}
	
	/**
	 * List of Genders icons
	 */
	public static function genderIcons(){
		return array(self::UNKNOWN=>'userSilhouette',self::MAN=>'userM',self::WOMAN=>'userF');
	}
}