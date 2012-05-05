<?php
/* Representational state transfer */
class SControllerREST extends Controller{
	public static function dispatch(&$suffix,&$mdef){
		self::$suffix=&$suffix;
		static::beforeDispatch();
		$method=CHttpRequest::getMethod(); $methodName=CRoute::getAction();
		if($method !=='GET') $methodName.=$method;
		
		if(!method_exists(get_called_class(),$methodName)) notFound();
		self::$methodName=&$methodName;
		$methodAnnotations=&$mdef['annotations'];
		return call_user_func_array(array('static',$methodName),$mdef['params']===false?array():self::getParams($mdef,$methodAnnotations));
	}
	
	
	
	protected static function renderModels($models){
		self::render(SModel::mToArray($models));
	}
	protected static function renderModel($model){
		self::render($model===false?false:$model->toArray());
	}
	
	protected static function render($content=null,$exit=true){
		/*self::noCache();*/
		$ext=CRoute::getExt();
		$allowedSource=array('json','xml','php','phpsource','html');
		if($ext && in_array($ext,$allowedSource)) $source=$ext;
		else $source=CHttpRequest::accepts($allowedSource);
		switch($source){
			case 'xml':
				header('Content-type: application/xml; charset=UTF-8');
				echo xmlrpc_encode($content);
				break;
			case 'php':
				header('Content-type: text/plain; charset=UTF-8');
				echo serialize($content);
				break;
			case 'phpsource':
				header('Content-type: text/plain; charset=UTF-8');
				echo UPhp::exportCode($content);
				break;
			case 'html':
				header('Content-type: text/html; charset=UTF-8');
				echo '<pre>'.print_r($content,true).'</pre>';
				break;
			default:
				header('Content-type: application/json; charset=UTF-8');
				echo json_encode($content);
		}
		if($exit) exit;
	}
	
	protected static function renderText($content,$exit=true){
		self::noCache();
		header("Content-Type: text/plain");
		echo $content;
		if($exit) exit;
	}
}
