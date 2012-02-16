<?php
class CModule{
	private static $_MODULES;
	public static function init(){
		self::$_MODULES=App::configArray('modules');
	}
	
	public static function __callStatic($methodName, $params){
		if(!empty(self::$_MODULES[$methodName]))
			foreach(self::$_MODULES[$methodName] as $className)
				call_user_func_array(array($className,$methodName),$params);
	}
}
CModule::init();