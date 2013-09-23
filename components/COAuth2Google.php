<?php
/**
 * Google connect, using OAuth 2
 * 
 * Set in your config/_.php the following configuration : google_appId, google_secret
 * 
 * https://code.google.com/apis/console#access
 * http://code.google.com/intl/fr-FR/apis/accounts/docs/OAuth2WebServer.html
 * http://code.google.com/intl/fr-FR/apis/accounts/docs/OAuth2Login.html
 * 
 */
class COAuth2Google extends COAuth2Connect{
	protected static $OAUTH_URL='https://accounts.google.com/o/oauth2/auth',$TOKEN_URL='https://accounts.google.com/o/oauth2/token',$API_URL='https://www.googleapis.com/oauth2/v1/',
		$CONFIG_PREFIX='google';
	
	protected static function appId(){ return Config::$google_appId; }
	protected static function secret(){ return Config::$google_secret; }
	
	public static function redirectForConnection($url,$state,$scope,$offline=false){
		return parent::redirectForConnection($url,$state,$scope,$offline?'&access_type=offline&approval_prompt=force':'');
	}
	
	public static function refreshTokens($refreshToken,$NULL=null){
		return CSimpleHttpClient::postJson(static::$TOKEN_URL,'client_id='.static::appId().'&client_secret='.urlencode(static::secret()).'&refresh_token='.$refreshToken.'&grant_type=refresh_token');
	}
	
	public function retrieveMe(){
		return $this->me=CSimpleHttpClient::getJson('https://www.googleapis.com/oauth2/v1/userinfo?access_token='.$this->accessToken);
	}
	
	public function allContacts($minUpdated=null){
		return CSimpleHttpClient::getJson('https://www.google.com/m8/feeds/contacts/default/full?v=3&alt=json'.($minUpdated===null?'':'&$min-updated='.HTime::toRFC3339Time($minUpdated)).'&max-results=99999&access_token='.$this->accessToken);
	}
	
	
	public function createUser(){
		$user=new User();
		$googleUser=new UserGoogle();
		if($this->updateUserInfo($user,$googleUser)){
			$user->insert();
			$googleUser->user_id=$user->id;
			$googleUser->insert();
			return true;
		}
		return false;
	}
	
	public function updateUserInfo($user,$googleUser){
		if($this->me===null) $this->retrieveMe();
		if(!$this->isValidMe()) return false;
		if(!empty($this->me['given_name'])) $user->first_name=$this->me['given_name'];
		if(!empty($this->me['family_name'])) $user->last_name=$this->me['family_name'];
		$googleUser->access_token=$this->accessToken;
		$googleUser->outdated=false;
		if(!empty($this->refreshToken)) $googleUser->refresh_token=$this->refreshToken;
		$googleUser->google_id=$this->me['id'];
		if(!empty($this->me['picture'])) $googleUser->picture=$this->me['picture'];
		if(!empty($this->me['email'])){
			$user->email=$this->me['email'];
			$user->email_verified = $this->me['verified_email'] ? true : false;
		}
		if(!empty($this->me['gender'])) $user->gender=$this->me['gender']==='male' ? SConsts::MAN : ($this->me['gender']==='female' ? SConsts::WOMAN : SConsts::UNKNOWN );
		return true;
	}
}