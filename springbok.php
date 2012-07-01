<?php
$parentCore=dirname(CORE).DS;
define('ENV',include $parentCore.'env.php');
require 'base/base.php';
define('ALIBS',APP.'libs/');
//define('CLIBS',CORE.'libs'.DS);
define('CLIBS',$parentCore.'libs//* DEV */dev/* /DEV *//* PROD */prod/* /PROD *//');

date_default_timezone_set('Europe/Paris');
ob_start();

//Exceptions
require 'base/HttpException.php';

class Springbok{
	const VERSION=/* EVAL time() /EVAL */0;
	public static $scriptname='index',$prefix='',$suffix='';

	public static function load($class_name,$path,$default_path){
		if(ctype_upper($class_name[1])){
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
				
				case 'V': include APP.'viewsElements/'.substr($class_name,1).'/'.$class_name.'.php'; break;
	    		case 'E': include APP.'models/'.$class_name[1].DS.substr($class_name,2).'.php'; break; //Entity...
				case 'M': include APP.'modules/'.$class_name.'.php'; break;
				//case 'S': include APP.'services/'.$class_name.'.php'; break;
				case 'S': include CORE.'springbok/'.$class_name.'.php'; break;
				
				case 'P': $plugin=explode('_',$class_name,3);
					self::load($plugin[1],CPlugins::path(substr($plugin[0],1)),NULL);
				
				//case 'P': include CLIBS.str_replace('_',DS,$class_name).'.php';
	    	}
		}else include $default_path.$class_name.'.php';
	}

	public static $inError;

	/** @param Exception $exception */
	public static function handleException(&$exception){
		$forceDefault=self::$inError===true/* DEV */||App::$enhancing/* /DEV */;
		self::$inError=true;
		/* DEV */if(isset(App::$enhancing) && App::$enhancing) App::$enhancing->onError(); /* /DEV */
		if(ob_get_length()>0) ob_end_clean();
		$log=get_class($exception).' ['.$exception->getCode().']'.' : '.$exception->getMessage().' ('.$exception->getFile().':'.$exception->getLine().")\n";
		//echo $log; ob_flush();
		if(isset($_SERVER['REQUEST_URI'])){
			$log.=' REQUEST_URI='.$_SERVER['REQUEST_URI'];
			if(/* DEV */!App::$enhancing && /* /DEV */CSecure::isConnected_Safe()) $log.=' Connected='.CSecure::connected();
		}
		$log.="\nCall Stack:\n".$exception->getTraceAsString();
		
		if($exception instanceof HttpException){
			if($exception instanceof FatalHttpException) CLogger::get('fatal-http-exception')->log($log);
			else CLogger::get('http-exception')->log($log);
			return;
		}
		if(class_exists('Config',false) && class_exists('CLogger')) CLogger::get('exception')->log($log);
		/* DEV */elseif(App::$enhancing){debug($log); exit;}else die($log);/* /DEV */
		
		if(!headers_sent()) header('HTTP/1.1 500 Internal Server Error',true,500);
		App::displayException($exception,$forceDefault);
		exit(1);
	}
	
	
	public static function handleError($code,$message,$file,$line,&$context=null){//debugCode(print_r($context,true));
		$forceDefault=self::$inError===true/* DEV */||App::$enhancing/* /DEV */;
		self::$inError=true;
		/* DEV */if(isset(App::$enhancing) && App::$enhancing) App::$enhancing->onError(); /* /DEV */
		$log=self::getErrorText($code)." : $message ($file:$line)\n";
		//echo $log; ob_flush();
		if(isset($_SERVER['REQUEST_URI'])){
			$log.=' REQUEST_URI='.$_SERVER['REQUEST_URI'];
			if(/* DEV */!App::$enhancing && /* /DEV */CSecure::isConnected_Safe()) $log.=' Connected='.CSecure::connected();
		}
		$log.="\nCall Stack:\n".prettyBackTrace();
		if($message==='Unsupported operand types') $log.="\nContext:\n".print_r($context,true);
		if(class_exists('Config',false) && class_exists('CLogger')) CLogger::get('error')->log($log);
		/* DEV *//*elseif(App::$enhancing){debug($log); exit;}else die($log);*//* /DEV */
		
		/* PROD */ if(! in_array($code,array(E_ERROR,E_CORE_ERROR,E_USER_ERROR,E_WARNING,E_CORE_WARNING,E_COMPILE_WARNING,E_USER_WARNING,E_RECOVERABLE_ERROR))) return true; /* /PROD */
		
		if(ob_get_length()>0) ob_end_clean();
		
		if(!headers_sent()) header('HTTP/1.1 500 Internal Server Error',true,500);
		App::displayError($forceDefault,$code, $message, $file, $line,$context);
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
	if($className==='Config'){
		eval('class Config{public static $autoload_default,$cookie_domain="",$models_infos,$db=array("default"=>array("dbname"=>"mysql","user"=>"mysql","password"=>"mysql"));}'
			.'Config::$autoload_default=APP.\'models/\';Config::$models_infos=Config::$autoload_default."infos/";');
		define("STATIC_URL",BASE_URL.'/web/'.WEB_FOLDER);
		debug('Config is loaded');
		return true;
	}/*elseif($className=='CSecure'){
		eval('class CSecure{public static function isConnected_Safe(){return false;}}');
		debug('CSecure is loaded');
		return true;
	}*/
/* /DEV */ Springbok::load($className,CORE,/* DEV */class_exists('Config',false)?/* /DEV */Config::$autoload_default/* DEV */:APP.'models/'/* /DEV */); }