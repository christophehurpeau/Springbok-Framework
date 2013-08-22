<?php
include CLIBS.'php-wsdl/class.phpwsdl.php';
//include CLIBS.'php-wsdl/class.phpwsdlhash.php';
/*#if DEV */
PhpWsdl::$Debugging=true;
PhpWsdl::$DebugFile=DATA.'logs/php-wsdl.log';
PhpWsdl::$DebugBackTrace=true;
/*#/if*/
PhpWsdl::$CacheFolder=DATA.'soap/';
class SControllerSoap extends Controller{
	protected static $resp,$soapMethods,$namespace;
	
	public static function dispatch($suffix,$mdef){
		self::$suffix=$suffix;
		static::beforeDispatch();
		$paramsFile=DATA.'soap/params'.$suffix.'.'.($className=substr(get_called_class(),0,-10)).'.php';
		/*#if PROD*/ if(!file_exists($paramsFile)): /*#/if*/
		$params=array('methods'=>static::soapMethods($className),'complexTypes'=>static::complexTypes());
		file_put_contents($paramsFile,serialize($params));
		ini_set('soap.wsdl_cache_enabled',0);
		/*#if PROD*/endif;/*#/if*/
		
		$params=unserialize(file_get_contents($paramsFile));
		
		$soap=PhpWsdl::CreateInstance(
			static::$namespace !== null ? static::$namespace : HHtml::url('/'.CRoute::getController(),null,true).'/',	// PhpWsdl will determine a good namespace
			HHtml::url('/'.CRoute::getController(),null,true),	// Change this to your SOAP endpoint URI (or keep it NULL and PhpWsdl will determine it)
			DATA.'soap/',							// Change this to a folder with write access
			null,								// PhpWsdl should not parse PHP comments for this demonstration
			$className.'Controller',			// The name of the class that serves the webservice
			$params['methods'],
			$params['complexTypes'],
			false,								// Don't send WSDL right now
			false);								// Don't start the SOAP server right now
		
		/*#if DEV */
		ini_set('soap.wsdl_cache_enabled',0);	// Disable caching in PHP
		PhpWsdl::$CacheTime=0;					// Disable caching in PhpWsdl
		/*#/if*/
		
		static::beforeRunServer($soap);
		
		try{
			$soap->RunServer();
		}catch(Exception $exception){
			/* http://dcx.sybase.com/1200/en/dbprogramming/errors-http.html */
			//Client 	The message was incorrectly formed or contained incorrect information
			//Server 	There was a problem with the server so the message could not proceed
			$log=get_class($exception).' ['.$exception->getCode().']'.' : '.$exception->getMessage().' ('.$exception->getFile().':'.$exception->getLine().")\n";
			if(isset($_SERVER['REQUEST_URI'])) $log.=' REQUEST_URI='.$_SERVER['REQUEST_URI'];
			if(!empty($_POST)) $log.="\nPOST=".print_r($_POST,true);
			$log.="\nCall Stack:\n".$exception->getTraceAsString();
			CLogger::get('ws-exception')->log($log);
			if($soap->SoapServer!==null) $soap->SoapServer->fault('Server','Internal Server Error','SControllerSoap');
		}
	}
	protected static function clientFault($description,$actor=null,$details=null,$faultName=null,$headerFault=null){
		return new SoapFault('Client',$description,$actor,$details,$faultName,$headerFault);
	}

	protected static function beforeRunServer($soap){}

	public static function soapMethods($className){
		$methods=array();
		if(empty(static::$soapMethods)) static::$soapMethods=array_diff(get_class_methods(get_called_class()),get_class_methods('Controller'),get_class_methods('SControllerSoap'));
		foreach(static::$soapMethods as $method){
			if($method[0]==='_') continue;
			$filename=APP.'controllers'.self::$suffix.'/methods/'.$className.'-'.$method;//debugVar($method,$filename);
			if(!file_exists($filename)) continue;
			$infos=include $filename;
			if(!isset($infos['annotations']['Return'])) continue;
			
			$return=$infos['annotations']['Return'][0]; $settings=array(); $params=array();
			
			if(isset($infos['annotations']['Doc'])) $settings['docs']=$infos['annotations']['Doc'][0];
			
			if($infos['params']!==false)
				foreach($infos['params'] as $name=>$param){
					$params[]=CSoapWsdl::param($name,$param['type']);
				}
			$methods[]=new PhpWsdlMethod($method,$params,$return==='void'?null:CSoapWsdl::param('return',$return),$settings);
		}
		return $methods;
	}
	
	protected static function complexTypes(){
		static::createComplexTypes($wdsl=new CSoapWsdl());
		return $wdsl->getTypes();
	}
}