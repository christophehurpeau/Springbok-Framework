<?php
/*#if DEV */ini_set('display_errors',1);/*#/if*/
/*#if PROD*/ini_set('display_errors',0);/*#/if*/
error_reporting(E_ALL/* | E_STRICT*/);

ob_start();
include CORE.'springbok.php';

require '_init.php';

/*#if DEV */
define('APP_DATE',time());
define('APP_VERSION','');
define('WEB_FOLDER','');
/*#/if */

require 'springbok/Controller.php';
require 'components/CRoute.php';
require 'components/CHttpRequest.php';

class App{
	/*#if DEV */public static $enhancing=false,$enhanceApp,$currentFileEnhanced='',$changes=array();/*#/if */
	
	/**
	 * Include the config from APP/config/
	 * 
	 * @param string
	 * @param bool
	 * @return array
	 */
	public static function configArray($name,$withSuffix=false){
		return include APP.'config/'.$name.($withSuffix ? '_'.ENV : '').'.php';
	}
	
	/**
	 * Return the site url with http or https
	 * 
	 * @param string
	 * @param bool|null
	 * @return string
	 */
	public static function siteUrl($entry,$https=null){
		$su=Config::$siteUrl[$entry];
		return ($su[0]===null ? ($https===null ? HTTP_OR_HTTPS : ($https===true ? 'https://' : 'http://')) : $su[0]). $su[1];
	}
	
	/**
	 * @return CLocale
	 */
	public static function getLocale(){
		return CLocale::get('fr');
	}
	
	/**
	 * Run the app.
	 * Called in index.php
	 */
	public static function run(){
		/*#if DEV */
		if(!file_exists($pathConfigFile=dirname(APP).'/src/config/_'.ENV.'.php')
				 && !file_exists(substr($pathConfigFile,0,-3).'yml'))
			exit('The config for your environnement: "'.ENV.'" does NOT exist ! Please create '.$pathConfigFile);
		
		$shouldEnhance=!empty($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'],'ApacheBench')===false && !isset($_GET['springbokNoEnhance'])
				&& !(CHttpRequest::isAjax()&&!isset($_GET['AJAX'])) && !CHttpRequest::isFlash()
				&& empty($_SERVER['HTTP_ORIGIN']) && !file_exists(dirname(APP).'/block_deploy');
		if(!$shouldEnhance){
			define('CORE_SRC',dirname(CORE).'/src/');
		}else{
			include CORE.'enhancers/EnhanceApp.php';
			
			$pathInfo=CHttpRequest::getPathInfo();
			$pathInfo=basename($pathInfo);
			$ext=strrpos($pathInfo,'.');
			if($ext!==false) $ext=substr($pathInfo,$ext+1);
			if($ext!==false && in_array($ext,array('png','jpg','css','js','gif'))) $shouldEnhance=false;
		
		
			$t=microtime(true);
			self::$enhanceApp=self::$enhancing=$enhanceApp=new EnhanceApp(dirname(APP));
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
		
		/*#/if */
				
		include APP.'config/_'.ENV.'.php';
		if(!class_exists('Config',false)) exit('CONFIG does not exists');
		
		/*#if DEV */
		//$t=microtime(true);
		if($shouldEnhance){
			$updateCookie=false;
			$generateSchema=($changes && !empty($changes['Model'])) || ($apply=CHttpRequest::_GETor('apply'))==='springbokProcessSchema' || $apply==='springbok_Evolu_Schema';
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
		
		/*#/if */

		
		//if(isset(Config::$base))
		//	foreach(Config::$base as $name) include CORE.'base/'.$name.'.php';
		try{
			/*#if DEV */
			if(rtrim(App::siteUrl('index',false),'/')!=='http://localhost'){
				$scriptname=strstr($_SERVER['HTTP_HOST'],'.',true);
				if(isset(Config::$siteUrl[$scriptname])){ //dev sur un serveur
					Springbok::$scriptname=$scriptname;
					if(Springbok::$scriptname==='www' || empty(Springbok::$scriptname)) Springbok::$scriptname='index';
				}
			}
			/*#/if */
			if(Springbok::$scriptname==='index'){
				Springbok::$prefix=Springbok::$suffix='';
				CRoute::init(/*#if DEV */''/*#/if */);
			}else{
				Springbok::$prefix=Springbok::$scriptname.'_';
				Springbok::$suffix='.'.Springbok::$scriptname;
				
				/*#if DEV */
				CRoute::init(rtrim(App::siteUrl('index',false),'/')==='http://localhost'?'/'.Springbok::$scriptname:'','_'.Springbok::$scriptname);
				if(CRoute::getController()==='Web'){
					Controller::renderFile(APP.substr(CRoute::getAll(),1));
				}
				/*#else*/
				CRoute::init();
				/*#/if*/
			}
			Controller::$defaultLayout=Springbok::$prefix.'default';
			
			//TODO do some optimization with cache + langs
			$mdef=APP.'controllers'.Springbok::$suffix.'/methods/'.CRoute::getController().'-'.CRoute::getAction();
			if(!file_exists($mdef))
				/*#if DEV */ throw new Exception('This action does not exists : '.Springbok::$suffix.' '.CRoute::getController().'::'.CRoute::getAction().' ('.CRoute::getAll().')');
				/*#else*/ notFound(); /*#/if*/
			
			$controllerName=CRoute::getController().'Controller';
			/* if(!file_exists($filename=APP.'controllers'.$suffix.'/'.$controllerName.'.php')) notFound(); */
			/*#if DEV */ if(!file_exists(APP.'controllers'.Springbok::$suffix.'/'.$controllerName.'.php')) throw new Exception("Controller does not exists : ".$controllerName); /*#/if*/
			include APP.'controllers'.Springbok::$suffix.'/'.$controllerName.'.php';
			
			/*if(!class_exists($controllerName,false)) notFound();*/
			/*#if DEV */ if(!class_exists($controllerName,false)) throw new Exception("Controller Class does not exists : ".$controllerName); /*#/if*/
			$controllerName::dispatch(Springbok::$suffix,$mdef);
		}catch(Exception $exception){
			$forceDefault=false;
			if(!($exception instanceof HttpException)){
				if(Springbok::$inError!==null && Springbok::$inError instanceof HttpException){
					$e=Springbok::$inError;
					$forceDefault=true;
				}elseif($exception instanceof DBException) $e=new FatalHttpException(503,'Service Temporarily Unavailable','Service Temporarily Unavailable');
				elseif($exception instanceof mysqli_sql_exception){
					/* http://dev.mysql.com/doc/refman//5.5/en/error-messages-server.html */
					$code=$exception->getCode();
					if($code===1040)
						$e=$exception=new FatalHttpException(503,'Service Temporarily Unavailable',_tC('The server is currently overloaded'),'',$exception);
					if($code===2002) /* Can't connect to local MySQL server through socket */
						$e=$exception=new FatalHttpException(503,'Service Temporarily Unavailable',_tC('The database is currently inaccessible'),'',$exception);
					elseif($code<1022){
						$e=$exception=new FatalHttpException(503,'Service Temporarily Unavailable',_tC('The server is currently overloaded').'','',$exception);
					}else $e=new FatalHttpException(503,'Service Temporarily Unavailable','Service Temporarily Unavailable');
				}elseif($exception instanceof MongoCursorException){
					$code=$exception->getCode();
					if($code===13||$code===10||$code===8||$code===7||$code===6||$code===4||$code===14||$code===16)
						$e=$exception=new FatalHttpException(503,'Service Temporarily Unavailable',_tC('The database is currently inaccessible'),'',$exception);
					else $e=new FatalHttpException(503,'Service Temporarily Unavailable','Service Temporarily Unavailable');
				}elseif($exception instanceof MongoCursorTimeoutException || $exception instanceof MongoConnectionException){
					$e=$exception=new FatalHttpException(503,'Service Temporarily Unavailable',_tC('The database is currently inaccessible'),'',$exception);
				}else $e=new InternalServerError();
			}else $e=$exception;
			
			$server_protocol = isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : false;
			if(!($server_protocol==='HTTP/1.1' OR $server_protocol==='HTTP/1.0'))
				$server_protocol='HTTP/1.1';
			
			if(!headers_sent())
				header($server_protocol.' '.$e->getHttpCode().' '.$e->getMessage(), true, $e->getHttpCode());
			
			Springbok::handleException($exception);
			
			/*#if DEV */
			self::displayException($e,false);
			/*#else*/
			if($e->getDescription()===false) exit;
			$vars=array('title'=>$e->getTitle(),'descr'=>$e->getDescription());
			if($forceDefault===false && file_exists(APP.'views'.Springbok::$suffix.'/http-exception.php')){
				include_once CORE.'mvc/views/View.php';
				render(APP.'views'.Springbok::$suffix.'/http-exception.php',$vars);
			}else render(CORE.'mvc/views/http-exception.php',$vars);
			/*#/if*/
		}
	}
	
	
	public static function shutdown(){
		/*#if DEV */
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
						array(CRoute::getAll(),CRoute::getController(),CRoute::getAction(),UVarDump::dump(CRoute::getExt()),print_r(CRoute::getParams(),true)))	);
				$testedRoutes=CRoute::$TESTED_ROUTES;
				array_unshift($testedRoutes,'Regexp');
				CFirebug::table('Tested routes',array_map(function($r){return array($r);},$testedRoutes));
			}
			if($setGroup!==false) CFirebug::groupEnd();
		}
		/*#/if */
	}
	
	/**
	 * @param Exception $exception
	 * @param bool
	 * @return void
	 */
	public static function displayException(&$exception,$forceDefault){
		/*header_remove('Content-Description');header_remove('Content-Disposition');header_remove('Content-type');header_remove('Transfer-Encoding');*/
		$type=CHttpRequest::acceptsByExtOrHttpAccept('html','json','xml');
		if($type && $type!=='html'){
			$content=array('error'=>array('type'=>'exception','class'=>get_class($exception)
					/*#if DEV */,'message'=>$exception->getMessage()/*#/if*/));
			if($type==='xml') displayXml($content);
			else displayJson($content);
		}else{
			if(!headers_sent()) header("Content-Type: text/html; charset=UTF-8",true);
			$vars=array(
				'e'=>&$exception,
				'e_className'=>get_class($exception),
				'e_message'=>/*#if DEV */(self::$enhancing?'Current File Enhanced : '.self::$currentFileEnhanced."\n":'')./*#/if*/$exception->getMessage(),
				'e_file'=>$exception->getFile(),
				'e_line'=>$exception->getLine(),
				'e_trace'=>$exception->getTrace(),
			);
			if($forceDefault===false && file_exists(APP.'views'.Springbok::$suffix.'/exception.php')
					/*#if DEV */ && class_exists('CRoute',false) && substr(CRoute::getAll(),0,5)!=='/Dev/'/*#/if*/){
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
				.'"type":'.json_encode(Springbok::getErrorText($code))/*#if DEV */.','
				.'"message":'.json_encode($message) /*#/if */
			.'}}');
		}else{
			if(!headers_sent()) header("Content-Type: text/html; charset=UTF-8",true);
			$vars=array(
				'e_name'=>Springbok::getErrorText($code),
				'e_message'=>/*#if DEV */(self::$enhancing?'Current File Enhanced : '.self::$currentFileEnhanced."\n":'')./*#/if */$message,
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