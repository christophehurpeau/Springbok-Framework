<?php
if(!defined('STDIN')) exit;
/* DEV */ini_set('display_errors',1);/* /DEV */
/* PROD */ini_set('display_errors',0);/* /PROD */
error_reporting(E_ALL | E_STRICT);

/* PROD */
if (version_compare(PHP_VERSION, '5.3.0')===-1)
	die('PHP Version 5.3.0 is REQUIRED');
/* /PROD */

set_time_limit(0);

define('BASE_URL',''); define('APP_VERSION',''); define('WEB_FOLDER','');
include CORE.'springbok.php';

Springbok::$prefix='cli_';

function display($str,$endChar="\n"){
	echo $str.$endChar;
	if(ob_get_length()>0) ob_flush();
}
class CliColors{
	/* http://www.if-not-true-then-false.com/2010/php-class-for-coloring-php-command-line-cli-scripts-output-php-output-colorizing-using-bash-shell-colors/ */
	const black='0;30',
		darkGray='1;30',
		blue='0;34',
		lightBlue='1;34',
		green='0;32',
		lightGreen='1;32',
		cyan='0;36',
		lightCyan='1;36',
		red='0;31',
		lightRed='1;31',
		purple='0;35',
		lightPurple='1;35',
		brown='0;33',
		yellow='1;33',
		lightGray='0;37',
		white='1;37';
}
function cliColor($str,$color){
	return "\033[".$color."m".$str."\033[0m";
}


class CSession{
	public static function exists($name){ return false; }
	public static function connected($orValue=false){ return $orValue; }
	public static function getOr($name,$orValue=null){ return $orValue; }
}

class App{
	/* DEV */public static $enhancing=false;/* /DEV */
		
	public static function configArray($name,$withSuffix=false){
		return include APP.'config'.DS.$name.($withSuffix ? '_'.ENV : '').'.php';
	}
	
	/** @return CLocale */
	public static function getLocale(){
		return CLocale::get('fr');
	}
	
	
	public static function run($action,$argv){
		/* DEV */
		if(!empty($argv[0]) && $argv[0]==='noenhance'){
			array_shift($argv);
			$shouldEnhance=false;
		}elseif(!empty($argv[1]) && $argv[1]==='noenhance'){
			array_splice($argv,1,1);
			$shouldEnhance=false;
		}elseif(!empty($argv[2]) && $argv[2]==='noenhance'){
			array_splice($argv,2,1);
			$shouldEnhance=false;
		}elseif($shouldEnhance=(!file_exists(dirname(APP).'block_enhance') && $action!=='daemon')){
			include CORE.'enhancers/EnhanceApp.php';
			self::$enhancing=$enhanceApp=new EnhanceApp(dirname(APP));
			$enhanceApp->process();
		}
		/* /DEV */
		include APP.'config/_'.ENV.'.php';
		/* DEV */
		if($shouldEnhance){
			$schemaProcessing=new DBSchemaProcessing(new Folder(APP.'models'),new Folder(APP.'triggers'),true,false);
			self::$enhancing=false;
		}
		
		//$logDir=new Folder(APP.'logs'); $logDir->mkdirs();
		//$tmpDir=new Folder(APP.'tmp'); $tmpDir->mkdirs();
		//$langDir=new Folder(APP.'models/infos'); $langDir->mkdirs();
		
		/* /DEV */
		
		if(!class_exists('Config',false)){
			echo 'CONFIG does not exists';exit;
		}
		
		//if(isset(Config::$base))
		//	foreach(Config::$base as $name) include CORE.'base/'.$name.'.php';
		try{
			CRoute::cliinit(/* DEV */''/* /DEV */);
			$className=basename($action);
			if(ctype_upper($className[0])){ $action.='Cli'; $className.='Cli'; $call=true; }else $call=false;
			if(file_exists($filename=APP.'cli/'.$action.'.php'))
				include $filename;
			else include CORE.'cli/'.$action.'.php';
			if($call) call_user_func_array(array($className,'main'),$argv);
		}catch(Exception $exception){
			if(!($exception instanceof HttpException)){
				$e=new InternalServerError();
			}else $e=$exception;
			
			Springbok::handleException($exception);
			
			echo ''.$e->getHttpCode().' '.$e->getMessage()."\n";
			echo ''.$e->getDescription().'';
		}
		if(ob_get_length() > 0){
			ob_end_flush();
		}
	}
	
	public static function shutdown(){
		echo PHP_EOL;
	}
	
	/**
	 * @param Exception $exception
	 */
	public static function displayException($exception,$forceDefault){
		display('');
		display(cliColor(get_class($exception),CliColors::lightRed));
		display($exception->getMessage()/* DEV */.' ('.str_replace(array(APP,CORE),array('APP/','CORE/'),$exception->getFile()).':'.$exception->getLine().')'/* /DEV */.'');
		/* DEV */
		if($exception->getFile() && $exception->getLine()){
			$content=file($exception->getFile());
			display(cliColor("Line:",CliColors::lightPurple).' '.$content[$exception->getLine()-1]);
		}
		display(cliColor("Backtrace:",CliColors::lightPurple));
		echo prettyBackTrace(0,$exception->getTrace());
		/* /DEV */
		echo "\n";
	}

	public static function displayError($forceDefault,$code, $message, $file, $line){
		echo "\nPHP Error [".Springbok::getErrorText($code)."]";
		echo "\n$message"/* DEV */." ($file:$line)"/* /DEV */;
		/* DEV */
		if($file && $line){
			$content=file($file);
			echo "\nLine: ".$content[$line-1];
		}
		echo "\nBacktrace:\n".prettyBackTrace().'';
		/* /DEV */
		echo "\n";
	}
}

set_exception_handler('Springbok::handleException');
set_error_handler('Springbok::handleError',E_ALL | E_STRICT);
register_shutdown_function('Springbok::shutdown');
App::run($action,$argv);
