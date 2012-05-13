<?php
include_once __DIR__.DS.'AFolderEnhancer.php';

class DefaultFolderEnhancer extends AFolderEnhancer{
	protected static $fileEnhancers;
	public static function registerFileEnhancers(){
		self::registerEnhancer('CssFile',array('css','sbcss'),true,'css');
		self::registerEnhancer('ScssFile','scss',true,'css');
		self::registerEnhancer('ImgFile',array('jpg','jpeg','png','gif'),true);
		self::registerEnhancer('JsFile','js',true);
		self::registerEnhancer('JsAppFile','jsapp',true);
	}
}
DefaultFolderEnhancer::registerFileEnhancers();
