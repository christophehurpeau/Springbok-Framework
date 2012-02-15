<?php
$parentCore=dirname(CORE).DS;
define('ENV',include $parentCore.'env.php');
require 'base/base.php';
define('ALIBS',APP.'libs/');
//define('CLIBS',CORE.'libs'.DS);
define('CLIBS',$parentCore.'libs//* DEV */dev/* /DEV *//* PROD */prod/* /PROD *//');

ob_start();

//Exceptions
require 'base/HttpException.php';

class Springbok{
	const VERSION=/* HIDE */0/* /HIDE *//* EVAL time() /EVAL */;
	public static $scriptname='index',$prefix='',$suffix='';

	public static function load($class_name,$path,$default_path){
		if(strtoupper($class_name[1])===$class_name[1]){
			switch($class_name[0]){
	    		case 'C': include $path.'components/'.$class_name.'.php'; break;
	    		case 'H': include $path.'helpers/'.$class_name.'.php'; break;
	    		case 'Q': include $path.'db/queries/'.$class_name.'.php'; break;
	    		case 'U': include $path.'utils/'.$class_name.'.php'; break;
	    		case 'D': include $path.'db/'.$class_name.'.php'; break;
				case 'A': self::load(substr($class_name,1),APP,ALIBS); break;
				
	    		case 'B': include $path.'behaviors/'.$class_name.'.php'; break;
	    		case 'T': include $path.'transformers/'.$class_name.'.php'; break;
				case 'R': include $path.'ressources/'.$class_name.'.php'; break;
				case 'F': include CORE.'springbok/'.$class_name.'.php'; break; // like Fundamental or First...
				
				case 'V': include APP.'viewsElements/'.substr($class_name,1).'/class.php'; break;
	    		case 'E': include APP.'models/'.$class_name[1].DS.substr($class_name,2).'.php'; break; //Entity...
				case 'M': include APP.'modules/'.$class_name.'.php'; break;
				case 'S': include APP.'services/'.$class_name.'.php'; break;
				
				case 'P': $plugin=explode('_',$class_name,3);
					self::load($plugin[1],CPlugins::path(substr($plugin[0],1)),NULL);
				
				//case 'P': include CLIBS.str_replace('_',DS,$class_name).'.php';
	    	}
		}else include $default_path.$class_name.'.php';
	}

	private static $inError;

	/** @param Exception $exception */
	public static function handleException($exception){
		$forceDefault=self::$inError===true;
		self::$inError=true;
		/* DEV */if(isset(App::$enhancing) && App::$enhancing) App::$enhancing->onError(); /* /DEV */
		if(ob_get_length()>0) ob_end_clean();
		$log=get_class($exception).' ['.$exception->getCode().']'.' : '.$exception->getMessage().' ('.$exception->getFile().':'.$exception->getLine().")\n";
		if(isset($_SERVER['REQUEST_URI'])) $log.=' REQUEST_URI='.$_SERVER['REQUEST_URI'];
		if(CSecure::isConnected_Safe()) $log.=' Connected='.CSecure::connected();
		$log.="\nCall Stack:\n".$exception->getTraceAsString();
		
		if($exception instanceof HttpException){
			if($exception instanceof FatalHttpException) CLogger::get('fatal-http-exception')->log($log);
			else CLogger::get('http-exception')->log($log);
			return;
		}
		if(class_exists('Config',false) && class_exists('CLogger')) CLogger::get('exception')->log($log);
		/* DEV */elseif(App::$enhancing){debug($log); exit;}else die($log);/* /DEV */
		App::displayException($exception,$forceDefault);
		exit(1);
	}
	
	
	public static function handleError($code,$message,$file,$line,$context=null){//debugCode(print_r($context,true));
		$forceDefault=self::$inError===true;
		self::$inError=true;
		/* DEV */if(isset(App::$enhancing) && App::$enhancing) App::$enhancing->onError(); /* /DEV */
		$log=self::getErrorText($code)." : $message ($file:$line)\n";
		if(isset($_SERVER['REQUEST_URI'])) $log.=' REQUEST_URI='.$_SERVER['REQUEST_URI'];
		if(CSecure::isConnected_Safe()) $log.=' Connected='.CSecure::connected();
		$log.="\nCall Stack:\n".prettyBackTrace();
		if($message==='Unsupported operand types') $log.="\nContext:\n".print_r($context,true);
		if(class_exists('Config',false) && class_exists('CLogger')) CLogger::get('error')->log($log);
		/* DEV */elseif(App::$enhancing){debug($log); exit;}else die($log);/* /DEV */
		
		/* PROD */ if(! in_array($code,array(E_ERROR,E_CORE_ERROR,E_USER_ERROR,E_WARNING,E_CORE_WARNING,E_COMPILE_WARNING,E_USER_WARNING,E_RECOVERABLE_ERROR))) return true; /* /PROD */
		
		if(ob_get_length()>0) ob_end_clean();
		
		/* DEV */ $stack=xdebug_get_function_stack(); /* /DEV */
		App::displayError($forceDefault,$code, $message, $file, $line,$context/* DEV */,$stack/* /DEV */);
		exit(1);
	}


	public static function getErrorText($code){
		switch($code){
			case E_ERROR: return 'ERROR';
			case E_WARNING: return 'WARNING';
			case E_PARSE: return 'PARSE';
			case E_NOTICE: return 'NOTICE';
			case E_CORE_ERROR: return 'CORE_ERROR';
			case E_CORE_WARNING: return 'CORE_WARNING';
			case E_COMPILE_ERROR: return 'COMPILE_ERROR';
			case E_COMPILE_WARNING: return 'COMPILE_WARNING';
			case E_USER_ERROR: return 'USER_ERROR';
			case E_USER_WARNING: return 'USER_WARNING';
			case E_NOTICE: return 'USER_NOTICE';
			case E_STRICT: return 'STRICT';
			case E_RECOVERABLE_ERROR: return 'RECOVERABLE_ERROR';
			case E_DEPRECATED: return 'DEPRECATED';
			case E_USER_DEPRECATED: return 'USER_DEPRECATED';
		}
		return 'Unknown';
	}
	
	public static function shutdown(){
		if(($error=error_get_last()) && in_array($error['type'],array(E_ERROR,E_PARSE,E_CORE_ERROR,E_CORE_WARNING,E_COMPILE_ERROR,E_COMPILE_WARNING)))
			self::handleError($error['type'],$error['message'],$error['file'],$error['line']);
		App::shutdown();
	}
}

function __autoload($className){ /* DEV */
	if($className==='Config') eval('class Config{}');
/* /DEV */ Springbok::load($className,CORE,/* DEV */class_exists('Config',false)?/* /DEV */Config::$autoload_default/* DEV */:APP.'models/'/* /DEV */); }