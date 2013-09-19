<?php
/** Inflector Utils */
	include_once CORE.'libs/Inflect.php';
class UInflector{
	/**
	 * Pluralize a word
	 * 
	 * @param string
	 * @return string
	 */
	public static function pluralize($word){
		return Inflect::pluralize($word);
	}
	
	/**
	 * Singularize a word
	 * 
	 * @param string
	 * @return string
	 */
	public static function singularize($word){
		return Inflect::singularize($word);
	}
	
	/**
	 * Pluralize the last word of words separated by "_"
	 * 
	 * @param string
	 * @return string
	 */
	public static function pluralizeUnderscoredWords($value){
		$words=explode('_',$value);
		$k=count($words)-1;
		$words[$k]=self::pluralize($words[$k]);
		return implode('_',$words);
	}
	
	/**
	 * Singularize the last word of words separated by "_"
	 * 
	 * @param string
	 * @return string
	 */
	public static function singularizeUnderscoredWords($value){
		$words=explode('_',$value);
		$k=count($words)-1;
		$words[$k]=self::singularize($words[$k]);
		return implode('_',$words);
	}

	/**
	 * Pluralize the last word of camelized words
	 * 
	 * @param string
	 * @return string
	 */
	public static function pluralizeCamelizedLastWord($value){
		if(preg_match('/(.*)([A-Z][a-z]+)$/U',$value,$m))
			return $m[1].self::pluralize($m[2]);
		return self::pluralize($value);
	}
}