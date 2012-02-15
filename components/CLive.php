<?php
/**
* http://msdn.microsoft.com/fr-fr/library/hh243647.aspx
* http://msdn.microsoft.com/fr-fr/library/hh243649.aspx
* scopes : http://msdn.microsoft.com/en-us/library/hh243646.aspx
*/
class CLive extends CAbstractOAuthConnect{
	public static function redirectForConnection($url,$state,$scope='wl.basic%20wl.signin%20wl.offline'){
		Controller::redirect('https://oauth.live.com/authorize?client_id='.Config::$wlive_appId.'&scope='.$scope.'&response_type=code&redirect_uri='.urlencode($url).'&state='.$state);
	}
	
	public static function getAccessTokens($url,$code){
		$token_url='https://oauth.live.com/token?client_id='.Config::$wlive_appId.'&redirect_uri='.urlencode($url).'&client_secret='.urlencode(Config::$wlive_secret).'&code='.$code.'&grant_type=authorization_code';
		return CSimpleHttpClient::getJson($token_url);
	}
	
	public static function refreshToken($url,$code,$params){
		$token_url='https://oauth.live.com/token?client_id='.Config::$wlive_appId.'&redirect_uri='.urlencode($url).'&client_secret='.urlencode(Config::$wlive_secret).'&code='.$code.'&grant_type=authorization_code';
		return CSimpleHttpClient::getJson($token_url);
	}
	
	public function __construct($accessTokens,$retrieveMe=false){
		parent::__construct($accessTokens['access_token'],$retrieveMe);
		if(!empty($this->refreshToken)) $this->refreshToken=$accessTokens['refresh_token'];
	}

	public function retrieveMe(){
		$graph_url = "https://apis.live.net/v5.0/me?access_token=".$this->accessToken;
		$this->me=CSimpleHttpClient::getJson($graph_url);
		return $this->me;
	}
}
