<?php
/**
 * OAuth 2 authentification
 */
abstract class COAuth2{
	public static function redirectForConnection($url,$state,$scope,$params=''){
		Controller::redirect(static::$OAUTH_URL.'?client_id='.static::appId().'&redirect_uri='.urlencode($url).'&response_type=code&state='.$state.(empty($params)?'':$params).(empty($scope)?'':'&scope='.$scope));
	}
	
	public static function getTokens($url,$code){
		return CSimpleHttpClient::postJson(static::$TOKEN_URL,'client_id='.static::appId().'&redirect_uri='.urlencode($url).'&client_secret='.urlencode(static::secret()).'&code='.$code.'&grant_type=authorization_code');
	}
	
	public static function refreshTokens($url,$refreshToken){
		return CSimpleHttpClient::postJson(static::$TOKEN_URL,'client_id='.static::appId().'&redirect_uri='.urlencode($url).'&client_secret='.urlencode(static::secret()).'&refresh_token='.$refreshToken.'&grant_type=refresh_token');
	}
	
	protected $accessToken,$refreshToken;
	public function __construct($tokens){
		if(empty($tokens['access_token'])) throw new Exception('No access token');
		$this->accessToken=$tokens['access_token'];
		if(!empty($tokens['refresh_token'])) $this->refreshToken=$tokens['refresh_token'];
	}
}