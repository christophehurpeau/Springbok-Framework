<?php
/**
 * REST Authentification
 * 
 * For browser API or REST API : user the HTTP_SAUTH to provide a valid token
 * 
 */
class CSecureRest{
	private static $_config,$connected;
	
	/** @ignore */
	public static function init(){
		self::$_config=self::loadConfig();
	}
	
	protected static function loadConfig($configName='secure'){
		$config=App::configArray($configName)
			+array('className'=>'User','login'=>'login','password'=>'pwd','auth'=>'','authConditions'=>array());
		if(!isset($config['id'])) $config['id']=$config['login'];
		return $config;
	}
	protected static function issetConfig($name){ return isset(self::$_config[$name]); }
	public static function config($name){ return self::$_config[$name]; }
	
	
	public static function isConnected(){
		return self::$connected!==false && self::$connected!==null;
	}
	public static function connected($orValue=false){
		return self::$connected===null ? $orValue : self::$connected;
	}
	
	public static function checkAuth(){
		if(self::$connected===null){
			if(isset($_SERVER['HTTP_SAUTH'])){
				self::$connected=UserToken::QValue()->field('user_id')
					->where(array('token'=>$_SERVER['HTTP_SAUTH'],'userAgent'=>sha1($_SERVER['HTTP_USER_AGENT'].USecure::getSalt())));
			}
		}
		return self::$connected!==false && self::$connected!==null;
	}
	
	public static function checkAccess($params=null){
		if(self::checkAuth()===false) forbidden();
		if($params!==null){
			$user=static::user();
			if(!$user->isAllowed($params[0])) forbidden();
		}
	}
	
	public static function connect($user){
		$className=static::config('className'); $login=static::config('login'); $password=static::config('password'); $id=static::config('id');
		$where=static::config('authConditions');
		$where[$login]=$user->$login;
		$where[$password]=static::hashPassword($user->$password);
		
		$query=$id===$login ? $className::QExist() : $className::QValue()->field($id);
		
		if($res=$query->where($where)->execute()){
			self::$connected=$id===$login ? $user->$login : $res;
			$ut=new UserToken;
			$ut->user_id=self::$connected;
			$ut->token=UGenerator::randomCode(22,/* EVAL str_split('azertyuiopqsdfghjklmwxcvbnAZERTYUIOPQSDFGHJKLMWXCVBN1234567890_-+') /EVAL */'');
			$ut->userAgent=sha1($_SERVER['HTTP_USER_AGENT'].USecure::getSalt());
			$ut->insert();
			return $ut->token;
		}
		return false;
	}


	public static function hashPassword($pwd){
		return USecure::hashWithSalt($pwd);
	}
}
CSecureRest::init();