<?php
class Controller{
	protected static $methodName,$suffix;
	public static $defaultLayout;//init in "app.php"

	private static $viewVars=array(),$layoutVars=array();

	protected static function beforeDispatch(){}

	public static function dispatch(&$suffix,&$mdef){
		self::$suffix=&$suffix;
		static::beforeDispatch();
		if(!method_exists(get_called_class(),$methodName=CRoute::getAction())) notFound();
		self::$methodName=&$methodName;
		$methodAnnotations=&$mdef['annotations'];
		//if(isset(static::$_classAnnotations))
		//	$methodAnnotations += static::$_classAnnotations;
		/* 
		if(isset($methodAnnotations['Ajax'])){
			if(!CHttpRequest::isAjax()) notFound();
		} */
		
		return call_user_func_array(array('static',$methodName),$mdef['params']===false?array():self::getParams($mdef,$methodAnnotations));
	}
	
	protected static function checkAccess($checkAnnotation){
		ACSecure::checkAccess($checkAnnotation);
	}

	protected static function &getParams(&$mdef,&$methodAnnotations){
		/* DONT FORGET TO CHANGE RESTCONTROLLER AND SOCKETCONTROLLER */
		$params=array();
		
		$method=CHttpRequest::getMethod();
		if($method==='GET') $DATA=&$_GET;
		elseif($method==='POST') $DATA=&$_POST;
		
		$rParams=CRoute::getParams();
		$num=0;
		foreach($mdef['params'] as $paramName=>$def){
			if($rParams && isset($rParams[$paramName])) $val=$rParams[$paramName];
			elseif($rParams && isset($rParams[$num])) $val=$rParams[$num];
			elseif(isset($DATA[$paramName])) $val=$DATA[$paramName];
			else $val=NULL;
			if($val !== NULL) $val=CBinder::bind($def['type'],$val,isset($def['annotations'])?$def['annotations']:false,$paramName);
			elseif(isset($def['annotations']['Required']) || (isset($def['annotations']['Valid']) && $def['annotations']['Valid']===false)) CValidation::required($paramName,false);
			$params[]=$val;
			$num++;
		}
		if(isset($methodAnnotations['ValidParams']) && CValidation::hasErrors()){
			if($methodAnnotations['ValidParams']===false) /* PROD */notFound();/* /PROD */
			/* HIDE */elseif(true)/* /HIDE *//* DEV */throw new Exception('Not valid params : '.print_r(CValidation::errors(),true));/* /DEV */
			else self::redirect($methodAnnotations['ValidParams'][0]);
		}
		
		return $params;
	}


	/* GETTERS & SETTERS */

	protected static function set($name,$value=null){
		/* DEV */
		if(is_array($name))
			throw new Exception('Controller::set array => use mset');
		/* /DEV */
		self::$viewVars[$name]=&$value;
	}
	protected static function set_($name,&$value){
		self::$viewVars[$name]=&$value;
	}
	protected static function mset($array){
		self::$viewVars=$array+self::$viewVars;
	}
	
	public static function setForView($name,$value){
		self::$viewVars[$name]=&$value;
	}
	
	public static function setForView_($name,&$value){
		self::$viewVars[$name]=&$value;
	}
	
	public static function setForLayout($name,$value=null){
		/* DEV */
		if(is_array($name))
			throw new Exception('Controller::setForLayout array => use msetForLayout');
		/* /DEV */
		if(is_array($name)) self::$layoutVars=$name+self::$layoutVars;
		else self::$layoutVars[$name]=&$value;
	}
	public static function setForLayout_($name,&$value){
		self::$layoutVars[$name]=&$value;
	}
	
	public static function setForLayoutAndView($name,$value){
		if(is_array($name)){
			self::$viewVars=$name+self::$viewVars;
			self::$layoutVars=$name+self::$layoutVars;
		}else self::$layoutVars[$name]=self::$viewVars[$name]=&$value;
	}
	public static function setForLayoutAndView_($name,&$value){
		self::$layoutVars[$name]=&$value;
		self::$viewVars[$name]=&$value;
	}
	
	public static function _isset($name){
		return isset(self::$viewVars[$name]);
	}
	
	public static function &get($name){
		return self::$viewVars[$name];
	}
	
	public static function &getLayoutVars(){
		return self::$layoutVars;
	}
	public static function &getLayoutVar($name){
		return self::$layoutVars[$name];
	}
	
	/* */
	
	/**
	 * name : Post name
	 * callback : function($name,$tmp_name,$size,$type)
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
	
	public static function moveUploadedFile($name,$to){
		if(!empty($_FILES[$name]) && $_FILES[$name]['error'] == UPLOAD_ERR_OK){
			move_uploaded_file($_FILES[$name]['tmp_name'],$to);
			return true;
		}
		return false;
	}
	
	public static function header404(){
		header('HTTP/1.1 404 Not Found');
		header('Status: 404 Not Found',false,404);
	}

	public static function redirect($to,$permanent=false,$exit=true,$forbiddendForAjax=true){
		if($permanent){
			header('HTTP/1.1 301 Moved Permanently');
			header('Status: 301 Moved Permanently',false,301);
		}elseif(CHttpRequest::isAjax()){
			/*if(isset($_GET['ajax']))
				self::renderHtml(HHtml::jsInline('S.ajax._load(\'container\','.json_encode(HHtml::url($to)).')'));
			else*/if(isset($_SERVER['HTTP_SPRINGBOKAJAXPAGE'])){
				header('SpringbokRedirect: '.HHtml::url($to));
				if($exit) exit;
				return;
			}
			elseif($forbiddendForAjax) forbidden();
		}
		header('Location: '.HHtml::url($to));
		if($exit) exit;
	}
	
	public static function redirectLast($toIfNotFound,$exit=true){
		$to=CHttpRequest::referer(true);
		if($to===CRoute::getAll()) $to=&$toIfNotFound;
		elseif($to===null) $to=&$toIfNotFound;
		self::redirect($to,false,$exit);
	}
	
	/* RENDER */

	protected static function beforeRender(){return true;}

	protected static function render($fileName=null,$folderName=null){
		if($fileName===null) $fileName=self::$methodName;
		if($folderName===null) $folderName=CRoute::getController();
		$render=self::_render(APP.'views'.self::$suffix.DS.$folderName.DS.$fileName.'.php');
	}

	protected static function _render($file){
		include_once CORE.'mvc/views/View.php';
		if(static::beforeRender()){
			/* DEV */
			if(!file_exists($file)) throw new Exception(_tC('This view does not exist:').' '.replaceAppAndCoreInFile($file));
			/* /DEV */
			render($file,self::$viewVars);
		}
	}
	
	protected static function renderTable($title,&$table,$add=false,$layout=null){
		include_once CORE.'mvc/views/View.php';
		if(static::beforeRender()){
			$v=new AjaxContentView($title,$layout);
			self::_add($add);
			HTable::table($table);
			$v->render();
		}
	}
	
	protected static function renderEditableTable($title,&$table,$pkField,$url,$add=false,$layout=null){
		include_once CORE.'mvc/views/View.php';
		if(static::beforeRender()){
			$v=new AjaxContentView($title,$layout);
			self::_add($add);
			HTableEditable::table($table,$pkField,$url);
			$v->render();
		}
		
	}
	
	private static function _add($add){
		if($add!==false){
			if(is_string($add)) $add=array('modelName'=>$add);
			if(!isset($add['form']['action'])) $add['form']['action']='/'.lcfirst($add['modelName']::$__pluralized).'/add';
			if(!isset($add['formContainer'])) $add['formContainer']=false;
			if(!isset($add['fields'])) $add['fields']=array($add['modelName']::$__displayField=>_tF($add['modelName'],'New').' :');
			$form=HForm::create($add['modelName'],$add['form'],$add['formContainer']);
			foreach($add['fields'] as $field=>$label)
				echo $form->input($field,array('label'=>$label));
			echo $form->end(_tC('Add'));
		}
	}
	
	public static function cacheFor($time){
		$maxAge=strtotime($time,0);
		header("Pragma: public");
		header("Cache-Control: max-age=".$maxAge);
		header('Expires: '.gmdate('D, d M Y H:i:s', time()+$maxAge).' GMT');
	}
	public static function noCache(){
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Pragma: no-cache");
	}
	public static function renderJSON($content,$exit=true){
		self::noCache();
		header('Content-type: application/json');
		echo $content;
		if($exit) exit;
	}
	protected static function renderText($content,$exit=true){
		header("Content-Type: text/plain");
		echo $content;
		if($exit) exit;
	}
	
	protected static function renderHtml($content,$exit=true){
		echo $content;
		if($exit) exit;
	}
	
	protected static function allowFlush(){
		apache_setenv('no-gzip',1); ini_set('zlib.output_compression',0); ini_set('implicit_flush',1);
		for($i = 0; $i < ob_get_level(); $i++){ ob_end_flush(); }
		ob_implicit_flush(1);
	}
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
	
	protected static function push($string){
		echo str_pad($string,4096);
		flush();
	}
	
	public static function sendText($content,$filename,$exit=true){
		header('Accept-Ranges: none');
		header('Content-Disposition: attachment; filename='.$filename);
		header('Content-Length: '.strlen($content));
		self::renderText($content,$exit);
	}
	
	public static function renderFile($filepath,$exit=true){
		self::sendFile($filepath,false);
		if($exit) exit;
	}
	
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