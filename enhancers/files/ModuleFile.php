<?php
class ModuleFile extends PhpFile{
	public static $CACHE_PATH='controllers_8.0';
	private $_className;
	
	protected function enhancePhpContent($phpContent,$false=false){
		self::$_changes=true;
		$matches=array();
		preg_match('/class ([A-Za-z_]+)/',$phpContent,$matches);//debug($matches);
		if(empty($matches[1])) return parent::enhancePhpContent($phpContent);
		$this->_className=$matches[1];
		//$content=preg_replace_callback('/(?:\/\*\*(.*)\*\/)?[\s]+public[\s]+function[\s]+([a-zA-Z0-9_ \$]+)[\s]*\((.*)\)[\s]*{([^{]*(?:{[^{]*(?:{[^{]*(?:{[^{]*(?:{[^{]*(?:{[^{]*(?:{[^{]*(?:{[^{]*(?:{[^{]*(?:{[^{]*(?:{[^{]*(?:{[^{]*(?:{[^{]*(?:{[^{]*(?:{[^{]*(?:{[^{]*(?:{.*})*[^{]*})*[^{]*})*[^{]*})*[^{]*})*[^{]*})*[^{]*})*[^{]*})*[^{]*})*[^{]*})*[^{]*})*[^{]*})*[^{]*})*[^{]*})*[^{]*})*[^{]*})*[^{]*)}/Ums',array($this,'enhanceMethodParams'),$content);
		
		foreach(self::$_modulesConfig as $methodName=>&$classNames)
			if($key=array_search($this->_className,$classNames)) unset($classNames[$key]);
		
		$phpContent=preg_replace_callback('/(?:\/\*\*([^{]*)\*\/)?[\s]+public\s+static\s+function\s+([a-zA-Z0-9_ \$]+)\s*\((.*)\)\s*{\s*(.*)\s*\n\t}\n/Ums',
				array($this,'enhanceMethodParams'),$phpContent);
		
		parent::enhancePhpContent($phpContent);
	}
	
	private function enhanceMethodParams($matches){
			
		self::$_modulesConfig[$matches[2]][]=$this->_className;
		// 1:annotations, 2:name, 3:args, 4:content
		return 'public static function '.$matches[2].'('.$matches[3].'){'.PHP_EOL.$matches[4].PHP_EOL.'}';
	}
	
	
	private static $_modulesConfig=array(),$_changes=false;
	public static function initFolder($folder,$config){
		$f=new File($folder->getPath().'config'.DS.'modules.php');
		if($f->exists()){
			self::$_modulesConfig=include $f->getPath();
		}else self::$_modulesConfig=array();
	}
	public static function fileDeleted($file){
		self::$_changes=true;
		$moduleName=substr($file->getName(),0,-4);
		foreach(self::$_modulesConfig as $methodName=>&$modules)
			unset($modules[$moduleName]);
	}
	
	public static function afterEnhanceApp(&$enhanced,&$dev,&$prod){
		if(self::$_changes){
			foreach(self::$_modulesConfig as $methodName=>&$modules)
				self::$_modulesConfig[$methodName]=array_unique($modules);
			$content='<?php return '.UPhp::exportCode(self::$_modulesConfig).';';
			file_put_contents($dev->getPath().'config/modules.php',$content);
			file_put_contents($prod->getPath().'config/modules.php',$content);
		}
	}
}
