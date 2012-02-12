<?php
class CSimpleHttpClient{
	public static function get($url){
		$ch=curl_init($url);
		curl_setopt_array($ch,array(CURLOPT_HEADER=>false,CURLOPT_RETURNTRANSFER=>true));
		$res=curl_exec($ch);
		curl_close($ch);
		return $res;
	}
	
	public static function post($url){
		$ch=curl_init($url);
		curl_setopt($ch,CURLOPT_POST,true);
	}
}