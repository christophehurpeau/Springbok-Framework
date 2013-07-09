<?php
class SViewElement{
	public static function create(){
		return new static(func_get_args());
	}
	
	protected $vars,$calledClass;
	public function __construct($vars){
		$this->calledClass=get_called_class();
		$this->loadVars($vars);
	}
	public function loadVars($vars){
		$this->vars=call_user_func_array($this->calledClass.'::vars',$vars);
	}
	
	public function render($view='view'){
		include_once CORE.'mvc/views/View.php';
		$vars=$this->vars;
		/*#if DEV*/if(isset($vars['_viewName'])) throw new Exception('_viewName is a restricted variable');/*#/if*/
		$vars['_viewName']=$view;
		return render(APP.'viewsElements/'.substr($this->calledClass,1).'/'.$view.'.php',$vars,true);
	}
}
