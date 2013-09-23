<?php
/** @ignore */
class CPayments{
	private static $instances,$paymentsConfig;
	public static function init(){
		self::$paymentsConfig=&App::configArray('payments');
	}
	
	public static function exist($name){return isset(self::$paymentsConfig[$name]);}
	public static function get($name){
		if(isset(self::$instances[$name])) return self::$instances[$name];
		return self::$instances[$name]=CorePayment::get($name,self::$paymentsConfig[$name]);
		//return $instance;
	}
}
CPayments::init();

/** @ignore */
abstract class Payment{
	protected $name;
	
	protected function beforeRender(){}

	protected function _render($file){
		$this->beforeRender();
		/*#if DEV */
		if(!file_exists($file)) throw new Exception(_tC('This view does not exist:').' '.replaceAppAndCoreInFile($file));
		/*#/if*/
		return render($file,self::$viewVars,true);
	}
}

/** @ignore */
abstract class CorePayment extends Payment{
	protected function render($fileName){
		return $this->_render(CORE.'payments/'.$this->name.DS.$fileName.'.php');
	}
	public static function get($name,$config){
		include CORE.'payments/'.$name.DS.$name.'.php';
		$instance=new $name;
		$instance->name=$name;
		foreach($config as $key=>&$value) $instance->$key=$value;
		return $instance;
	}
}

/** @ignore */
abstract class AppPayment extends Payment{
	protected function render($fileName){
		return $this->_render(APP.'payments/'.$this->name.DS.$fileName.'.php');
	}
	public static function get($name,$config){
		include APP.'payments/'.$name.DS.$name.'.php';
		$instance=new $name;
		$instance->name=$name;
		foreach($config as $key=>&$value) $instance->$key=$value;
		return $instance;
	}
}
