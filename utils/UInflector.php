<?php
class UInflector{
	public static function underscore($word){
		$word=preg_replace('/([A-Z]+|[0-9]+)([A-Z][a-z])/','$1_$2',$word);
		$word=preg_replace('/([a-z])([A-Z]|[0-9])/','$1_$2',$word);
		return strtolower($word);
	}
	
	public static function camelize($value,$startWithLowercase=false){
		$values=explode('_',$value);
		$value=array_shift($values);
		$res=($startWithLowercase?$value:ucfirst($value));
		foreach($values as $value) $res.=ucfirst($value);
		return $res;
	}
	
	public static function pluralize($word){
		include_once CLIBS.'Inflect.php';
		return Inflect::pluralize($word);
	}
	
	public static function singularize($word){
		include_once CLIBS.'Inflect.php';
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