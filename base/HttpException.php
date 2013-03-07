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
		return parent::__construct(500,'Internal Server Error',_tC('http.500'));
	}
}

function httpCreated(){throw new HttpException(201,'Created','Created');}
function httpAccepted(){throw new HttpException(202,'Accepted','Accepted');}
function httpNoContent(){throw new HttpException(204,'No Content',false);}


function badRequest(){throw new HttpException(400,'Bad Request','Bad Request');}
function unauthorized(){throw new HttpException(401,'Unauthorized','Unauthorized');}
function forbidden(){throw new HttpException(403,'Forbidden',_tC('http.403'),_tC('http.403.description'));}
function notFound(){throw new HttpException(404,'Not Found',_tC('http.404'),_tC('http.404.description'));}
function methodNotAllowed(){throw new HttpException(405,'Method Not Allowed','Method Not Allowed');}
function notAcceptable(){throw new HttpException(406,'Not Acceptable','Not Acceptable');}
function proxyAuthenticationRequired(){throw new HttpException(407,'Proxy Authentication Required');}
function requestTimeout(){throw new HttpException(408,'Request Timeout','Request Timeout');}
function pageGone(){throw new HttpException(410,'Gone',_tC('http.410'),_tC('http.410.description'));}
function internalServerError(){throw new InternalServerError();}
function notImplemented(){throw new HttpException(501,'Not Implemented','Not Implemented');}
function serviceUnavailable($details=''){throw new FatalHttpException(503,'Service Unavailable','Service Unavailable',$details);}