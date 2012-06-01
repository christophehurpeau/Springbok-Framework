<?php
class SViewElement{
	public static function create(){
		return new static(func_get_args());
	}
	
	protected $vars,$calledClass;
	public function __construct($vars){
		$this->calledClass=get_called_class();
		$this->vars=call_user_func_array($this->calledClass.'::vars',$vars);
	}
	
	public function render($view='view'){
		return render(APP.'viewsElements/'.substr($this->calledClass,1).'/'.$view.'.php',$this->vars,true);
	}
}
