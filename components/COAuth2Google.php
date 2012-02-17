<?php
/** https://code.google.com/apis/console#access
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
	
	public function retrieveMe(){
		return $this->me=CSimpleHttpClient::getJson('https://www.googleapis.com/oauth2/v1/userinfo?access_token='.$this->accessToken);
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
	
	public function updateUserInfo(&$user,&$googleUser){
		if($this->me===null) $this->retrieveMe();
		if(!$this->isValidMe()) return false;
		$user->first_name=$this->me['given_name'];
		$user->last_name=$this->me['family_name'];
		$googleUser->access_token=$this->accessToken;
		$googleUser->outdated=false;
		if(!empty($this->refreshToken)) $googleUser->refresh_token=$this->refreshToken;
		$googleUser->google_id=$this->me['id'];
		if(!empty($this->me['picture'])) $googleUser->picture=$this->me['picture'];
		if(!empty($this->me['email'])){
			$user->email=$this->me['email'];
			$user->email_verified = $this->me['verified_email'] ? true : false;
		}
		if(!empty($this->me['gender'])) $user->gender=$this->me['gender']==='male' ? AConsts::MAN : ($this->me['gender']==='female' ? AConsts::WOMAN : AConsts::UNKNOWN );
		return true;
	}
}