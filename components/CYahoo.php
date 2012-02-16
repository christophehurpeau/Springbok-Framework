<?php
class CYahoo{
	public static function query($query){
		return json_decode(file_get_contents('http://query.yahooapis.com/v1/public/yql?q='.urlencode($query).'&format=json'));
	}
	public static function place($whoeid){
		//http://where.yahooapis.com/v1/place/2507854?appid=[yourappidhere]
	}
}
