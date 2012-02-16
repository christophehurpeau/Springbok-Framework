<?php
/**
* http://msdn.microsoft.com/fr-fr/library/hh243647.aspx
* http://msdn.microsoft.com/fr-fr/library/hh243649.aspx
* scopes : http://msdn.microsoft.com/en-us/library/hh243646.aspx
* user api : http://msdn.microsoft.com/en-us/library/hh243648.aspx#user
*/
class COAuth2Live extends CAbstractOAuthConnect{
	public static function redirectForConnection($url,$state,$scope='wl.basic%20wl.signin%20wl.offline_access'){
		Controller::redirect('https://oauth.live.com/authorize?client_id='.Config::$wlive_appId.'&scope='.$scope.'&response_type=code&redirect_uri='.urlencode($url).'&state='.$state);
	}
	
	public static function getAccessTokens($url,$code){
		$token_url='https://oauth.live.com/token?client_id='.Config::$wlive_appId.'&redirect_uri='.urlencode($url).'&client_secret='.urlencode(Config::$wlive_secret).'&code='.$code.'&grant_type=authorization_code';
		return CSimpleHttpClient::getJson($token_url);
	}
	
	public static function refreshToken($url,$refreshToken){
		$token_url='https://oauth.live.com/token?client_id='.Config::$wlive_appId.'&redirect_uri='.urlencode($url).'&client_secret='.urlencode(Config::$wlive_secret).'&refresh_token='.$refreshToken.'&grant_type=refresh_token';
		return CSimpleHttpClient::getJson($token_url);
	}
	
	public function __construct($accessTokens,$retrieveMe=false){
		parent::__construct($accessTokens['access_token'],$retrieveMe);
		if(!empty($accessTokens['refresh_token'])) $this->refreshToken=$accessTokens['refresh_token'];
	}

	public function retrieveMe(){
		$graph_url = "https://apis.live.net/v5.0/me?access_token=".$this->accessToken;
		$this->me=CSimpleHttpClient::getJson($graph_url);
		return $this->me;
	}
	
	
	public function createUser(){
		$user=new User();
		$wliveUser=new UserWLive();
		if($this->updateUserInfo($user,$wliveUser)){
			$user->insert();
			$wliveUser->user_id=$user->id;
			$wliveUser->insert();
			return true;
		}
		return false;
	}
	
	public function updateUserInfo(&$user,&$wliveUser){
		if($this->me===null) $this->retrieveMe();
		if(!$this->isValidMe()) return false;
		$user->first_name=$this->me['first_name'];
		$user->last_name=$this->me['last_name'];
		$wliveUser->access_token=$this->accessToken;
		$wliveUser->outdated=false;
		if(!empty($this->refreshToken)) $wliveUser->refresh_token=$this->refreshToken;
		$wliveUser->wlive_id=$this->me['id'];
		if(!empty($this->me['link'])) $wliveUser->link=$this->me['link'];
		if(!empty($this->me['emails'])){
			$email=null;
			if(!empty($this->me['emails']['preferred'])) $email=$this->me['emails']['preferred'];
			elseif(!empty($this->me['emails']['account'])) $email=$this->me['emails']['account'];
			if(!empty($email)) $user->email=$email;
			if(!empty($this->me['emails']['account'])) $wliveUser->email=$this->me['emails']['account'];
		}
		if(!empty($this->me['gender'])) $user->gender=$this->me['gender']==="male" ? AConsts::MAN : ($this->me['gender']==='female' ? AConsts::WOMAN : AConsts::UNKNOWN );
		return true;
	}
}
