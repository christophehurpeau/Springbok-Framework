<?php
class HttpException extends Exception{
	private $httpcode,$description;
	
	public function __construct($httpcode,$message='',$description='',$previous=null){
		parent::__construct($message,$httpcode,$previous);
		$this->httpcode=$httpcode;
		$this->description=$description;
	}
	
	public function getHttpCode(){
		return $this->httpcode;
	}
	
	public function hasDescription(){
		return $this->description!=='';
	}
	
	public function getDescription(){
		return $this->description;
	}
}

class FatalHttpException extends HttpException{
	
}

function httpCreated(){throw new HttpException(201,'Created');}
function httpAccepted(){throw new HttpException(202,'Accepted');}
function httpNoContent(){throw new HttpException(204,'No Content',false);}


function badRequest(){throw new HttpException(400,'Bad Request');}
function unauthorized(){throw new HttpException(401,'Unauthorized');}
function forbidden(){throw new HttpException(403,'Forbidden');}
function notFound(){throw new HttpException(404,'Not Found',_tC('The page you requested was not found'));}
function methodNotAllowed(){throw new HttpException(405,'Method Not Allowed');}
function notAcceptable(){throw new HttpException(406,'Not Acceptable');}
function proxyAuthenticationRequired(){throw new HttpException(407,'Proxy Authentication Required');}
function requestTimeout(){throw new HttpException(408,'Request Timeout');}
function internalServerError(){throw new FatalHttpException(500,'Internal Server Error');}
function notImplemented(){throw new HttpException(501,'Not Implemented');}
function serviceUnavailable($details=''){throw new FatalHttpException(503,'Service Unavailable',$details);}