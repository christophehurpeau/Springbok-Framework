var HttpException=function(core,error,details){
	this.code=code;
	this.error=error;
	this.details=details;
}
HttpException.prototype.getCode=function(){return this.code;}
HttpException.prototype.getError=function(){return this.error;}
HttpException.prototype.getDetails=function(){return this.details;}


function badRequest(){throw new HttpException(400,'Bad Request');}
function unauthorized(){throw new HttpException(401,'Unauthorized');}
function forbidden(){throw new HttpException(403,'Forbidden');}
function notFound(){throw new HttpException(404,'Not Found',_tC('The page you requested was not found.'));}
function methodNotAllowed(){throw new HttpException(405,'Method Not Allowed');}
function notAccepable(){throw new HttpException(406,'Not Acceptable');}
function proxyAuthenticationRequired(){throw new HttpException(407,'Proxy Authentication Required');}
function requestTimeout(){throw new HttpException(408,'Request Timeout');}
function internalServerError(){throw new FatalHttpException(500,'Internal Server Error');}
function notImplemented(){throw new HttpException(501,'Not Implemented');}
function serviceUnavailable(details){throw new FatalHttpException(503,'Service Unavailable',details);}