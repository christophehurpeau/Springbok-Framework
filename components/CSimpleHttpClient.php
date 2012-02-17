<?php
class CSimpleHttpClient{
	public static function get($url){
		$ch=curl_init($url);
		curl_setopt_array($ch,array(CURLOPT_HEADER=>false,CURLOPT_RETURNTRANSFER=>true));
		$res=curl_exec($ch);
		curl_close($ch);
		return $res;
	}
	public static function getJson($url){
		/*print_r(self::get($url));*/
		return json_decode(self::get($url),true);
	}
	
	public static function post($url,$params){
		$ch=curl_init($url);
		curl_setopt_array($ch,array(CURLOPT_POST=>true,CURLOPT_HEADER=>false,CURLOPT_RETURNTRANSFER=>true));
		if(!empty($params)) curl_setopt($ch,CURLOPT_POSTFIELDS,is_array($params)?http_build_query($params):$params);
		$res=curl_exec($ch);
		curl_close($ch);
		return $res;
	}
	public static function postJson($url,$params){
		return json_decode(self::post($url,$params),true);
	}
}