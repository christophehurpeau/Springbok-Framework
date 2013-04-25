<?php
/* DEV */define('ENV',include dirname(CORE).'/env.php');/* /DEV */
/* PROD */define('ENV',include APP.'env.php');/* /PROD */
require 'base/base.php';
define('ALIBS',APP.'libs/');
//define('CLIBS',CORE.'libs'.DS);
//define('CLIBS',$parentCore.'libs//* DEV */dev/* /DEV *//* PROD */prod/* /PROD *//');


date_default_timezone_set('Europe/Paris');
ob_start();

//Exceptions
require 'springbok/SDetailedException.php';
require 'base/HttpException.php';

class Springbok{
	const VERSION=/* EVAL time() /EVAL */0;
	public static $scriptname='index',$prefix='',$suffix='';

	public static function findPath($className){
		return self::_findPath($className,CORE,/* DEV */class_exists('Config',false)?/* /DEV */Config::$autoload_default/* DEV */:APP.'models/'/* /DEV */);
	}
	
	public static function _findPath($class_name,$path,$default_path){
		if(ctype_upper($class_name[1])){
			switch($class_name[0]){
				case 'C': return $path.'components/'.$class_name.'.php';
				case 'H': return $path.'helpers/'.$class_name.'.php';
				case 'Q': return $path.'db/queries/'.$class_name.'.php';
				case 'U': return $path.'utils/'.$class_name.'.php';
				case 'D': return $path.'db/'.$class_name.'.php';
				case 'A': return self::_findPath(substr($class_name,1),APP,ALIBS);
				
				case 'B': return $path.'behaviors/'.$class_name.'.php';
				case 'T': return $path.'transformers/'.$class_name.'.php';
				case 'R': return $path.'ressources/'.$class_name.'.php';
				
				case 'V': return APP.'viewsElements/'.substr($class_name,1).'/'.$class_name.'.php';
				case 'E': return APP.'models/'.$class_name[1].DS.substr($class_name,2).'.php'; //Entity...
				case 'M': return APP.'modules/'.$class_name.'.php';
				//case 'S': return APP.'services/'.$class_name.'.php';
				case 'S': return CORE.'springbok/'.$class_name.'.php';
				
				case 'P': $plugin=explode('_',$class_name,3);
					return self::_findPath($plugin[1],CPlugins::path(substr($plugin[0],1)),NULL);
				
				//case 'P': return CLIBS.str_replace('_',DS,$class_name).'.php';
			}
		}else return $default_path.$class_name.'.php';
	}

	public static $inError;

	/** @param Exception $exception */
	public static function handleException($exception){
		$previousError=self::$inError;
		$forceDefault=self::$inError!==null/* DEV */||App::$enhancing/* /DEV */;
		self::$inError=$exception;
		/* DEV */if(isset(App::$enhancing) && App::$enhancing) App::$enhancing->onError(); /* /DEV */
		while(ob_get_length()>0) ob_end_clean();
		$log=get_class($exception).' ['.$exception->getCode().']'.' : '.$exception->getMessage().' ('.$exception->getFile().':'.$exception->getLine().")\n";
		//echo $log; ob_flush();
		if(isset($_SERVER['REQUEST_URI'])){
			$log.=' REQUEST_URI='.$_SERVER['REQUEST_URI'];
			if(/* DEV */!App::$enhancing && /* /DEV */CSecure::isConnected_Safe()) $log.=' Connected='.CSecure::connected();
		}
		if(!empty($_POST)) $log.="\nPOST=".print_r($_POST,true);
		$log.="\nCall Stack:\n".$exception->getTraceAsString();
		
		if($isHttpException=($exception instanceof HttpException)){
			if($exception instanceof FatalHttpException) CLogger::get('fatal-http-exception')->log($log);
			else CLogger::get(date('d').'-'.'http-exception')->log($log);
			return;
		}
		if(class_exists('Config',false) && class_exists('CLogger')) CLogger::get('exception')->log($log);
		/* DEV */elseif(App::$enhancing){debugPrintr($log); exit;}else die($log);/* /DEV */
		
		
		if($previousError!==null){
			$exception=new Exception($exception->getMessage()."\nPrevious error : ".$previousError->getMessage()
										.' ('.$previousError->getFile().':'.$previousError->getLine().')',0,$exception);
		}
		
		
		if(!$isHttpException && !headers_sent()) header('HTTP/1.1 500 Internal Server Error',true,500); // ????
		App::displayException($exception,$forceDefault);
		exit(1);
	}
	
	
	public static function handleError($code,$message,$file,$line,&$context=null,$fromShutdown=false){//debugCode(print_r($context,true));
		if($fromShutdown===false && !($code & (E_STRICT|E_NOTICE)))
			throw new ErrorException($message,$code,0,$file,$line);
		$previousError=self::$inError;
		$forceDefault=self::$inError!==null/* DEV */||App::$enhancing/* /DEV */;
		self::$inError=new ErrorException($message,$code,0,$file,$line);
		/* DEV */if(isset(App::$enhancing) && App::$enhancing) App::$enhancing->onError(); /* /DEV */
		$log=self::getErrorText($code)." : $message ($file:$line)\n";
		//echo $log; ob_flush();
		if(isset($_SERVER['REQUEST_URI'])){
			$log.=' REQUEST_URI='.$_SERVER['REQUEST_URI'];
			/* DEV */if(!App::$enhancing){/* /DEV */
				if(!class_exists('CSession',false)) include CORE.'components/CSession.php';
				if(!class_exists('CSecure',false)) include CORE.'components/CSecure.php';
				/* DEV */
				if(!class_exists('HText',false)) include CORE.'helpers/HText.php';
				if(!class_exists('HDev',false)) include CORE.'helpers/HDev.php';
				/* /DEV */
				if(CSecure::isConnected_Safe()) $log.=' Connected='.CSecure::connected();
			/* DEV */}/* /DEV */
		}
		if(!empty($_POST)) $log.="\nPOST=".print_r($_POST,true);
		$log.="\nCall Stack:\n".prettyBackTrace();
		if($message==='Unsupported operand types') $log.="\nContext:\n".print_r($context,true);
		if(class_exists('Config',false) && class_exists('CLogger')) CLogger::get('error')->log($log);
		/* DEV *//*elseif(App::$enhancing){debug($log); exit;}else die($log);*//* /DEV */
		
		/* PROD */ if($code & (E_STRICT|E_NOTICE)) return true; /* /PROD */
		
		while(ob_get_length()>0) ob_end_clean();
		
		if($previousError!==null){
			$message.="\nPrevious error : ".$previousError->getMessage()
										.' ('.$previousError->getFile().':'.$previousError->getLine().')';
		}
		
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
			self::handleError($error['type'],$error['message'],$error['file'],$error['line'],$NULL,true);
		App::shutdown();
	}
}

function __autoload($className){ /* DEV */
	if($className==='Config'){
		eval('class Config{public static $autoload_default,$cookie_domain=array("'.Springbok::$scriptname.'"=>""),$models_infos,$default_lang="fr",$availableLangs=array("fr"),$siteUrl=array("index"=>""),$db=array("default"=>array("dbname"=>"mysql","user"=>"mysql","password"=>"mysql"));}'
			.'Config::$autoload_default=APP.\'models/\';Config::$models_infos=Config::$autoload_default."infos/";');
		define("STATIC_URL",BASE_URL.'/web/');
		define("WEB_URL",STATIC_URL.WEB_FOLDER);
		debug('Config is loaded');
		return true;
	}/*elseif($className=='CSecure'){
		eval('class CSecure{public static function isConnected_Safe(){return false;}}');
		debug('CSecure is loaded');
		return true;
	}*/
/* /DEV */ include Springbok::findPath($className); }