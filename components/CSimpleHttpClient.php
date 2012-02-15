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
		return json_decode(self::get($url),true);
	}
	
	public static function post($url,$params){
		$ch=curl_init($url);
		curl_setopt_array($ch,array(CURLOPT_POST=>true,CURLOPT_HEADER=>false,CURLOPT_RETURNTRANSFER=>true));
		if(!empty($params)) curl_setopt($ch,CURLOPT_POSTFIELDS,http_build_query($params));
		$res=curl_exec($ch);
		curl_close($ch);
		return $res;
	}
}