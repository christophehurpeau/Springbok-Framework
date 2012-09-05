function HttpException(code,error,details){
	this.code=code;
	this.error=error;
	this.details=details;
}
HttpException.prototype={
	getCode:function(){return this.code;},
	getError:function(){return this.error;},
	getDetails:function(){return this.details;}
};


function FatalHttpException(){
	
}

function badRequest(){throw new HttpException(400,'Bad Request');}
function unauthorized(){throw new HttpException(401,'Unauthorized');}
function forbidden(){throw new HttpException(403,'Forbidden',i18nc['http.403']);}
function notFound(){throw new HttpException(404,'Not Found',i18nc['http.404']);}
function methodNotAllowed(){throw new HttpException(405,'Method Not Allowed');}
function notAccepable(){throw new HttpException(406,'Not Acceptable');}
function proxyAuthenticationRequired(){throw new HttpException(407,'Proxy Authentication Required');}
function requestTimeout(){throw new HttpException(408,'Request Timeout');}
function internalServerError(){throw new FatalHttpException(500,i18nc['http.500']);}
function notImplemented(){throw new HttpException(501,'Not Implemented');}
function serviceUnavailable(details){throw new FatalHttpException(503,'Service Unavailable',details);}

