<?php
class FViewElement{
	public static function generate(){
		$vars=static::vars(func_get_args());
		file_put_contents(static::path($vars),render(APP.'viewsElements/'.substr(get_called_class(),1).'/view.php',$vars,true));
	}
	
	public static function render(){
		$vars=static::vars(func_get_args());
		render(APP.'viewsElements/'.substr(get_called_class(),1).'/view.php',$vars);
	}
}
