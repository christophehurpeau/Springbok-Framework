<?php
class CSession{
	private static $_config,$_agent,$_SESSION;
	
	public static function init(){
		session_set_cookie_params(5760,HHtml::url('/'));
		self::start();
		/*if(self::$_config['checkAgent']){
			$agent=md5($_SERVER['HTTP_USER_AGENT'].CSecure::getSalt());
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
	
	public static function getOr($name,$orValue=NULL){
		return self::exists($name) ? self::get($name) : $orValue;
	}
	
	public static function getAndRemoveOr($name,$orValue=NULL){
		return self::exists($name) ? self::getAndRemove($name) : $orValue;
	}
	
	public static function set($name,$value){
		$_SESSION[$name]=$value;
	}
	
	public static function setFlash($message, $element='div', $params=array(), $key='message'){
		self::set('flash.'.$key,compact('message','element','params'));
	}
	
	public static function flash($key='message'){
		if(!self::exists('flash.'.$key)) return;
		$flash=self::getAndRemove('flash.'.$key);
		if(!isset($flash['params']['class'])) $flash['params']['class']='flashMessage';
		return HHtml::tag($flash['element'],$flash['params'],$flash['message']).HHtml::jsInline('$(".flashMessage").delay(4500).fadeOut(600)');
	}
}
CSession::init();