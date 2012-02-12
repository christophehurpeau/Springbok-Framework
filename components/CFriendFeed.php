<?php
include_once CLIBS.'oauth.php';
include CLIBS.'friendfeed.php';

class CFriendFeed{
	const FRIENDFEED_OAUTH_BASE = 'https://friendfeed.com/account/oauth';
	
	const OAUTH_WRAP_ACCESS='https://friendfeed.com/account/wrap/access_token';
	const OAUTH_WRAP_AUTHORIZE='https://friendfeed.com/account/wrap/authorize';
	const API_URL = 'http://friendfeed-api.com/v2/';
	
	private static $oAuthWrap,$consumerKey,$consumerSecret;
	
	public static function init(){
		self::$consumerKey=Config::$friendfeed_consumerKey;
		self::$consumerSecret=Config::$friendfeed_consumerSecret;
		self::$oAuthWrap=new COAuthWrap(self::OAUTH_WRAP_ACCESS,self::OAUTH_WRAP_AUTHORIZE,self::$consumerKey,self::$consumerSecret);
	}

	/**
	 * @return Friendfeed
	 */
	public static function create($ff_token=NULL,$ff_secret=NULL){
		return new Friendfeed(self::$consumerKey,self::$consumerSecret,array('oauth_token'=>$ff_token,'oauth_token_secret'=>$ff_secret));
	}













	
	private $consumer,$tokenPair,$access_token;
	
	public function __construct($key,$secret){
		$this->consumer = new OAuthConsumer($key,$secret,null);
		$this->tokenPair=array($key,$secret);
	}
	
}
CFriendFeed::init();

/*
include_once CLIBS.'oauth.php';
//include CLIBS.'friendfeed.php';

class CFriendFeed{
	const FRIENDFEED_OAUTH_BASE = 'https://friendfeed.com/account/oauth';
	const API_URL = 'http://friendfeed-api.com/v2/';
	
	private static $oAuth;
	
	public static function init(){
		self::$oAuth=new COAuth(Config::$friendfeed_consumerKey,Config::$friendfeed_consumerSecret);
	}

	
	private $consumer;
	
	public function __construct($ff_token,$ff_secret){
		$this->consumer = new OAuthConsumer($ff_token,$ff_secret,null);
	}

	
	public function getMyFeed(){
		return self::$oAuth->get($this->consumer,self::API_URL.'feed/me');
	}
}
CFriendFeed::init();*/