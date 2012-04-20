<?php
class SViewElement{
	public static function generate(){
		$vars=static::vars(func_get_args());
		file_put_contents(static::path($vars),render(APP.'viewsElements/'.substr(get_called_class(),1).'/view.php',$vars,true));
	}
	
	public static function render(){
		$vars=static::vars(func_get_args());
		render(APP.'viewsElements/'.substr(get_called_class(),1).'/view.php',$vars);
	}
	
	public static function vars(&$vars){ return $vars; }
	
	public static function destroy(){
		$path=static::path(func_get_args());
		if(file_exists($path)) unlink($path);
	}
}
