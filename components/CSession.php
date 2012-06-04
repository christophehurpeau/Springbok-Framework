<?php
class CSession{
	private static $_config,$_agent,$_SESSION;
	
	public static function init(){
		session_set_cookie_params(5760,BASE_URL.'/',Config::$cookie_domain);
		self::start();
		/*if(self::$_config['checkAgent']){
			$agent=md5($_SERVER['HTTP_USER_AGENT'].USecure::getSalt());
		}*/
	}
	public static function start(){
		session_start();
		self::$_SESSION=&$_SESSION;
	}
	
	public static function destroy(){
		unset($_SESSION);
		return session_destroy();
	}
	
	public static function close(){
		self::$_SESSION=$_SESSION;
		return session_write_close();
	}
	
	public static function exists($name){
		return isset(self::$_SESSION[$name]);
	}
	
	public static function remove($name){
		unset($_SESSION[$name]);
	}
	
	public static function &get($name){
		return self::$_SESSION[$name];
	}
	
	public static function &getAndRemove($name){
		$res=&self::$_SESSION[$name];
		unset($_SESSION[$name]);
		return $res;
	}
	
	public static function increment($name){
		return isset(self::$_SESSION[$name]) ? ++self::$_SESSION[$name] : self::$_SESSION[$name]=1;
	}
	
	public static function getOr($name,$orValue=null){
		return self::exists($name) ? self::get($name) : $orValue;
	}
	
	public static function getAndRemoveOr($name,$orValue=null){
		return self::exists($name) ? self::getAndRemove($name) : $orValue;
	}
	
	public static function set($name,$value){
		$_SESSION[$name]=$value;
	}
	
	public static function setFlash($message,$key='message',$params=array()){
		self::set('flash.'.$key,compact('message','params'));
	}
	
	public static function flash($key='message',$element='div',$params=array()){
		if(!self::exists('flash.'.$key)) return;
		$flash=self::getAndRemove('flash.'.$key);
		$params+=$flash['params'];
		if(!isset($params['class'])) $params['class']='flashMessage';
		if(!isset($params['id'])) $params['id']=uniqid('f_');
		return HHtml::tag($element,$params,(empty($params['icon'])?'<span class="icon '.h($params['icon']).'"></span>':'')
				.(empty($flash['notEscape'])?h($flash['message']):$flash['message']),false)
			.HHtml::jsInline('$("#'.$params['id'].'").delay(5500).fadeOut(800)');
	}
}
CSession::init();