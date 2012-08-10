<?php
/* DEV */ini_set('display_errors',1);/* /DEV */
/* PROD */ini_set('display_errors',0);/* /PROD */
error_reporting(E_ALL | E_STRICT);

include CORE.'springbok.php';

require '_init.php';

/* DEV */
define('APP_DATE',time());
define('APP_VERSION','');
define('WEB_FOLDER','');
/* /DEV */

require 'springbok/Controller.php';
require 'components/CRoute.php';
require 'components/CHttpRequest.php';

class App{
	/* DEV */public static $enhancing=false,$currentFileEnhanced='',$changes=array();/* /DEV */
	
	public static function configArray($name,$withSuffix=false){
		return include APP.'config/'.$name.($withSuffix ? '_'.ENV : '').'.php';
	}
	
	/** @return CLocale */
	public static function getLocale(){
		return CLocale::get('fr');
	}
	
	public static function run(){
		/* DEV */
		if(!file_exists(dirname(APP).'/src/config/_'.ENV.'.php')) exit('The config for your environnement: "'.ENV.'" does NOT exist !');
		
		include CORE.'enhancers/EnhanceApp.php';
		$shouldEnhance=!isset($_GET['springbokNoEnhance']) && !CHttpRequest::isAjax() && empty($_SERVER['HTTP_ORIGIN']) && !file_exists(dirname(APP).'/block_deploy');
		if($shouldEnhance){
			$pathInfo=CHttpRequest::getPathInfo();
			$pathInfo=basename($pathInfo);
			$ext=strrpos($pathInfo,'.');
			if($ext!==false) $ext=substr($pathInfo,$ext+1);
			if($ext!==false && in_array($ext,array('png','jpg','css','js','gif'))) $shouldEnhance=false;
		}
		
		if($shouldEnhance){
			$t=microtime(true);
			self::$enhancing=$enhanceApp=new EnhanceApp(dirname(APP));
			$process=$enhanceApp->process();
			$changes=$process?$process->getChanges():false;
			self::$enhancing=false;
			if(!empty($changes)){
				self::$changes[0]=array(microtime(true) - $t,$changes,$process->getErrors(),$process->getWarnings());
			}
			
		}
		
		//$logDir=new Folder(APP.'logs'); $logDir->mkdirs();
		//$tmpDir=new Folder(APP.'tmp'); $tmpDir->mkdirs();
		//$langDir=new Folder(APP.'models/infos'); $langDir->mkdirs();
		
		/* /DEV */
				
		include APP.'config/_'.ENV.'.php';
		if(!class_exists('Config',false)) exit('CONFIG does not exists');
		
		/* DEV */
		//$t=microtime(true);
		if($shouldEnhance){
			$updateCookie=false;
			$generateSchema=($changes && !empty($changes['Model'])) || CHttpRequest::_GETor('apply')==='springbokProcessSchema';
			$cookie=CCookie::get('springbok');
			if(!$generateSchema){
				/*if(!CCookie::exists('springbok') || !isset($cookie->check)){
					if(isset($_GET['check']) && ($_GET['check']==='springbokCheckFalse' || $_GET['check']==='springbokCheckTrue')){
						if($_GET['check']==='springbokCheckFalse') $cookie->check=false;
						elseif($_GET['check']==='springbokCheckTrue') $cookie->check=true;
						$cookie->write();
						header('Location: '.$_REQUEST['REQUEST_URI']=substr($_SERVER['REQUEST_URI'],0,-strlen('?check='.$_GET['check'])));
						exit;
					}else{
						$vars=array(); render(CORE.'db/check-view.php',$vars);
						exit;
					}
				}*/
				if(/*$cookie->check && (*/empty($cookie->lastProcess) || $cookie->lastProcess<(time()-(60*60*10)))/*)*/{
					$cookie->lastProcess=time();
					$updateCookie=$generateSchema=true;
				}
			}else{
				$cookie->lastProcess=time();
				$updateCookie=true;
			}
			
			if($generateSchema || $changes){
				$modelFolder=new Folder(APP.'models');
				/*if($changes && !empty($changes['Model'])){ */
				$schemaProcessing=new DBSchemaProcessing($modelFolder,new Folder(APP.'triggers'),false,$generateSchema);
				/*}else DBSchema::checkPropDef($modelFolder);*/
			}
			
			if(isset(Config::$plugins)){
				include CORE.'enhancers/EnhancePlugin.php';
				foreach(Config::$plugins as $key=>&$plugin){
					if(!isset($plugin[2])) continue;
					self::$enhancing=$enhancePlugin=new EnhancePlugin($pluginFolder=(Config::$pluginsPaths[$plugin[0]].$plugin[1]));
					$changes=$enhancePlugin->process();
					self::$enhancing=false;
					
					if(!$generateSchema) $generateSchema=$changes && !empty($changes['Model']);
					$plugin[1].='/dev';
					
					$modelFolder=new Folder($pluginFolder.'/dev/models/');
					$schemaProcessing=new DBSchemaProcessing($modelFolder,new Folder($pluginFolder.'/dev/triggers/'),false,$generateSchema);
				}
			}
			if($updateCookie) $cookie->write();
		}
		//debug('schema process took : '.(microtime(true) - $t).' s');
		
		/* /DEV */

		
		//if(isset(Config::$base))
		//	foreach(Config::$base as $name) include CORE.'base/'.$name.'.php';
		try{
			/* DEV */
			if(isset(Config::$dev_prefixed_routes) && !Config::$dev_prefixed_routes){
				Springbok::$scriptname=strstr($_SERVER['HTTP_HOST'],'.',true);
				if(Springbok::$scriptname==='www') Springbok::$scriptname='index';
			}
			/* /DEV */
			if(Springbok::$scriptname==='index'){
				Springbok::$prefix=Springbok::$suffix='';
				CRoute::init(/* DEV */''/* /DEV */);
			}else{
				Springbok::$prefix=Springbok::$scriptname.'_';
				Springbok::$suffix='.'.Springbok::$scriptname;
				
				/* DEV */
				CRoute::init(!isset(Config::$dev_prefixed_routes)||Config::$dev_prefixed_routes?'/'.Springbok::$scriptname:'','_'.Springbok::$scriptname);
				if(CRoute::getController()==='Web'){
					Controller::renderFile(APP.substr(CRoute::getAll(),1));
				}
				/* /DEV */
				/* PROD */
				CRoute::init();
				/* /PROD */
			}
			Controller::$defaultLayout=Springbok::$prefix.'default';
			
			//TODO do some optimization with cache + langs
			$filename=APP.'controllers'.Springbok::$suffix.'/methods/'.CRoute::getController().'-'.CRoute::getAction();
			if(!file_exists($filename))
				/* DEV */ throw new Exception('This action does not exists : '.Springbok::$suffix.' '.CRoute::getController().'::'.CRoute::getAction().' ('.CRoute::getAll().')'); /* /DEV */
				/* PROD */ notFound(); /* /PROD */
			$mdef=include $filename;
			
			$controllerName=CRoute::getController().'Controller';
			/* if(!file_exists($filename=APP.'controllers'.$suffix.'/'.$controllerName.'.php')) notFound(); */
			/* DEV */ if(!file_exists(APP.'controllers'.Springbok::$suffix.'/'.$controllerName.'.php')) throw new Exception("Controller does not exists : ".$controllerName); /* /DEV */
			include APP.'controllers'.Springbok::$suffix.'/'.$controllerName.'.php';
			
			/*if(!class_exists($controllerName,false)) notFound();*/
			/* DEV */ if(!class_exists($controllerName,false)) throw new Exception("Controller Class does not exists : ".$controllerName); /* /DEV */
			$controllerName::dispatch(Springbok::$suffix,$mdef);
		}catch(Exception $exception){
			if(!($exception instanceof HttpException)){
				if($exception instanceof DBException) $e=new FatalHttpException(503,'Service Temporarily Unavailable');
				elseif($exception instanceof mysqli_sql_exception){
					/* http://dev.mysql.com/doc/refman//5.5/en/error-messages-server.html */
					$code=$exception->getCode();
					if($code===1040)
						$e=$exception=new FatalHttpException(503,'Service Temporarily Unavailable',_tC('The server is currently overloaded'),$exception);
					if($code===2002) /* Can't connect to local MySQL server through socket */
						$e=$exception=new FatalHttpException(503,'Service Temporarily Unavailable',_tC('The database is currently inaccessible'),$exception);
					elseif($code<1022){
						$e=$exception=new FatalHttpException(503,'Service Temporarily Unavailable',_tC('The server is currently overloaded').'',$exception);
					}else $e=new FatalHttpException(503,'Service Temporarily Unavailable');
				}else $e=new FatalHttpException(500,'Internal Server Error');
			}else $e=$exception;
			
			$server_protocol = isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : false;
			if(!($server_protocol==='HTTP/1.1' OR $server_protocol==='HTTP/1.0'))
				$server_protocol='HTTP/1.1';
			
			if(!headers_sent())
				header($server_protocol.' '.$e->getHttpCode().' '.$e->getMessage(), true, $e->getHttpCode());
			
			Springbok::handleException($exception);
			
			/* DEV */
			self::displayException($e,false);
			/* /DEV */
			/* PROD */
			if($e->getDescription()===false) exit;
			$vars=array('title'=>$e->getHttpCode().' '.$e->getMessage(),'descr'=>$e->getDescription());
			if(file_exists(APP.'views'.Springbok::$suffix.'/http-exception.php')){
				include_once CORE.'mvc/views/View.php';
				render(APP.'views'.Springbok::$suffix.'/http-exception.php',$vars);
			}else render(CORE.'mvc/views/http-exception.php',$vars);
			/* /PROD */
		}
	}
	
	public static function shutdown(){
		/* DEV */
		if(!error_get_last() && class_exists('DB',false) && CFirebug::isAvailable() && !headers_sent()){
			$setGroup=false;
			foreach(DB::getAll() as $dbname=>$db){
				$queries=$db->getQueries(); $totalQuery=count($queries);
				if($totalQuery > 0){
					$table=array(array('#','Query','Time'));
					$irow=0;
					foreach($queries as $query) $table[]=array($irow++,$query['query'],number_format($query['time']*1000,0,'',' ').' ms');
					if($setGroup===false){ $setGroup=true; CFirebug::group('Queries',array('Collapsed'=>true)); }
					CFirebug::table($dbname.' - '.$totalQuery,$table);
				}
			}
			if($setGroup!==false) CFirebug::groupEnd();
			if(!empty(CRoute::$TESTED_ROUTES)){
				$setGroup=true; CFirebug::group('Routes',array('Collapsed'=>true));
				CFirebug::table('Current Route',array(array('All','Controller','Action','Ext','Params'),
						array(CRoute::getAll(),CRoute::getController(),CRoute::getAction(),short_debug_var(CRoute::getExt()),print_r(CRoute::getParams(),true)))	);
				$testedRoutes=CRoute::$TESTED_ROUTES;
				array_unshift($testedRoutes,'Regexp');
				CFirebug::table('Tested routes',array_map(function($r){return array($r);},$testedRoutes));
			}
			if($setGroup!==false) CFirebug::groupEnd();
		}
		/* /DEV */
	}
	
	/**
	 * @param Exception $exception
	 */
	public static function displayException(&$exception,$forceDefault){
		/*header_remove('Content-Description');header_remove('Content-Disposition');header_remove('Content-type');header_remove('Transfer-Encoding');*/
		$type=CHttpRequest::acceptsByExtOrHttpAccept('html','json','xml');
		if($type && $type!=='html'){
			$content=array('error'=>array('type'=>'exception','class'=>get_class($exception)
					/* DEV */,'message'=>$exception->getMessage()/* /DEV */));
			if($type==='xml') displayXml($content);
			else displayJson($content);
		}else{
			if(!headers_sent()) header("Content-Type: text/html; charset=UTF-8",true);
			$vars=array(
				'e'=>&$exception,
				'e_className'=>get_class($exception),
				'e_message'=>/* DEV */(self::$enhancing?'Current File Enhanced : '.self::$currentFileEnhanced."\n":'')./* /DEV */$exception->getMessage(),
				'e_file'=>$exception->getFile(),
				'e_line'=>$exception->getLine(),
				'e_trace'=>$exception->getTrace(),
			);
			if($forceDefault===false && file_exists(APP.'views'.Springbok::$suffix.'/exception.php')){
				include_once CORE.'mvc/views/View.php';
				render(APP.'views'.Springbok::$suffix.'/exception.php',$vars);
			}else render(CORE.'mvc/views/exception.php',$vars);
		}
	}
	
	public static function displayError($forceDefault,$code,$message,$file,$line,&$context=null){
		/*header_remove('Content-Description');header_remove('Content-Disposition');header_remove('Content-type');header_remove('Transfer-Encoding');*/
		$type=CHttpRequest::acceptsByExtOrHttpAccept('html','json','xml');
		if($type==='json'){
			header('Content-type: application/json; charset=UTF-8');
			exit('{"error":{'
				.'"type":"error",'
				.'"type":'.json_encode(Springbok::getErrorText($code))/* DEV */.','
				.'"message":'.json_encode($message) /* /DEV */
			.'}}');
		}else{
			if(!headers_sent()) header("Content-Type: text/html; charset=UTF-8",true);
			$vars=array(
				'e_name'=>Springbok::getErrorText($code),
				'e_message'=>/* DEV */(self::$enhancing?'Current File Enhanced : '.self::$currentFileEnhanced."\n":'')./* /DEV */$message,
				'e_file'=>$file,
				'e_line'=>$line,
				'e_context'=>$context,
			);
			//debugVar($vars);
			if($forceDefault===false && file_exists(APP.'views'.Springbok::$suffix.'/error.php')){
				include_once CORE.'mvc/views/View.php';
				render(APP.'views'.Springbok::$suffix.'/error.php',$vars);
			}else render(CORE.'mvc/views/error.php',$vars);
		}
	}
}
set_exception_handler('Springbok::handleException');
set_error_handler('Springbok::handleError',E_ALL | E_STRICT);
register_shutdown_function('Springbok::shutdown');
App::run();