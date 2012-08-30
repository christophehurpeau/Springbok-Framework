<?php
class SConsts{
	/* GENDER */
	const UNKNOWN=0,MAN=1,WOMAN=2;
	
	
	public static function gender(){
		return array(self::UNKNOWN=>'Inconnu',self::MAN=>'Homme',self::WOMAN=>'Femme');
	}
}