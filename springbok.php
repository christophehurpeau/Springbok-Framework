<?php
/**
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @link          http://springbok-framework.com
 * @package       Springbok
 * @since         Springbok v 1
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Christophe Hurpeau <christophe@hurpeau.com>
 */

/*#if DEV */define('ENV',include dirname(CORE).'/env.php');/*#/if*/
/*#if PROD*/define('ENV',include APP.'env.php');/*#/if*/
require 'base/base.php';
define('ALIBS',APP.'libs/');
//define('CLIBS',CORE.'libs'.DS);
//define('CLIBS',$parentCore.'libs//*#if DEV */dev/*#/if*//*#if PROD*/prod/*#/if*//');


date_default_timezone_set('Europe/Paris');

//Exceptions
require 'springbok/SDetailedException.php';
require 'base/HttpException.php';

class Springbok{
	const VERSION=/*#eval time() */0;
	public static $scriptname='index',$prefix='',$suffix='';

	public static function findPath($className){
		return self::_findPath($className,CORE,/*#if DEV */class_exists('Config',false)?/*#/if */Config::$autoload_default/*#if DEV */:APP.'models/'/*#/if */);
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
		$forceDefault=self::$inError!==null/*#if DEV */||App::$enhancing/*#/if */;
		if($previousError===$exception) $previousError=null;
		else self::$inError=$exception;
		/*#if DEV */if(isset(App::$enhancing) && App::$enhancing) App::$enhancing->onError(); /*#/if */
		while(ob_get_length()>0) ob_end_clean();
		$log=get_class($exception).' ['.$exception->getCode().']'.' : '.$exception->getMessage().' ('.$exception->getFile().':'.$exception->getLine().")\n";
		//echo $log; ob_flush();
		if(isset($_SERVER['REQUEST_URI'])){
			$log.=' REQUEST_URI='.$_SERVER['REQUEST_URI'];
			if(/*#if DEV */!App::$enhancing && /*#/if */CSecure::isConnected_Safe()) $log.=' Connected='.CSecure::connected();
		}
		if(!empty($_POST)) $log.="\nPOST=".print_r($_POST,true);
		$log.="\nCall Stack:\n".$exception->getTraceAsString();
		
		if($isHttpException=($exception instanceof HttpException)){
			try{
				if($exception instanceof FatalHttpException) CLogger::get('fatal-http-exception')->log($log);
				else CLogger::get(date('d').'-'.'http-exception')->log($log);
			}catch(Exception $e2){
				$previousError = $exception;
				$exception = $e2;
			}
			return;
		}
		if(class_exists('Config',false) && class_exists('CLogger')) CLogger::get('exception')->log($log);
		/*#if DEV */elseif(App::$enhancing){debugPrintr($log); exit;}else die($log);/*#/if */
		
		
		if($previousError!==null){
			$exception=new Exception($exception->getMessage()."\nPrevious error : ".$previousError->getMessage()
										.' ('.$previousError->getFile().':'.$previousError->getLine().')',0,$exception);
		}
		
		if(!headers_sent())
			if(!empty($_SERVER['HTTP_USER_AGENT']) && stripos($_SERVER['HTTP_USER_AGENT'],'bot')===false)
				header('HTTP/1.1 500 Internal Server Error',true,500);
			else
				header('HTTP/1.1 503 Service Unavailable',true,503);
		App::displayException($exception,$forceDefault);
		exit(1);
	}
	
	
	public static function handleError($code,$message,$file,$line,&$context=null,$fromShutdown=false){//debugCode(print_r($context,true));
		if($fromShutdown===false && !($code & (E_STRICT|E_NOTICE)))
			throw new ErrorException($message,$code,0,$file,$line);
		$previousError=self::$inError;
		$forceDefault=self::$inError!==null/*#if DEV */||App::$enhancing/*#/if*/;
		self::$inError=new ErrorException($message,$code,0,$file,$line);
		/*#if DEV */if(isset(App::$enhancing) && App::$enhancing) App::$enhancing->onError(); /*#/if */
		$log=self::getErrorText($code)." : $message ($file:$line)\n";
		//echo $log; ob_flush();
		if(isset($_SERVER['REQUEST_URI'])){
			$log.=' REQUEST_URI='.$_SERVER['REQUEST_URI'];
			/*#if DEV */if(!App::$enhancing){/*#/if */
				if(!class_exists('CSession',false)) include CORE.'components/CSession.php';
				if(!class_exists('CSecure',false)) include CORE.'components/CSecure.php';
				/*#if DEV */
				if(!class_exists('HText',false)) include CORE.'helpers/HText.php';
				if(!class_exists('HDev',false)) include CORE.'helpers/HDev.php';
				/*#/if */
				if(CSecure::isConnected_Safe()) $log.=' Connected='.CSecure::connected();
			/*#if DEV */}/*#/if */
		}
		if(!empty($_POST)) $log.="\nPOST=".print_r($_POST,true);
		$log.="\nCall Stack:\n".prettyBackTrace();
		if($message==='Unsupported operand types') $log.="\nContext:\n".print_r($context,true);
		if(class_exists('Config',false) && class_exists('CLogger')){
			try{
				CLogger::get('error')->log($log);
			}catch(Exception $e2){
				$previousError = self::$inError;
				$message = $e2->getMessage();
			}
		}
		/*#if DEV *//*elseif(App::$enhancing){debug($log); exit;}else die($log);*//*#/if */
		
		/*#if PROD */ if($code & (E_STRICT|E_NOTICE)) return true; /*#/if */
		
		while(ob_get_length()>0) ob_end_clean();
		
		if($previousError!==null){
			$message.="\nPrevious error : ".$previousError->getMessage()
										.' ('.$previousError->getFile().':'.$previousError->getLine().')';
		}
		
		if(!headers_sent())
			if(!empty($_SERVER['HTTP_USER_AGENT']) && stripos($_SERVER['HTTP_USER_AGENT'],'bot')===false)
				header('HTTP/1.1 500 Internal Server Error',true,500);
			else
				header('HTTP/1.1 503 Service Unavailable',true,503);
		App::displayError($forceDefault,$code, $message, $file, $line,$context);
		exit(1);
	}


	public static function getErrorText($code){
		switch($code){
			case E_ERROR: return 'ERROR';
			case E_WARNING: return 'WARNING';
			case E_PARSE: return 'PARSE';
			case E_NOTICE: return 'NOTICE';
			case E_DEPRECATED: return 'DEPRECATED';
			case E_STRICT: return 'STRICT';
			case E_CORE_ERROR: return 'CORE_ERROR';
			case E_CORE_WARNING: return 'CORE_WARNING';
			case E_COMPILE_ERROR: return 'COMPILE_ERROR';
			case E_COMPILE_WARNING: return 'COMPILE_WARNING';
			case E_USER_ERROR: return 'USER_ERROR';
			case E_USER_WARNING: return 'USER_WARNING';
			case E_USER_NOTICE: return 'USER_NOTICE';
			case E_USER_DEPRECATED: return 'USER_DEPRECATED';
			case E_RECOVERABLE_ERROR: return 'RECOVERABLE_ERROR';
		}
		return 'Unknown';
	}
	
	public static function shutdown(){
		if(($error=error_get_last()) && in_array($error['type'],array(E_ERROR,E_PARSE,E_CORE_ERROR,E_CORE_WARNING,E_COMPILE_ERROR,E_COMPILE_WARNING)))
			self::handleError($error['type'],$error['message'],$error['file'],$error['line'],$NULL,true);
		App::shutdown();
	}
}

function __autoload($className){ /*#if DEV */
	if($className==='Config'){
		eval('class Config{public static $autoload_default,$cookie_domain=array("'.Springbok::$scriptname.'"=>""),$models_infos,'
			.'$allLangs=array("'.($lang=(file_exists(dirname(APP).'/src/locales/fr.yml')?'fr':'en')).'"),$availableLangs=array("'.$lang.'"),$siteUrl=array("index"=>""),'
			.'$db=array("default"=>array("dbname"=>"mysql","user"=>"mysql","password"=>"mysql"));}'
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
/*#/if */ include Springbok::findPath($className); }
