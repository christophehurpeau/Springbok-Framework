<?php
class COAuthWrap{
	private $urlAccess,$urlAuthorize,$key,$secret;
	public function __construct($urlAccess,$urlAuthorize,$key,$secret){
		$this->urlAccess=$urlAccess;
		$this->urlAuthorize=$urlAuthorize;
		$this->key=$key;
		$this->secret=$secret;
	}
	
	public static function hasVerificationCode(){
		return !empty($_GET['wrap_verification_code']);
	}
	
	public function createLoginUrl($callback=NULL){
		debugVar($callback);
		return $this->urlAuthorize. http_build_query(array(
			'wrap_client_id' => $this->key,
			'wrap_callback'	=> $callback===NULL ? urlencode("http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']) : HHtml::urlEscape($callback),
			'wrap_client_only' => 'true'
		)); 
	}
	
	public function verifyCode(){
		
	}
}
