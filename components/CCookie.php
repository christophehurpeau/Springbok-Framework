<?php
class CCookie{
	private static $_config;
	
	public static function init(){
		self::$_config=App::configArray('cookies');
	}
	
	public static function exists($name){
		return isset($_COOKIE[$name]);
	}
	public static function get($name){
		return new CCookie($name,self::_getConfig($name));
	}
	
	public static function getLang(){
		if(!isset(self::$_config['lang'])) return null;
		else return $_COOKIE['lang'];
	}
	public static function setLang($lang){
		if(!isset(self::$_config['lang'])) return null;
		setcookie('lang',$lang,time()+36000,'/','',false,true);
	}
	
	public static function delete($name){
		$config=self::_getConfig($name);
		setcookie($name,'',time()-42000,$config['path'],$config['domain'],$config['https'],$config['httponly']);
	}
	
	private static function _getConfig($name){
		$config=(isset(self::$_config[$name])?self::$_config[$name]:array())
			+array('name'=>'Sb'.$name,'expires'=>'2 weeks','path'=>null,'domain'=>Config::$cookie_domain[Springbok::$scriptname],'https'=>IS_HTTPS,'httponly'=>true,'key'=>NULL,);

		if($config['expires'] === 0);
		elseif($config['expires'] === '0') $config['expires']=0;
		elseif(is_numeric($config['expires'])) $config['expires']=time()+((int)$config['expires']);
		else $config['expires']=strtotime($config['expires']);
		if($config['path']===null) $config['path']=HHtml::url($config['path']);

		return $config;
	}
	
	
	/* */
	
	private $name,$config,$data;
	
	public function __construct($name,$config){
		$this->name=$name; $this->config=$config;
		if(isset($_COOKIE[$name])){
			$decrytpeddata=USecure::decryptAES($_COOKIE[$name],$config['key']);
			//if($name!=='springbok') debugVar([$name,substr($decrytpeddata,0,40),sha1(substr($decrytpeddata,0,40)),substr($decrytpeddata,40),$_COOKIE[$name]]);
			if(empty($decrytpeddata)) $this->data=array();
			else{
				$jsondata=substr($decrytpeddata,40);
				if(substr($decrytpeddata,0,40)!==sha1($jsondata)){
					/*#if DEV */ if($name!=='springbok') throw new Exception('CCookie : decrypted data does not match sha1 (name='.$name.')'); /*#/if*/
					$this->data=array();
				}else{
					$this->data=json_decode($jsondata,true);
					if(empty($this->data)) $this->data=array();
				}
			}
		}
	}
	
	public function getName(){
		return $this->name;
	}
	
	
	public function write(){
		$jsondata=json_encode($this->data);
		$_COOKIE[$this->name]=USecure::encryptAES(sha1($jsondata).$jsondata,$this->config['key']);
		return setcookie($this->name,$_COOKIE[$this->name],$this->config['expires'],$this->config['path'],$this->config['domain'],
			$this->config['https'],$this->config['httponly']);
	}
	
	public function destroy(){
		unset($_COOKIE[$this->name]);
		setcookie($this->name,'',time()-42000,$this->config['path'],$this->config['domain'],$this->config['https'],$this->config['httponly']);
	}
	
	public function __isset($name){
		return isset($this->data[$name]);
	}
	public function __get($name){
		return $this->data[$name];
	}
	public function __set($name,$value){
		$this->data[$name]=&$value;
	}
	public function __unset($name){
		unset($this->data[$name]);
	}
	
	public function _setData(&$data){
		$this->data=&$data;
	}
	
	public function _getData(){
		return $this->data;
	}
	
	public function __toString(){
		return UPhp::exportCode($this->data);
	}
}
CCookie::init();