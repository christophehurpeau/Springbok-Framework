<?php
/**
 * CSession
 * 
 * Starts a session, use flash messages.
 * 
 * Default lifetime : 5760s
 * 
 * Use cookie_domain[ entry ] to configure the available domain for entries
 */
class CSession{
	private static $_config,$_agent,$_SESSION;
	
	/** @ignore */
	public static function init(){
		session_set_cookie_params(5760,BASE_URL.'/',Config::$cookie_domain[Springbok::$scriptname]);
		self::start();
		/*if(self::$_config['checkAgent']){
			$agent=md5($_SERVER['HTTP_USER_AGENT'].USecure::getSalt());
		}*/
	}
	
	/**
	 * Start the session
	 * @return void
	 */
	public static function start(){
		/*#if DEV */if(!(Springbok::$inError!==null && headers_sent()))/*#/if*/session_start();
		self::$_SESSION=&$_SESSION;
	}
	
	/**
	 * Destroy the session, don't keep the value
	 * 
	 * @return bool
	 */
	public static function destroy(){
		unset($_SESSION);
		return session_destroy();
	}
	
	/**
	 * Close the session but keep the value
	 * 
	 * @return void
	 */
	public static function close(){
		self::$_SESSION=$_SESSION;
		return session_write_close();
	}
	
	/**
	 * @param string
	 * @return bool
	 */
	public static function exists($name){
		return isset(self::$_SESSION[$name]);
	}
	
	/**
	 * @param string
	 * @return void
	 */
	public static function remove($name){
		unset($_SESSION[$name]);
	}
	
	
	/**
	 * @param string
	 * @return mixed
	 */
	public static function get($name){
		return self::$_SESSION[$name];
	}
	
	/**
	 * Remove a variable and return the previous value
	 * 
	 * @param string
	 * @return mixed
	 */
	public static function getAndRemove($name){
		$res=self::$_SESSION[$name];
		unset($_SESSION[$name]);
		return $res;
	}
	
	/**
	 * Increment a variable
	 * 
	 * @param string
	 * @return int the incremented value
	 */
	public static function increment($name){
		return isset(self::$_SESSION[$name]) ? ++self::$_SESSION[$name] : self::$_SESSION[$name]=1;
	}
	
	/**
	 * @param string
	 * @param mixed
	 * @return mixed
	 * @see get()
	 */
	public static function getOr($name,$orValue=null){
		return self::exists($name) ? self::get($name) : $orValue;
	}
	
	/**
	 * @param string
	 * @param mixed
	 * @return mixed
	 * @see getAndRemove()
	 */
	public static function getAndRemoveOr($name,$orValue=null){
		return self::exists($name) ? self::getAndRemove($name) : $orValue;
	}
	
	/**
	 * @param string
	 * @param mixed
	 * @return void
	 */
	public static function set($name,$value){
		$_SESSION[$name]=$value;
	}
	
	/**
	 * @param string
	 * @param string
	 * @param array
	 * @return void
	 */
	public static function setFlash($message,$key='message',$params=array()){
		self::set('flash.'.$key,compact('message','params'));
	}
	
	/**
	 * @param string
	 * @param string
	 * @param array
	 * @return string HTML flash message, including javascript : fadeOut the message.
	 */
	public static function flash($key='message',$element='div',$params=array()){
		if(!self::exists('flash.'.$key)) return;
		$flash=self::getAndRemove('flash.'.$key);
		if(is_string($flash['params'])) $params['class']=$flash['params'];
		else $params+=$flash['params'];
		if(!isset($params['class'])) $params['class']='flashMessage';
		if(!isset($params['id']) && !isset($params['permanent'])) $params['id']=uniqid('f_');
		return HHtml::tag($element,$params,
				('<a href="#" class="faR smallinfo italic" onclick="$(this).parent().fadeOut(999);$(this).remove();return false;">'.h(_tC('Close')).'</a>')
				.(empty($params['icon'])?'':'<span class="icon '.h($params['icon']).'"></span>')
				.(empty($flash['notEscape'])?h($flash['message']):$flash['message'])
				,false)
			.(isset($params['permanent'])?'':HHtml::jsInline('$("#'.$params['id'].'").delay(9999).fadeOut(999)'));
	}
}
CSession::init();