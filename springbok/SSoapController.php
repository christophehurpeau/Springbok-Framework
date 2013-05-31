<?php
class SSoapController extends Controller{
	protected static $uri;
	
	protected static function _wsdl($fileName=null,$folderName=null){
		if($fileName===null) $fileName=CRoute::getAction();
		if($folderName===null) $folderName=CRoute::getController();
		
		$wsdlfilename=APP.'views'.self::$suffix.DS.$folderName.DS.$fileName.'.xml';
		/*#if PROD*/ if(!file_exists($wsdlfilename)): /*#/if*/
		$name=substr(get_called_class(),0,-10);
		self::$uri=HHTml::url('/'.lcfirst($name),true,false);
		
		$wsdl=new CWsdl($name,static::$uri);
		$wsdl->addSchemaTypeSection();
		
		$port=$wsdl->addPortType($name.'Port');
        $binding=$wsdl->addBinding($name.'Binding','tns:'.$name.'Port');
		$wsdl->addSoapBinding($binding,'rpc','http://schemas.xmlsoap.org/soap/http');
		$wsdl->addService($name.'Service', $name.'Port','tns:'.$name.'Binding',static::$uri);
		
		foreach(array_diff(get_class_methods(get_called_class()),get_class_methods('Controller'),get_class_methods('SSoapController')) as $method){
			$filename=APP.'controllers/methods/'.$name.'-'.$method;
			if(!file_exists($filename)) continue;
			$infos=include $filename;
			if(!isset($infos['annotations']['Return'])) continue;
			$wsdl->_addFunctionToWsdl($method,$port,$binding,$infos);
		}
		debugVar($wsdlfilename);
		$wsdl->dump($wsdlfilename);
		/*#if PROD*/endif;/*#/if*/
		
		header('Content-type: text/xml');
		echo file_get_contents(APP.'views'.self::$suffix.DS.$folderName.DS.$fileName.'.xml');
	}

	
	
	protected static function _index(){
		
	}
}
