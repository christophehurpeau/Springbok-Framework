<?php
/** Representational state transfer */
class SControllerREST extends Controller{
	/**
	 * @internal
	 */
	public static function dispatch($suffix,$mdef){
		self::$suffix=$suffix;
		static::beforeDispatch();
		$method=CHttpRequest::getMethod(); $methodName=CRoute::getAction();
		if($method !=='GET'){ $methodName.=$method; $mdef.=$method; }
		
		if(!method_exists(get_called_class(),$methodName)) notFound();
		$mdef=include $mdef;
		$methodAnnotations=$mdef['annotations'];
		static::crossDomainHeaders();
		return call_user_func_array(array('static',$methodName),self::getParams($mdef,$methodAnnotations));
	}
	
	/**
	 * Override this to specify cross-domain headers
	 * @return void
	 */
	public static function crossDomainHeaders(){}
	
	
	/**
	 * Render models
	 * 
	 * @param array
	 * @return void
	 */
	protected static function renderModels($models){
		// PHP 5.3 : self::render(SModel::mToArray($models));
		self::render($models);
	}
	
	/**
	 * Render model
	 * 
	 * @param SModel
	 * @return void
	 */
	protected static function renderModel($model){
		// PHP 5.3 : self::render($model===false?false:$model->toArray());
		self::render($models);
	}
	
	/**
	 * Render a content
	 * 
	 * @param string|null|SModel|mixed
	 * @param bool
	 * @return void
	 */
	protected static function render($content=null,$exit=true){
		/*self::noCache();*/
		switch(CHttpRequest::acceptsByExtOrHttpAccept('json','xml','php','phpsource','html')){
			case 'xml':
				displayXml($content);
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
				displayJson($content);
		}
		if($exit) exit;
	}
	
	/**
	 * Render plain text
	 * 
	 * @param string
	 * @param bool
	 * @return void
	 */
	protected static function renderText($content,$exit=true){
		self::noCache();
		header("Content-Type: text/plain");
		echo $content;
		if($exit) exit;
	}
}
