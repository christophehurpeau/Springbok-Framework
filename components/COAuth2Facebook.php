<?php
/*include CLIBS.'facebook/facebook.php';*/
/**
 * Facebook connect, using OAuth 2
 * 
 * Set in your config/_.php the following configuration : facebook_appId, facebook_secret
 */
class COAuth2Facebook extends COAuth2Connect{
	/*private $facebook;
	
	public function __construct(){
		$this->facebook=new Facebook(array('appId'=>Config::$facebook_appId,'secret'=>Config::$facebook_secret));
	}
	
	public function fql($query){
		return $this->facebook->api(array('method' => 'fql.query','query' => $fql));
	}
	
	public function getFriends(){
		return $this->fql('SELECT name from user where uid = ' . $user_id);
	}
	*/
	
	protected static $OAUTH_URL='https://www.facebook.com/dialog/oauth',$TOKEN_URL='https://graph.facebook.com/oauth/access_token',$API_URL='https://graph.facebook.com';
	
	protected static function appId(){ return Config::$facebook_appId; }
	protected static function secret(){ return Config::$facebook_secret; }
	
	
	public static function getTokens($url,$code){
		$token_url=self::$TOKEN_URL.'?client_id='.Config::$facebook_appId.'&redirect_uri='.urlencode($url).'&client_secret='.Config::$facebook_secret.'&code='.$code;
		$response=CSimpleHttpClient::get($token_url);
		parse_str($response,$params);
		return $params;
	}
	
	public function createUser(){
		$user=new User();
		$facebookUser=new UserFacebook();
		if($this->updateUserInfo($user,$facebookUser)){
			$user->insert();
			$facebookUser->user_id=$user->id;
			$facebookUser->insert();
			return true;
		}
		return false;
	}
	
	public function updateUserInfo(&$user,$facebookUser){
		if($this->me===null) $this->retrieveMe();
		if(!$this->isValidMe()) return false;
		if(!empty($this->me['first_name'])) $user->first_name=$this->me['first_name'];
		if(!empty($this->me['last_name'])) $user->last_name=$this->me['last_name'];
		$facebookUser->access_token=$this->accessToken;
		$facebookUser->outdated=false;
		$facebookUser->facebook_id=$this->me['id'];
		if(!empty($this->me['username'])) $facebookUser->facebook_username=$this->me['username'];
		$facebookUser->link=$this->me['link'];
		$user->email=$facebookUser->email= (isset($this->me['email']) ? $this->me['email'] : null);
		if(!empty($this->me['gender'])) $user->gender=$this->me['gender']==="male" ? SConsts::MAN : ($this->me['gender']==='female' ? SConsts::WOMAN : SConsts::UNKNOWN );
		if(!empty($this->me['verified'])) $facebookUser->facebook_verified=$this->me['verified'];
		return true;
	}
}
