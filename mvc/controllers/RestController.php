<?php
/*throw new Exception('does not work anymore');
class RestController{
	protected static function beforeDispatch(){}
	
	public static function dispatch(&$prefix,&$mdef){
		self::$prefix=&$prefix;
		static::beforeDispatch();
		$method=CHttpRequest::getMethod();
		
		if(!method_exists(get_called_class(),$methodName=(CRoute::getAction().$method))) notFound();
		self::$methodName=$methodName;
		$methodAnnotations=isset(static::$_methodAnnotations[$methodName]) ? static::$_methodAnnotations[$methodName] : array();
		if(isset(static::$_classAnnotations))
			$methodAnnotations += static::$_classAnnotations;
		if(isset($methodAnnotations['Check'])){
			if(isset($methodAnnotations['Check'][0]) && is_string($methodAnnotations['Check'][0])){
				$className=array_shift($methodAnnotations['Check']);
				$className::checkAccess($methodAnnotations['Check']);
			}else
				ACSecure::checkAccess($methodAnnotations['Check']);
		}
		$rParams=CRoute::getParams();
		$params=array(); $num=0;
		
		if($method==='GET') $DATA=&$_GET;
		elseif($method==='POST') $DATA=&$_POST;
		elseif($method==='PUT'){ $DATA=NULL; parse_str(file_get_contents('php://input'),$DATA); }
		//json_decode ?
		foreach($mdef as $paramName=>$def){
			if($rParams && isset($rParams[$paramName])) $val=$rParams[$paramName];
			elseif($rParams && isset($rParams[$num])) $val=$rParams[$num];
			elseif(isset($DATA[$paramName])) $val=$DATA[$paramName];
			else $val=NULL;
			if($val !== NULL) $val=CBinder::bind($def['type'],$val,isset($def['annotations'])?$def['annotations']:false,$paramName);
			elseif(isset($def['annotations']['Required']) || isset($def['annotations']['Valid'])) CValidation::required($paramName,false);
			$params[]=$val;
			$num++;
		}
		if(isset($methodAnnotations['ValidParams']) && CValidation::hasErrors()) notFound();
		call_user_func_array(array('static', $methodName), $params);
	}

	protected static function renderModels($models){
		self::render(Model::mToArray($models));
	}
	protected static function renderModel($model){
		self::render($model===false?false:$model->toArray());
	}

	protected static function render($content,$exit=true){
		self::noCache();
		switch(CHttpRequest::accepts(array('json','xml','php','phpsource','html'))){
			case 'xml':
				header('Content-type: application/xml');
				echo xmlrpc_encode($content);
				break;
			case 'php':
				echo serialize($content);
				break;
			case 'phpsource':
				echo UPhp::exportCode($content);
				break;
			case 'html':
				echo '<pre>'.print_r($content,true).'</pre>';
				break;
			default:
				header('Content-type: application/json');
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
*/