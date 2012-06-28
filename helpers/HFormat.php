<?php
class HFormat{
	
	public static function price($price,$currency=array()){
		//TODO : money_format
		$currency=$currency+Config::$default_currency;
		$blank = ($currency['blank'] ? ' ' : '');
		if (($isNegative = ($price < 0))) $price *= -1;
		//$price = self::round($price, $currency['decimals']);
		switch ($currency['format']){
	 	 	/* X 0,000.00 */
	 	 	case 1:
				$ret = $currency['sign'].$blank.number_format($price,$currency['decimals'], '.', ',');
				break;
			/* 0 000,00 X*/
			case 2:
				$ret = number_format($price,$currency['decimals'], ',', ' ').$blank.$currency['sign'];
				break;
			/* X 0.000,00 */
			case 3:
				$ret = $currency['sign'].$blank.number_format($price,$currency['decimals'], ',', '.');
				break;
			/* 0,000.00 X */
			case 4:
				$ret = number_format($price,$currency['decimals'], '.', ',').$blank.$currency['sign'];
				break;
			default:
				$ret = 0;
		}
		if ($isNegative) $ret = '-'.$ret;
		return $ret;
	}
	
	public static function decimal($val,$decimals=2){
		$config=App::getLocale()->data('decimalFormat');
		return number_format($val,$decimals,$config['decimal_sep'],$config['thousands_sep']);
	}
	public static function percent($val,$decimals=0){
		return sprintf(App::getLocale()->data('percentFormat'),self::decimal($val,$decimals));
	}
	
	
	public static function starsImg($val,$max=5,$imgEmpty='/stars/empty.png',$imgFull='/stars/full.png'){
		if($val==='') $val=0;
		$imgFull=HHtml::img($imgFull); $imgEmpty=HHtml::img($imgEmpty);
		return str_repeat($imgFull,$val).str_repeat($imgEmpty,$max-$val);
	}
	
	public static function starsIcon($val,$max=5,$classEmpty='star_empty',$classFull='star_full'){
		if($val==='') $val=0;
		$spanFull='<span class="icon '.$classFull.'"></span>'; $spanEmpty='<span class="icon '.$classEmpty.'"></span>';
		return str_repeat($spanFull,$val).str_repeat($spanEmpty,$max-$val);
	}
	
	public static function stars($val,$max=5){
		if($val==='') $val=0;
		$spanFull='<span class="rating"></span>'; $spanEmpty='<span></span>';
		return '<span class="stars">'.str_repeat($spanFull,$val).str_repeat($spanEmpty,$max-$val).'</span>';
	}

	public static function datetime($time){
		return $time===null?'':HTime::completeTime($time,true);
	}
	
	public static function date($time){
		return $time===null?'':HTime::completeTime($time,false);
	}
	
	
	public static function datetime_($time){
		return $time===null?'':HTime::complete($time,true);
	}
	
	public static function date_($time){
		return $time===null?'':HTime::complete($time,false);
	}
}
	