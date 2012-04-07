<?php
if(!defined('STDIN')) exit;
/* DEV */ini_set('display_errors',1);/* /DEV */
/* PROD */ini_set('display_errors',0);/* /PROD */
error_reporting(E_ALL | E_STRICT);

set_time_limit(0);

define('BASE_URL',''); define('WEB_FOLDER','');
include CORE.'springbok.php';


class App{
	/* DEV */public static $enhancing=false;/* /DEV */
		
	public static function configArray($name,$withSuffix=false){
		return include APP.'config'.DS.$name.($withSuffix ? '_'.ENV : '').'.php';
	}
	
	public static function run($action,$argv){
		/* DEV */
		if(!empty($argv[1]) && $argv[1]!=='noenhance'){
			include CORE.'enhancers/EnhanceApp.php';
			self::$enhancing=$enhanceApp=new EnhanceApp(dirname(APP));
			$changes=$enhanceApp->process();
		}
		/* /DEV */
		include APP.'config/_'.ENV.'.php';
		/* DEV */
		if(!empty($argv[1]) && $argv[1]!=='noenhance'){
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
		
		if(isset(Config::$base))
			foreach(Config::$base as $name) include CORE.'base/'.$name.'.php';
		try{
			CRoute::cliinit('','');
			if(file_exists($filename=APP.'cli'.DS.$action.'.php'))
				include $filename;
			else include CORE.'cli'.DS.$action.'.php';
		}catch(Exception $exception){
			if(!($exception instanceof HttpException)){
				if($exception instanceof PDOException) $e=new HttpException(503,'Service Temporarily Unavailable');
				else $e=new HttpException(500,'Internal Server Error');
			}else $e=$exception;
			
			Springbok::handleException($exception);
			
			echo ''.$e->getHttpCode().' '.$e->getMessage()."\n";
			echo ''.$e->getDescription().'';
		}
		if(ob_get_length() > 0){
			echo PHP_EOL;
			ob_end_flush();
		}
	}
	
	public static function shutdown(){}
	
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
