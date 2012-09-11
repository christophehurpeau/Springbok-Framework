<?php
if(!defined('STDIN')) exit;
/* DEV */ini_set('display_errors',1);/* /DEV */
/* PROD */ini_set('display_errors',0);/* /PROD */
error_reporting(E_ALL | E_STRICT);

set_time_limit(0);

define('BASE_URL',''); define('APP_VERSION',''); define('WEB_FOLDER','');
include CORE.'springbok.php';

function display($str){
	echo $str."\n";
	ob_flush();
}

class CSession{
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
		if($shouldEnhance=((empty($argv[1]) || $argv[1]!=='noenhance') && !file_exists(dirname(APP).'block_enhance') && $action!=='daemon')){
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
		echo ''.get_class($exception)."\n";
		echo ''.$exception->getMessage()/* DEV */.' ('.str_replace(array(APP,CORE),array('APP/','CORE/'),$exception->getFile()).':'.$exception->getLine().')'/* /DEV */.'';
		/* DEV */
		if($exception->getFile() && $exception->getLine()){
			$content=file($exception->getFile());
			echo PHP_EOL.'Line : '.$content[$exception->getLine()];
		}
		echo PHP_EOL.'Backtrace : '.prettyBackTrace(0,$exception->getTrace()).'';
		/* /DEV */
		echo PHP_EOL;
	}

	public static function displayError($forceDefault,$code, $message, $file, $line){
		echo "PHP Error [".Springbok::getErrorText($code)."]\n";
		echo "$message"/* DEV */." ($file:$line)"/* /DEV */."\n";
		/* DEV */
		if($file && $line){
			$content=file($file);
			echo PHP_EOL.'Line : '.$content[$line-1];
		}
		echo 'Backtrace :'.prettyBackTrace().'';
		/* /DEV */
		echo PHP_EOL;
	}
}

set_exception_handler('Springbok::handleException');
set_error_handler('Springbok::handleError',E_ALL | E_STRICT);
register_shutdown_function('Springbok::shutdown');
App::run($action,$argv);
