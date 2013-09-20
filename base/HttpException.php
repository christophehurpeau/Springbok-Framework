<?php
class HttpException extends SDetailedException{
	private $httpcode;
	
	public function __construct($httpcode,$status,$title,$description='',$previous=null){
		parent::__construct($status,$httpcode,$title,$description,$previous);
		$this->httpcode=$httpcode;
	}
	
	public function getHttpCode(){
		return $this->httpcode;
	}
	
	public function hasDescription(){
		return parent::hasDetails();
	}
	
	public function getDescription(){
		return parent::getDetails();
	}
}

class FatalHttpException extends HttpException{
	
}
class InternalServerError extends FatalHttpException{
	public function __construct(){
		if(!empty($_SERVER['HTTP_USER_AGENT']) && stripos($_SERVER['HTTP_USER_AGENT'],'bot')===false)
			parent::__construct(500,'Internal Server Error',_tC('http.500'));
		else
			parent::__construct(503,'Service Unavailable',_tC('http.500')); //keep error message 500
	}
}

/** 201 Created */
function httpCreated(){throw new HttpException(201,'Created','Created');}
/** 202 Accepted */
function httpAccepted(){throw new HttpException(202,'Accepted','Accepted');}
/** 204 No Content */
function httpNoContent(){throw new HttpException(204,'No Content',false);}


/** 400 Bad Request */
function badRequest(){throw new HttpException(400,'Bad Request','Bad Request');}
/** 401 Unauthorized */
function unauthorized(){throw new HttpException(401,'Unauthorized','Unauthorized');}
/** 403 Forbidden */
function forbidden(){throw new HttpException(403,'Forbidden',_tC('http.403'),_tC('http.403.description'));}
/** 404 Not Found */
function notFound(){throw new HttpException(404,'Not Found',_tC('http.404'),_tC('http.404.description'));}
/** 405 Method Not Allowed */
function methodNotAllowed(){throw new HttpException(405,'Method Not Allowed','Method Not Allowed');}
/** 406 Not Acceptable */
function notAcceptable(){throw new HttpException(406,'Not Acceptable','Not Acceptable');}
/** 407 Proxy Authentication Required */
function proxyAuthenticationRequired(){throw new HttpException(407,'Proxy Authentication Required');}
/** 408 Request Timeout */
function requestTimeout(){throw new HttpException(408,'Request Timeout','Request Timeout');}
/** 410 Gone */
function pageGone(){throw new HttpException(410,'Gone',_tC('http.410'),_tC('http.410.description'));}
/** 500 Internal Server Error */
function internalServerError(){throw new InternalServerError();}
/** 501 Not Implemented */
function notImplemented(){throw new HttpException(501,'Not Implemented','Not Implemented');}
/** 503 Service Unavailable */
function serviceUnavailable($details=''){throw new FatalHttpException(503,'Service Unavailable','Service Unavailable',$details);}