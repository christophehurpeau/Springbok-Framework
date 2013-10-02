<?php
/**
 * Controller
 */
class Controller{
	/**
	 * The entry point, or '' if the entry is "index"
	 * 
	 * @var string
	 */
	protected static $suffix;
	
	/**
	 * Default layout path
	 * 
	 * This variable is initialized in app.php
	 * 
	 * You can override it before declaring your controller class
	 * 
	 * @var string
	 */
	public static $defaultLayout;
	/**
	 * The layout overrided name
	 * 
	 * Used in Ajax content loading
	 * 
	 * @var string|null
	 */
	public static $defaultLayoutOverride=null;
	
	/**
	 * @var array
	 */
	private static $viewVars=array();
	
	/**
	 * @var array
	 */
	private static $layoutVars=array();
	
	/**
	 * Called before dispatching the action.
	 * 
	 * You can end the request here, check the authentifications, or do other things.
	 * 
	 * @see Controoler::beforeRender()
	 */
	protected static function beforeDispatch(){}
	
	/**
	 * @internal
	 * Dispatch the action to the right function with the right arguments
	 * 
	 * @param string
	 * @param array the method definition
	 * @return mixed
	 */
	public static function dispatch($suffix,$mdef){
		self::$suffix=$suffix;
		static::beforeDispatch();
		if(!method_exists(get_called_class(),$methodName=CRoute::getAction())) notFound();
		$mdef=include $mdef;
		$methodAnnotations=$mdef['annotations'];
		//if(isset(static::$_classAnnotations))
		//	$methodAnnotations += static::$_classAnnotations;
		if(isset($methodAnnotations['SubAction'])) notFound();
		return call_user_func_array(array('static',$methodName),self::getParams($mdef,$methodAnnotations));
	}
	
	/**
	 * @internal
	 * Loads the method definition
	 * 
	 * @param string
	 * @param string
	 * @return array
	 * @throws HttpException
	 */
	public static function _loadMdef($controller,$action){
		$mdef=APP.'controllers'.self::$suffix.'/methods/'.$controller.'-'.$action;
		if(!file_exists($mdef))
			/*#if DEV */ throw new Exception('This action does not exists : '.Springbok::$suffix.' '.$controller.'::'.$action.' ('.CRoute::getAll().')');
			/*#else*/ notFound(); /*#/if*/
		return include $mdef;
	}
	
	/**
	 * @param array
	 * @return void
	 */
	protected static function checkAccess($checkAnnotation){
		ACSecure::checkAccess($checkAnnotation);
	}
	
	/**
	 * @param array
	 * @param array
	 * @param array|null
	 */
	protected static function getParams($mdef,$methodAnnotations,$rParams=null){
		if($mdef['params']===false) return array();
		
		/* DONT FORGET TO CHANGE RESTCONTROLLER AND SOCKETCONTROLLER */
		$params=array();
		
		$method=CHttpRequest::getMethod();
		if($method==='GET') $DATA=$_GET;
		elseif($method==='POST') $DATA=$_POST;
		
		if($rParams===null) $rParams=CRoute::getParams();
		$num=0;
		foreach($mdef['params'] as $paramName=>$def){
			if($rParams && isset($rParams[$paramName])) $val=$rParams[$paramName];
			elseif($rParams && isset($rParams[$num])) $val=$rParams[$num];
			elseif(isset($DATA[$paramName])) $val=$DATA[$paramName];
			else $val=null;
			if($val !== null) $val=CBinder::bind($def['type'],$val,isset($def['annotations'])?$def['annotations']:false,$paramName);
			if($val===null && isset($def['annotations']['Required']) || (isset($def['annotations']['Valid']) && $def['annotations']['Valid']===false)) CValidation::required($paramName,false);
			$params[]=$val;
			$num++;
		}
		if(isset($methodAnnotations['ValidParams']) && CValidation::hasErrors()){
			/*#if DEV */throw new Exception('Not valid params : '.print_r(CValidation::errors(),true)
																		."\n\nparams=".print_r($params,true)
																		."\n\nmdef params=".print_r($mdef['params'],true)
																		."\n\n\$_GET=".print_r($_GET,true)
																		."\n\n\$_POST=".print_r($_POST,true));/*#/if*/
			if($methodAnnotations['ValidParams']===false) notFound();
			else{
				self::header404();
				self::redirect($methodAnnotations['ValidParams'][0]);
			}
		}
		
		return $params;
	}


	/* GETTERS & SETTERS */
	
	/**
	 * Set a value in a view
	 * 
	 * @param string
	 * @param mixed
	 * @return void
	 */
	protected static function set($name,$value=null){
		/*#if DEV */
		if(is_array($name))
			throw new Exception('Controller::set array => use mset');
		/*#/if*/
		self::$viewVars[$name]=$value;
	}
	
	
	/**
	 * Set a value in a view, by reference
	 * 
	 * @param string
	 * @param mixed
	 * @return void
	 */
	protected static function set_($name,&$value){
		self::$viewVars[$name]=&$value;
	}
	
	/**
	 * Set multiple values in a view
	 * 
	 * @param array
	 * @return void
	 */
	protected static function mset($array){
		self::$viewVars=$array+self::$viewVars;
	}
	
	/**
	 * Set a value in a view, same as set, but public.
	 * 
	 * @see set
	 * @return void
	 */
	public static function setForView($name,$value){
		self::$viewVars[$name]=$value;
	}
	
	/**
	 * Set a value in a layout
	 * 
	 * @param string
	 * @param mixed
	 * @return void
	 */
	public static function setForLayout($name,$value=null){
		/*#if DEV */
		if(is_array($name))
			throw new Exception('Controller::setForLayout array => use msetForLayout');
		/*#/if*/
		self::$layoutVars[$name]=$value;
	}
	
	/**
	 * Set a value in a view and a layout
	 * 
	 * @param string
	 * @param mixed
	 * @return void
	 */
	public static function setForLayoutAndView($name,$value){
		/*#if DEV */
		if(is_array($name))
			throw new Exception('Controller::setForLayout array => use msetForLayoutAndView');
		/*#/if*/
		self::$layoutVars[$name]=self::$viewVars[$name]=$value;
	}
	
	/**
	 * Set multiple values in a view and a layout
	 * 
	 * @param array
	 * @return void
	 */
	public static function msetForLayoutAndView($name){
		self::$viewVars=$name+self::$viewVars;
		self::$layoutVars=$name+self::$layoutVars;
		self::$layoutVars[$name]=self::$viewVars[$name]=$value;
	}
	
	/**
	 * Check if a key exists in the view vars
	 * 
	 * @param string
	 * @return bool
	 */
	public static function _isset($name){
		return isset(self::$viewVars[$name]);
	}
	
	/**
	 * Get a value in the view vars
	 * 
	 * @param string
	 * @return mixed
	 */
	public static function get($name){
		return self::$viewVars[$name];
	}
	
	/**
	 * Get all view vars
	 * 
	 * @return array
	 */
	public static function getViewVars(){
		return self::$viewVars;
	}
	
	/**
	 * Get all layout vars
	 * 
	 * @return array
	 */
	public static function getLayoutVars(){
		return self::$layoutVars;
	}
	
	/**
	 * Get a value in the layout vars
	 * 
	 * @param string
	 * @return mixed
	 */
	public static function getLayoutVar($name){
		return self::$layoutVars[$name];
	}
	
	/* call controllers */
	
	/**
	 * Call an other controller
	 * 
	 * @param string
	 * @param string
	 * @param array
	 */
	protected static function callController($controllerName,$actionName,$params=array()){
		CRoute::setControllerAndAction($controllerName,$actionName);
		$controllerName.='Controller';
		include APP.'controllers'.self::$suffix.'/'.$controllerName.'.php';
		call_user_func_array(array($controllerName,$actionName),$params);
		exit;
	}
	
	/**
	 * Call a sub-action
	 * 
	 * @param string
	 * @param string
	 * @param array
	 * @param array
	 */
	protected static function callSubAction($controllerName,$actionName,$rParams,$moreParams){
		$mdef=self::_loadMdef($controllerName,$actionName);
		$params=self::getParams($mdef,$mdef['annotations'],$rParams);
		foreach($moreParams as $k=>$p) $params[$k]=$p;
		self::callController($controllerName,$actionName,$params);
	}
	
	/* */
	
	/**
	 * Callback for each files uploaded
	 * @param string $name POST name
	 * @param function $callback function($name,$tmp_name,$size,$type)
	 */
	protected static function uploadedFiles($name,$callback){
		if(empty($_FILES) || empty($_FILES[$name]))
			CValidation::addError($name,_tC('No files'));
		else{
			$errors=array();
			foreach($_FILES[$name]['error'] as $key=>$error){
				if($error === UPLOAD_ERR_OK){
					$callback($_FILES[$name]['name'][$key],$_FILES[$name]['tmp_name'][$key],$_FILES[$name]['size'][$key],$_FILES[$name]['type'][$key]);
				}else $errors[$key]=_tC('Upload error');
			}
		}
		
	}
	
	/**
	 * Move an uploaded file to a new path
	 * 
	 * @param string $name POST name
	 * @param string $to path
	 * @return bool if the uploaded file was successfully moved
	 * @uses move_uploaded_file
	 */
	public static function moveUploadedFile($name,$to){
		if(!empty($_FILES[$name]) && $_FILES[$name]['error'] == UPLOAD_ERR_OK){
			move_uploaded_file($_FILES[$name]['tmp_name'],$to);
			return true;
		}
		return false;
	}
	
	/**
	 * Set 404 Not Found Headers, but without displaying an exception
	 * 
	 * @return void
	 */
	public static function header404(){
		header('HTTP/1.1 404 Not Found');
		header('Status: 404 Not Found',false,404);
	}
	
	/**
	 * Redirect to a new url
	 * 
	 * @param string|array $to
	 * @param string|null $entry
	 * @param bool $exit
	 * @param bool $forbiddenForAjax instead of redirecting, throw a forbidden Exception if the request was made in Ajax
	 * @param bool $permanent 301 Moved Permanently if true (use redirectPermanent() instead)
	 * @return void
	 * 
	 * @see HHtml::url
	 */
	public static function redirect($to,$entry=null,$exit=true,$forbiddendForAjax=true,$permanent=false){
		if(CHttpRequest::isAjax()){
			/*if(isset($_GET['ajax']))
				self::renderHtml(HHtml::jsInline('S.ajax._load(\'container\','.json_encode(HHtml::url($to)).')'));
			else*/if(isset($_SERVER['HTTP_SPRINGBOKAJAXPAGE'])||isset($_SERVER['HTTP_SPRINGBOKAJAXFORMSUBMIT'])){
				header('SpringbokRedirect: '.HHtml::url($to,$entry));
				if($exit) exit;
				return;
			}
			elseif($forbiddendForAjax) forbidden();
		}elseif($permanent){
			header('HTTP/1.1 301 Moved Permanently');
			header('Status: 301 Moved Permanently',false,301);
		}
		header('Location: '.($entry===false?$to:HHtml::url($to,$entry)));
		if($exit) exit;
	}
	
	/**
	 * Redirect to the last url using the referer
	 * 
	 * @param string|array the url if the referer was empty or from another site
	 * @param string|null
	 * @param bool
	 * @return void
	 */
	public static function redirectLast($toIfNotFound,$entryIfNotFound=null,$exit=true){
		$to=CHttpRequest::referer(true);
		if($to===CRoute::getAll() || $to===null) $to=$toIfNotFound;
		else $entryIfNotFound=false;
		self::redirect($to,$entryIfNotFound,$exit);
	}
	
	/**
	 * Permanent 301 redirection
	 * 
	 * @param string|array
	 * @param string
	 * @param bool
	 * @param bool
	 * @return void 
	 */
	public static function redirectPermanent($to,$entry=null,$exit=true,$forbiddendForAjax=true){
		self::redirect($to,$entry,$exit,$forbiddendForAjax,true);
	}
	
	/** @deprecated */
	public static function redirectLastIfNotSecured($toIfNotFound,$exit=true){
		throw new Exception('TODO : put ischeckrequired in mdef');
		$to=CHttpRequest::referer(true);
		$entry=null;
		if($to===CRoute::getAll() || $to===null) $to=$toIfNotFound;
		else{
			$route=CRoute::resolveRoute($to);
			$filename=APP.'controllers'.Springbok::$suffix.'/methods/'.$route['controller'].'-'.$route['action'];
			if(!file_exists($filename)) $to=$toIfNotFound;
			else{
				$entry=false;
				$mdef=include $filename;
				
			}
			
		}
		self::redirect($to,$entry,$exit);
	}
	
	
	/* RENDER */
	
	/**
	 * Called before rendering the view
	 * 
	 * You can here add other variables in the view or the layout
	 * 
	 * @return void
	 */
	protected static function beforeRender(){}
	
	/**
	 * Render the view views[.entry]/controller/action.php
	 * 
	 * @param string|null if null : CRoute::getAction()
	 * @param string|null if null : CRoute::getController()
	 * @return void
	 */
	protected static function render($fileName=null,$folderName=null){
		if($fileName===null) $fileName=CRoute::getAction();
		if($folderName===null) $folderName=CRoute::getController();
		$render=self::_render(APP.'views'.self::$suffix.DS.$folderName.DS.$fileName.'.php');
	}
	
	/**
	 * Render any view
	 * 
	 * @param string $file the full path of the view
	 * @return void
	 */
	public static function _render($file){
		include_once CORE.'mvc/views/View.php';
		static::beforeRender();
		/*#if DEV */
		if(!file_exists($file)) throw new Exception(_tC('This view does not exist:').' '.replaceAppAndCoreInFile($file));
		/*#/if*/
		render($file,self::$viewVars);
	}
	
	/**
	 * Render content in the default layout
	 * 
	 * Use this to avoid calling a view with only some stupid echo.
	 * 
	 * @param string
	 * @param string
	 * @param bool
	 * @return void
	 */
	protected static function renderContent($title,$content,$exit=true){
		include_once CORE.'mvc/views/View.php';
		static::beforeRender();
		$v=new AjaxContentView($title);
		echo $content;
		$v->render();
		if($exit===true) exit;
	}
	
	/**
	 * Set the headers for caching
	 * 
	 * @param string '2 weeks', '1 month',...
	 * @see strtotime
	 * @return void
	 */
	public static function cacheFor($time){
		$maxAge=strtotime($time,0);
		header("Pragma: public");
		header("Cache-Control: max-age=".$maxAge);
		header('Expires: '.gmdate('D, d M Y H:i:s', time()+$maxAge).' GMT');
	}

	/**
	 * Set the headers for no caching
	 * 
	 * @return void
	 */
	public static function noCache(){
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Pragma: no-cache");
	}
	
	/**
	 * Echo jsonfied string with the right header application/json
	 * 
	 * @param string
	 * @param bool
	 * @return void
	 */
	public static function renderJSON($content,$exit=true){
		self::noCache();
		header('Content-type: application/json');
		echo $content;
		if($exit) exit;
	}
	
	/**
	 * Echo text string with the right header text/plain
	 * 
	 * @param string
	 * @param bool
	 * @return void
	 */
	protected static function renderText($content,$exit=true){
		header("Content-Type: text/plain");
		echo $content;
		if($exit) exit;
	}
	
	/**
	 * Echo html content
	 * 
	 * @param string
	 * @param bool
	 * @return void
	 */
	protected static function renderHtml($content,$exit=true){
		echo $content;
		if($exit) exit;
	}
	
	/**
	 * Echo Xml string with the right header application/xml
	 * 
	 * @param string
	 * @param bool
	 * @return void
	 */
	protected static function renderXml($content,$exit=true){
		header('Content-type: application/xml; charset=UTF-8');
		echo $content;
		if($exit) exit;
	}
	
	/**
	 * Allow flushing to make sure your content is sent to the browser even if the request is not over
	 */
	protected static function allowFlush(){
		apache_setenv('no-gzip',1); ini_set('zlib.output_compression',0); ini_set('implicit_flush',1);
		for($i = 0; $i < ob_get_level(); $i++){ ob_end_flush(); }
		ob_implicit_flush(1);
	}
	
	/**
	 * Force closing the connection but continue the script
	 * 
	 * This doesn't seems to work
	 * 
	 * @return void
	 */
	protected static function closeConnection(){
		self::allowFlush();
		header('Connection: close');
		ignore_user_abort(true);
		//$size = ob_get_length();
		//header('Content-Length: '.$size);
		CSession::close();
		//ob_end_flush();
		flush();
	}
	
	/**
	 * Push string to the browser
	 * 
	 * @param string
	 * @return void
	 */
	public static function push($string){
		echo str_pad($string,4096);
		flush();
	}
	
	/**
	 * Send text to download
	 * 
	 * @param string
	 * @param string
	 * @param bool
	 * @return void
	 */
	public static function sendText($content,$filename,$exit=true){
		header('Accept-Ranges: none');
		header('Content-Disposition: attachment; filename='.$filename);
		header('Content-Length: '.strlen($content));
		self::renderText($content,$exit);
	}
	
	/**
	 * Echo file content to display
	 * 
	 * @param string
	 * @param bool
	 * @return void
	 */
	public static function renderFile($filepath,$exit=true){
		self::sendFile($filepath,false);
		if($exit) exit;
	}
	
	/**
	 * Send file to download
	 * 
	 * @param string
	 * @param string|false|null
	 * @param bool
	 * @param int if you want to reduce kbps, 0 = unlimited
	 * @return void
	 */
	protected static function sendFile($filepath,$filename=null,$partial=true,$kbps=0){
		if(!is_file($filepath)) notFound();
		if($filename===null) $filename=basename($filepath);
		header('Content-type: '.CMimeType::fromExt($filepath)/*($filename?'application/octet-stream':'')*/);
		header('Accept-Ranges: '.($partial?'bytes':'none'));
		if($filename) header('Content-Disposition: '.($filename?'attachment':'inline').'; filename='.($filename?$filename:basename($filepath)));
		header('Content-Length: '.filesize($filepath));
		
		if(!$kbps) readfile($filepath);
		else{
			$max=ini_get('max_execution_time');
			$ctr=1;
			$handle=fopen($filepath,'r');
			$time=time();
			while (!feof($handle) && !connection_aborted()){
				if ($kbps>0) {
					// Throttle bandwidth
					$ctr++;
					$elapsed=microtime(true)-$time;
					if (($ctr/$kbps)>$elapsed)
						usleep(1e6*($ctr/$kbps-$elapsed));
				}
				// Send 1KiB and reset timer
				echo fread($handle,1024);
				set_time_limit($max);
			}
			fclose($handle);
		}
		return true;
	}
}