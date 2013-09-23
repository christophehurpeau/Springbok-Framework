<?php
/** @ignore */
class CPlugins{
	private static $_PLUGINS_PATH,$_PLUGINS;
	
	public static function init(){
		self::$_PLUGINS_PATH=&Config::$pluginsPaths;
		self::$_PLUGINS=&Config::$plugins;
	}
	
	public static function path($pluginName){
		return self::$_PLUGINS_PATH[self::$_PLUGINS[$pluginName][0]].self::$_PLUGINS[$pluginName][1].DS;
	}
	
	public static function configArray($pluginName,$name,$withSuffix=false){
		return include self::path($pluginName).'config/'.$name.($withSuffix ? '_'.ENV : '').'.php';
	}
	
}
CPlugins::init();