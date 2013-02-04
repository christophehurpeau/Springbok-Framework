<?php
class CFacebookSignedRequest{
	
	/* https://developers.facebook.com/docs/howtos/login/signed-request/ */
	public static function hasSignedRequest(){ return isset($_POST['signed_request']); }
	
	public static function parse(){
		$signed_request = $_POST['signed_request'];
		list($encoded_sig, $payload) = explode('.', $signed_request, 2); 

		// decode the data
		$sig = self::base64_url_decode($encoded_sig);
		$data = json_decode(self::base64_url_decode($payload), true);
		
		if (strtoupper($data['algorithm']) !== 'HMAC-SHA256')
			throw new Exception('Unknown algorithm. Expected HMAC-SHA256');
		
		$expected_sig = hash_hmac('sha256', $payload, Config::$facebook_secret, true);
		if($sig !== $expected_sig) throw new Exception('Bad Signed JSON signature !');

		return new CFacebookSignedRequest($data);
	}
	public static function base64_url_decode($input) {
		return base64_decode(strtr($input, '-_', '+/'));
	}
	
	private $data;
	public function __contruct($data){
		$this->data=$data;
		$this->me=$data['registration'];
	}
	
	public function me($name){
		return $this->me[$name];
	}
	
	/* COPY FROM COAuth2Facebook except $facebookUser->access_token=$this->data['oauth_token']; */
	public function updateUserInfo($user,$facebookUser){
		if($this->me===null) $this->retrieveMe();
		if(!$this->isValidMe()) return false;
		if(!empty($this->me['first_name'])) $user->first_name=$this->me['first_name'];
		if(!empty($this->me['last_name'])) $user->last_name=$this->me['last_name'];
		$facebookUser->access_token=$this->data['oauth_token'];
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