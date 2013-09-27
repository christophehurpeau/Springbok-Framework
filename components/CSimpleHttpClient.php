<?php
/**
 * A simple HttpClient, using curl
 */
class CSimpleHttpClient{
	
	/**
	 * @param string
	 * @param int
	 * @return string
	 */
	public static function get($url,$timeout=3){
		$ch=curl_init($url);
		curl_setopt_array($ch,array(CURLOPT_HEADER=>false,CURLOPT_RETURNTRANSFER=>true,CURLOPT_CONNECTTIMEOUT=>1,CURLOPT_TIMEOUT=>$timeout));
		$res=curl_exec($ch);
		curl_close($ch);
		return $res;
	}
	
	/**
	 * @param string
	 * @param array
	 * @param int
	 * @return string
	 */
	public static function getWithQuery($url,$params,$timeout=3){
		return self::get($url.'?'.http_build_query($params),$timeout);
	}
	
	
	/**
	 * @param string
	 * @param int
	 * @return mixed
	 */
	public static function getJson($url,$timeout=3){
		/*print_r(self::get($url));*/
		return json_decode(self::get($url,$timeout),true);
	}
	
	
	/**
	 * @param string
	 * @param array
	 * @param int
	 * @return string
	 */
	public static function post($url,$params,$timeout=3){
		$ch=curl_init($url);
		curl_setopt_array($ch,array(CURLOPT_POST=>true,CURLOPT_HEADER=>false,CURLOPT_RETURNTRANSFER=>true,CURLOPT_CONNECTTIMEOUT=>1,CURLOPT_TIMEOUT=>$timeout));
		if(!empty($params)) curl_setopt($ch,CURLOPT_POSTFIELDS,is_array($params)?http_build_query($params):$params);
		$res=curl_exec($ch);
		curl_close($ch);
		return $res;
	}
	
	/**
	 * @param string
	 * @param array
	 * @param int
	 * @return mixed
	 */
	public static function postJson($url,$params,$timeout=3){
		return json_decode(self::post($url,$params,$timeout),true);
	}
}