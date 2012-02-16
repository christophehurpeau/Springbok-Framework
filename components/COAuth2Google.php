<?php
class COAuth2Google extends CAbstractOAuthConnect{
	public static function redirectForConnection($url,$state,$scope){
		Controller::redirect('https://accounts.google.com/o/oauth2/auth?client_id='.Config::$facebook_appId.'&redirect_uri='.urlencode($url).'&state='.$state.(empty($scope)?'':'&scope='.$scope));
	}
	
	
	public static function getAccessTokens($url,$code){
		$token_url='https://accounts.google.com/o/oauth2/token?client_id='.Config::$google_appId.'&redirect_uri='.urlencode($url).'&client_secret='.urlencode(Config::$google_secret).'&code='.$code.'&grant_type=authorization_code';
		return CSimpleHttpClient::getJson($token_url);
	}
	
	public static function refreshToken($url,$refreshToken){
		$token_url='https://accounts.google.com/o/oauth2/token?client_id='.Config::$google_appId.'&redirect_uri='.urlencode($url).'&client_secret='.urlencode(Config::$google_secret).'&refresh_token='.$refreshToken.'&grant_type=refresh_token';
		return CSimpleHttpClient::getJson($token_url);
	}
	
}