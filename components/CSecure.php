<?php
/**
 * Secure Component : handle authentification
 * 
 * @see https://github.com/christophehurpeau/Springbok-Framework-Plugins/tree/master/users
 */
class CSecure{
	const BACK_URL='SECURE_BACK_URL',
		CONNECTION_FORM=0,CONNECTION_BASIC=1,CONNECTION_COOKIE=2,CONNECTION_AFTER_REGISTRATION=3;
	private static $_config,$_user=NULL;
	protected static $_cookie;
	
	/** @ignore */
	public static function init(){
		/*#if DEV */if(Springbok::$inError===null)/*#/if*/
		self::$_config=self::loadConfig();
	}
	
	protected static function loadConfig($configName='secure'){
		$config=App::configArray($configName)
			+array('className'=>'User','login'=>'login','password'=>'pwd','auth'=>'','authConditions'=>array(),'blacklist_back_url'=>array(),
				'trim'=>". \t\n\r\0\x0B",'logConnections'=>false,'userHistory'=>false);
		if(!isset($config['cookiename'])) $config['cookiename']=$config['className'];
		if(!isset($config['id'])) $config['id']=$config['login'];
		return $config;
	}
	
	protected static function loadUser(){
		//if(static::config('loadUser') && ($user=self::connected()) && !CHttpRequest::isAjax()){
		if(($user=static::connected()) !== null){
			$className=static::config('className');
			$query=$className::QOne()->where(array(static::config('id')=>$user));
			if(static::issetConfig('fields')) $query->setFields(static::config('fields'));
			if(static::issetConfig('with')) $query->setAllWith(static::config('with'));
			$res=$query->execute();
			if($res===false) self::logout();
			return $res;
		}
		return false;
	}
	
	protected static function loadCookie(){
		if(($cookiename=static::config('cookiename')) && (!isset(self::$_cookie) || self::$_cookie->getName()!==$cookiename))
			self::$_cookie=CCookie::get(static::config('cookiename'));
	}
	
	/**
	 * @param string
	 * @return bool
	 */
	protected static function issetConfig($name){
		return isset(self::$_config[$name]);
	}
	
	/**
	 * @param string
	 * @return mixed
	 */
	public static function config($name){
		return self::$_config[$name];
	}
	
	/**
	 * Returns if the current user is connected
	 * 
	 * @return bool
	 */
	public static function isConnected(){
		return CSession::exists('user_'.static::config('id'));
	}
	
	/**
	 * Returns if the current user is connected, safely
	 * 
	 * Try to not send another error : check if CSession is already loaded or if headers are not sent.
	 * Use this on error pages
	 * 
	 * @return bool
	 */
	public static function isConnected_Safe(){
		if(class_exists('CSession',false) || !headers_sent()) return self::isConnected();
		else return false;
	}
	
	/**
	 * Return the current connected user
	 * 
	 * @param mixed
	 * @return mixed
	 */
	public static function connected($orValue=false){
		return CSession::getOr('user_'.static::config('id'),$orValue);
	}
	
	/**
	 * Return the current user model instance
	 * 
	 * @return SModel
	 */
	public static function user(){
		if(self::$_user===null) return self::$_user=self::loadUser();
		return self::$_user;
	}
	
	/**
	 * Return the current user model, if already loaded
	 * 
	 * Use this on error pages
	 * 
	 * @return SModel
	 */
	public static function userSafe(){
		return self::$_user;
	}
	
	/**
	 * Return if the current user is an Admin
	 * 
	 * @return bool
	 */
	public static function isAdmin(){
		return static::user()->isAdmin();
	}
	
	/**
	 * Check access
	 * 
	 * Redirect to login page if the user is not connected
	 * if param is set, check if the user is allowed with the method $user->isAllowed($params[0])
	 * 
	 * @return bool
	 */
	public static function checkAccess($params=null){
		if(!static::connect(false)){
			if(($auth=static::config('auth'))===''){
				CSession::set(self::BACK_URL,CHttpRequest::isGET() ? CRoute::getAll() : '/');
				self::redirectToLogin();
			}else call_user_func(array('self','authenticate_'.$auth));
			// foreach(explode(',',$check) as $profile) static::check($profile)
		}
		if($params!==null){
			$user=static::user();
			if(!$user->isAllowed($params[0])){
				if(empty($params[1])) forbidden();
				Controller::redirect($params[1]);
			}
		}
	}
	
	/**
	 * Redirect to the login page
	 * 
	 * @return void
	 */
	public static function redirectToLogin(){
		Controller::redirect(array(true,static::config('url_login'),'?'=>'back='.urlencode(CHttpRequest::getCurrentUrl())));
	}
	
	/**
	 * Try to connect the user using cookie
	 * 
	 * @param bool redirect if not connected
	 * @return bool
	 */
	public static function connect($redirect=true){
		if($redirect){
			/*if(!CSession::exists(self::BACK_URL)) */self::setBackUrl();
			static::redirectIfConnected();
		}elseif(static::isConnected()) return true;
		// look cookie
		self::loadCookie();
		if(!empty(self::$_cookie->user) && !empty($_SERVER['HTTP_USER_AGENT'])){
			$className=static::config('className'); $login=static::config('login'); $id=static::config('id');
			
			if(sha1($_SERVER['HTTP_USER_AGENT'].USecure::getSalt())===self::$_cookie->agent){
				$where=static::config('authConditions');
				$where[$login]=self::$_cookie->user;
			
				$query=$id===$login ? /**/$className::QExist() : /**/$className::QValue()->field($id);
				if($res=$query->where($where)->execute()){
					self::setConnected(self::CONNECTION_COOKIE,($id===$login ? self::$_cookie->user : $res),self::$_cookie->user);
					if(static::checkCookie(self::$_cookie)){
						self::$_cookie->write();
						if($redirect) static::redirectAfterConnection();
						return true;
					}else CSession::destroy();
				}
			}
			if(static::config('logConnections')) self::logConnection(self::CONNECTION_COOKIE,false,self::$_cookie->user);
			self::$_cookie->destroy();
		}
		return false;
	}
	
	/**
	 * @param string
	 * @return void
	 */
	public static function setBackUrl($url=null){
		if($url===null) $url=CHttpRequest::referer(true);
		/*#if DEV */if(startsWith($url,'/'.Springbok::$scriptname.'/')) $url=substr($url,strlen(Springbok::$scriptname)+1);/*#/if*/
		if($url===null) return;
		if($url===HHtml::url(static::config('url_login'))) return;
		foreach(static::config('blacklist_back_url') as $blacklistedUrl)
			if($url===HHtml::url($blacklistedUrl)) return;
		CSession::set(self::BACK_URL,$url);
	}
	
	/**
	 * @return void
	 */
	public static function redirectIfConnected(){
		if(static::isConnected()) static::redirectAfterConnection();
	}
	
	/**
	 * @return void
	 */
	public static function redirectAfterConnection($exit=true){
		Controller::redirect(CSession::getAndRemoveOr(self::BACK_URL,static::config('url_redirect')),null,$exit);
	}
	
	/**
	 * @param int
	 * @param mixed
	 * @param mixed
	 * @return void
	 */
	public static function setConnected($type,$connected,$login){
		CSession::set('user_'.static::config('id'),$connected);
		if(static::config('logConnections'))
			self::logConnection($type,true,$login,$connected);
		static::onAuthenticated($type);
	}
	
	/**
	 * @param SModel
	 * @return void
	 */
	public static function createCookie($user){
		if(empty($_SERVER['HTTP_USER_AGENT'])) return false;
		$login=static::config('login');
		self::loadCookie();
		self::$_cookie->user=$user->$login;
		self::$_cookie->agent=sha1($_SERVER['HTTP_USER_AGENT'].USecure::getSalt());
		self::$_cookie->write();
	}
	
	/**
	 * @return bool
	 */
	protected static function checkCookie(){
		return true;
	}
	
	/**
	 * Connect a user from a form
	 * 
	 * @param SModel
	 * @param bool
	 * @param bool
	 */
	public static function authenticate($user,$remember=false,$redirect=true){
		//$by='by'.ucfirst($_config['login']).'And'.ucfirst($_config['password']);
		$className=static::config('className'); $login=static::config('login'); $password=static::config('password'); $id=static::config('id'); $logConnections=static::config('logConnections');
		$connected=$type=false;
		if($className){
			$where=static::config('authConditions');
			$where[$login]=$user->$login;
			$pwd=trim($user->$password,static::config('trim'));
			$where[$password]=USecure::hashWithSalt($pwd);
			
			$query=$id===$login ? /**/$className::QExist() : /**/$className::QValue()->field($id);
			
			if($res=$query->where($where)->execute()){
				$connected=$id===$login ? $user->$login : $res;
				if($remember) static::createCookie($user);
			}
			$type=self::CONNECTION_FORM;
		}elseif(($users=static::config('users'))){
			if(isset($users[$user['login']]) && $users[$user['login']]===$user['pwd']) $connected=$user['login'];
			if($logConnections) $type=self::CONNECTION_BASIC;
		}elseif($logConnections) $type=self::CONNECTION_BASIC;
		if($connected){
			CSession::set('user_'.$id,$connected);
			if($logConnections) self::logConnection($type,true,$user->$login,$connected);
			static::onAuthenticated($type);
			if($redirect) static::redirectAfterConnection(false);
			return true;
		}
		if($logConnections) self::logConnection($type,false,$user->$login);
		
		return false;
	}

	/**
	 * @return void
	 */
	protected static function authFailed(){
		CSession::setFlash(_tC('Sorry, your login or your password is incorrect...'));
		sleep(3);
	}

	/**
		Basic HTTP authentication
			@return boolean
			@param $auth mixed
			@param $realm string
			@public
	**/
	public static function authenticate_basic($realm=NULL) {
		if(isset($_SERVER['PHP_AUTH_USER']))
			if(self::authenticate(array('login'=>$_SERVER['PHP_AUTH_USER'],'pwd'=>$_SERVER['PHP_AUTH_PW']),static::config('remember'))) return true;
		if($realm === NULL) $realm=Config::$projectName.' Auth';//$_SERVER['REQUEST_URI'];
		header('WWW-Authenticate: Basic realm="'.utf8_decode($realm).'"',true,401);
		exit;
	}
	
	/**
	 * Logout user
	 * 
	 * @return void
	 */
	public static function logout(){
		self::loadCookie();
		if(isset(self::$_cookie)) self::$_cookie->destroy();
		else CCookie::delete(static::config('cookiename'));
		CSession::destroy();
		static::onDisconnected();
	}
	
	public static function onDisconnected(){}
	public static function onAuthenticated($type){}


	/**
	 * @param int
	 * @param bool
	 * @param mixed
	 * @param mixed
	 * @return void
	 */
	private static function logConnection($type,$succeed,$login,$connected=null){
		switch(static::config('logConnections')){
			case 'sql':
				$c=new UserConnection;
				$c->type=$type;
				$c->succeed=$succeed;
				$c->login=$login;
				if($connected!==null) $c->connected=$connected;
				$c->ip=CHttpRequest::getRealClientIP();
				$c->insert();
				if(static::config('userHistory') && $succeed) UserHistory::add(UserHistory::CONNECT,$c->id,$connected);
				break;
			case 'file':
				CLogger::get('connections')->log($type.': '.($succeed?'SUCCEED':'FAILED').' - '.$login.($connected!==NULL?(' => '.$connected):''));
				break;
		}
	}

	/**
	 * Hash a password
	 * 
	 * @param string
	 * @return void
	 */
	public static function hashPassword($pwd){
		return USecure::hashWithSalt(trim($pwd,static::config('trim')));
	}
}
CSecure::init();
