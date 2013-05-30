<?php
class_exists('CSocket');
class BasicAppSocketUser extends BasicSocketUser{
	private $connected;
	public function isConnected(){
		return $this->connected!==null;
	}
	public function setConnected($connected){
		$this->connected=$connected;
	}
}

include CORE.'springbok/Controller.php';
class SocketController extends Controller{
	public static $DATA;
	protected static function getParams($mdef,$methodAnnotations){
		$params=array();
		
		$rParams=CRoute::getParams();
		$num=0;
		$mdef=include $mdef;
		foreach($mdef['params'] as $paramName=>$def){
			if($rParams && isset($rParams[$paramName])) $val=$rParams[$paramName];
			elseif($rParams && isset($rParams[$num])) $val=$rParams[$num];
			else $val=NULL;
			if($val !== NULL) $val=CBinder::bind($def['type'],$val,isset($def['annotations'])?$def['annotations']:false,$paramName);
			elseif(isset($def['annotations']['Required']) || (isset($def['annotations']['Valid']) && $def['annotations']['Valid']===false)) CValidation::required($paramName,false);
			$params[]=$val;
			$num++;
		}
		return $params;
	}
	
	protected static function checkAccess($checkAnnotation){
		CSocketSecure::checkAccess($checkAnnotation);
	}
}

class CSocketSecure extends CSecure{
	protected static $user;
	
	public static function setUser(&$user){self::$user=&$user;}
	
	protected static function loadCookie(){}
	
	public static function isConnected(){
		return self::$user->isConnected();
	}
	
	public static function connected(){
		return self::$user->connected();
	}
	
	public static function checkAccess($params){
		if(!static::isConnected()) forbidden();
		if($params){
			$user=static::user();
			if(!$user->isAllowed($params[0])) forbidden();
		}
	}
	public static function connect($redirect=true){
		throw new BadFunctionCallException();
	}
	
	protected static function authSuccess(&$id,&$connected,&$redirect){
		self::$user->setConnected($connected);
	}
	
	protected static function authFailed(){
	}
}

class CSocketApp{
	private $socket,$prefix;
	
	public function __construct($socket){
		$this->socket=$socket;
	}
	
	public function run($name){
		$this->prefix=$name.DS;
		CRoute::cliinit(/*#if DEV */''/*#/if*/);
		
	}
	
	public function action($user,$route,$params){
		CRoute::initRoute($route);
		
		try{
			// do some optimization with cache + langs
			$filename=APP.'controllers/'.$this->prefix.'methods/'.CRoute::getController().'-'.CRoute::getAction();
			if(!file_exists($filename))
				/*#if DEV */ throw new Exception('This route does not exists : '.$this->prefix.CRoute::getController().'::'.CRoute::getAction()); /*#/if*/
				/*#if PROD*/ notFound(); /*#/if*/
			$mdef=include $filename;
			
			$controllerName=CRoute::getController().'Controller';
			if(!class_exists($controllerName,false)){
				if(!file_exists($filename=APP.'controllers/'.$this->prefix.$controllerName.'.php')) notFound();
				include $filename;
				
				if(!class_exists($controllerName,false)) notFound();
			}
			CSocketSecure::setUser($user);
			SocketController::$DATA=$params;
			return $controllerName::dispatch($this->prefix,$mdef);
		}catch(Exception $exception){
			if(!($exception instanceof HttpException)){
				if($exception instanceof DBException) $e=new FatalHttpException(503,'Service Temporarily Unavailable');
				elseif($exception instanceof mysqli_sql_exception){
					if($exception->getCode()===1040) $e=$exception=new FatalHttpException(503,'Service Temporarily Unavailable',_tC('The server is currently overloaded'));
					else $e=new FatalHttpException(503,'Service Temporarily Unavailable');
				}else $e=new InternalServerError(500);
			}else $e=$exception;
			
			Springbok::handleException($exception);
			
			return json_encode(array('error'=>array('code'=>$e->getHttpCode(),'message'=>$e->getMessage())));
		}
	}
}
