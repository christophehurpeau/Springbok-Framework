<?php
/**
 * A module is like hooks
 * 
 * Call CModule::{$hookName} and all methods $hookName of available modules will be called
 */
class CModule{
	private static $_MODULE;
	
	/** @ignore*/
	public static function init(){
		self::$_MODULES=file_exists(APP.'config/modules.php') ? include APP.'config/modules.php' : array();
	}
	
	public static function __callStatic($methodName, $params){
		if(!empty(self::$_MODULES[$methodName]))
			foreach(self::$_MODULES[$methodName] as $className)
				call_user_func_array(array($className,$methodName),$params);
	}
}
CModule::init();