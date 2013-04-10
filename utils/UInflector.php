<?php
class UInflector{
	public static function pluralize($word){
		include_once CORE.'libs/Inflect.php';
		return Inflect::pluralize($word);
	}
	
	public static function singularize($word){
		include_once CORE.'libs/Inflect.php';
		return Inflect::singularize($word);
	}
	
	public static function pluralizeUnderscoredWords($value){
		$words=explode('_',$value);
		$k=count($words)-1;
		$words[$k]=self::pluralize($words[$k]);
		return implode('_',$words);
	}
	public static function singularizeUnderscoredWords($value){
		$words=explode('_',$value);
		$k=count($words)-1;
		$words[$k]=self::singularize($words[$k]);
		return implode('_',$words);
	}

	public static function pluralizeCamelizedLastWord($value){
		if(preg_match('/(.*)([A-Z][a-z]+)$/U',$value,$m))
			return $m[1].self::pluralize($m[2]);
		return self::pluralize($value);
	}
}