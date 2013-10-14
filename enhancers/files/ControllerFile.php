<?php
class ControllerFile extends PhpFile{
	public static $CACHE_PATH=false;//'controllers_8.0';
	
	const REGEXP_ACTION='/(?:\/\*\*([^{]*)\*\/)\s+(?:static\s+)?function\s+([a-zA-Z0-9_ \$]+)\s*\((.*)\)\s*{\s*(.*)\s*\n(?:\t|\040{2}|\040{4})}\n/Ums';
	
	private $_className,$_annotations=array(),$_classAnnotations;
	private $_methodDefFiles=array();//,$_methodAnnotations=array();
	
	protected function findTraitPath($traitName){ return CORE.'controllers/'.$traitName.'.php'; }
	
	protected function loadContent($srcContent){
		$controllersSrc=array(); $enhanced=$this->enhanced;
		if($this->fileName()==='SiteController.php'){
			$srcContent=preg_replace('/\}\s*$/',"/* @ImportAction('core','Site','jsError') */\n}",$srcContent);
		}/* @ImportAction('core','Site','jsError') */
		$srcContent=preg_replace_callback('/\/\*\s+@Import(Action|Function)\(([^*]+)\)\s+\*\//',function(&$m) use(&$enhanced,&$controllersSrc,$srcContent){
			eval('$eval=array('.$m[2].');');
			if(!isset($eval))
				$this->throwException('Error eval : '.$m[2]);
			$countEval=count($eval);
			if($countEval===3 && ($eval[0]==='core')||($eval[0]==='springbok')){
				array_shift($eval);
				$controllerPath=CORE.'controllers/'.$eval[0].'Controller.php';
				if(!isset($controllersSrc[$countEval.$controllerPath]))
					$controllersSrc[$countEval.$controllerPath]=file_get_contents($controllerPath);
			}else{
				$parentPath=$countEval===4 ? $enhanced->pluginPathFromKey(array_shift($eval)) : $enhanced->getAppDir().'src/';
				$suffix=$countEval===3||$countEval===4 ? '.'.array_shift($eval):''; if($suffix==='.') $suffix='';
				$controllerPath='controllers'.$suffix.'/'.($eval[0]).'Controller.php';
				if(!isset($controllersSrc[$countEval.$controllerPath]))
					$controllersSrc[$countEval.$controllerPath]=file_get_contents($parentPath.$controllerPath);
			}
			if($m[1]==='Action'){
				if(!preg_match_all(str_replace('function\s+([a-zA-Z0-9_ \$]+)','function\s+('
									.($eval[1]==='#'||$eval[1]==='*'?'[a-zA-Z_]+':preg_quote($eval[1])).')',
							ControllerFile::REGEXP_ACTION),$controllersSrc[$countEval.$controllerPath],$mAction))
					$this->throwException('Import action : unable to find '.$controllerPath.' '.$eval[1]);
				$res='';//mAction : 1 = annotations, 2=name, 3=params, 4=content
				foreach($mAction[0] as $kAction=>$srcAction){
					if(!preg_match(str_replace('function\s+([a-zA-Z0-9_ \$]+)','function\s+'.$mAction[2][$kAction],ControllerFile::REGEXP_ACTION),$srcContent))
						$res.=$srcAction."\n";
				}
				return $res;
			}else{
				if(!preg_match_all(self::regexpFunction($eval[1]),$controllersSrc[$countEval.$controllerPath],$mFunction))
					$this->throwException('Import action : unable to find '.$controllerPath.' '.$eval[1]);
				return implode("\n",$mFunction[0])."\n";
			}
		},$srcContent);
		$srcContent=preg_replace('/\/\*\s+@SimpleAction\(\'([^*\']+)\'\)\s+\*\//',"/** */\n\tfunction $1(){\n\t\trender();\n\t}",$srcContent);
		$srcContent=preg_replace('/\/\*\s+@SimpleCheckedAction\(\'([^*\']+)\'(?:,(\'([^*\']+)\'))?\)\s+\*\//',"/** @Check($2) */\n\tfunction $1(){\n\t\trender();\n\t}",$srcContent);
		$this->_srcContent=$srcContent;
	}
	
	public function enhancePhpContent($phpContent,$false=false){
		$matches=array();
		preg_match('/(?:\/\*\*([^{]*)\*\/\s+)?class ([A-Za-z_0-9]+)Controller/U',$phpContent,$matches);//debug($matches);
		if(empty($matches[2])) return parent::enhancePhpContent($phpContent);
		$this->_className=$matches[2];
		self::_delAclPermissions($this->_className);
		$this->_classAnnotations=empty($matches[1])?array():PhpFile::parseAnnotations($matches[1]);
		
		if(!empty($this->_traits)){
			$phpContent=trim(substr(trim($phpContent),5));
			
			foreach($this->_traits as $trait)
				$phpContent=trim(substr(trim($trait['content']),5))."\n".$phpContent;
			
			$phpContent='<?php '.$phpContent;
		}
		
		//$content=preg_replace_callback('/(?:\/\*\*(.*)\*\/)?[\s]+public[\s]+function[\s]+([a-zA-Z0-9_ \$]+)[\s]*\((.*)\)[\s]*{([^{]*(?:{[^{]*(?:{[^{]*(?:{[^{]*(?:{[^{]*(?:{[^{]*(?:{[^{]*(?:{[^{]*(?:{[^{]*(?:{[^{]*(?:{[^{]*(?:{[^{]*(?:{[^{]*(?:{[^{]*(?:{[^{]*(?:{[^{]*(?:{.*})*[^{]*})*[^{]*})*[^{]*})*[^{]*})*[^{]*})*[^{]*})*[^{]*})*[^{]*})*[^{]*})*[^{]*})*[^{]*})*[^{]*})*[^{]*})*[^{]*})*[^{]*})*[^{]*)}/Ums',array($this,'enhanceMethodParams'),$content);
		$phpContent=preg_replace_callback(self::REGEXP_ACTION,array($this,'enhanceMethodParams'),$phpContent);
		
		$phpContent=preg_replace('/(self::|\s+)(mset|set|set_|setForLayout|setForLayoutAndView|'
				.'uploadedFiles|moveUploadedFile|redirect|redirectPermanent|redirectLast|'
				.'render|_render|renderJSON|renderText|renderHtml|renderFile|sendFile)\(/',
			"\n".'self::$2(',$phpContent);
		
		$phpContent=preg_replace_callback('/self::mset\(((?:\s*\$(?:[a-zA-Z0-9\_]+)\s*\,)*\s*\$(?:[a-zA-Z0-9\_]+)\s*)\);/mU',function(&$matches){
			$content='';
/*			foreach(explode(',',$matches[1]) as $varname){
				$content.="'".($varname=substr(trim($varname),1))."'".'=>&$'.$varname.',';
			}
			return 'self::mset(array('.rtrim($content,',').'));';*/
			
			foreach(explode(',',$matches[1]) as $varname)
				$content.="self::set/*#if PROD*/_/*#/if*/('".($varname=substr(trim($varname),1))."'".',$'.$varname.');'; //TODO !
			return $content;
		},$phpContent);
		
		//$phpContent=preg_replace('/(extends [A-Za-z_]*Controller{)/','$1 protected static $_methodAnnotations='.UPhp::exportCode($this->_methodAnnotations).';', $phpContent);
		
		unset($this->_classAnnotations['Check'],$this->_classAnnotations['Post'],$this->_classAnnotations['Ajax']);
		
		
		return parent::enhancePhpContent($phpContent,$this->_classAnnotations);
	}
	

	private function enhanceMethodParams($matches){
		// 1:annotations, 2:name, 3:args, 4:content
		list($types,$annotations)=$this->parseMethodAnnotations($matches[1]);
		$params=explode(',',$matches[3]);
		$aparams=array(); $paramsString='';
		foreach($params as $param){
			$param=trim($param);
			if(empty($param)) continue;
			$type='string';
			if(strpos($param,' ')!==false){
				$param=explode(' ', $param);
				$type=trim($param[0]);
				$param=trim($param[1]);
			}elseif(isset($types[substr($param,1)])){
				$type=$types[substr($param,1)];
			}
			$aparams[substr($param,1)]=$type;
			$paramsString.=$param.',';
		}
		$paramsString=rtrim($paramsString,',');
		

		$mdef=array();
		if(empty($aparams)) $mdef['params']=false;
		else foreach($aparams as $paramName=>$type){
			$mdef['params'][$paramName]=array('type'=>$type);
			if(isset($annotations[$paramName])) $mdef['params'][$paramName]['annotations']=$annotations[$paramName];
		}
		
		$mdef['annotations']=isset($annotations['!'])?$annotations['!']:array();
		
		$methodBody=$matches[4];
		
		$hasCheck=isset($mdef['annotations']['Check']) || (isset($this->_classAnnotations['Check']) && !isset($mdef['annotations']['NoCheck']));
		/* Dernier à etre testé */
		if(isset($mdef['annotations']['Acl']) || isset($this->_classAnnotations['Acl'])){
			$aclAnnotation=isset($mdef['annotations']['Acl'])?$mdef['annotations']['Acl']:$this->_classAnnotations['Acl'];
			$permission=$aclAnnotation[0];
			self::_addAclPermission($this->_className,$permission);
			$methodBody='if(false===ACAcl::checkAccess('.UPhp::exportString($permission).(empty($aclAnnotation[1])?'':','.$aclAnnotation[1]).')) '
				.($hasCheck?'':'CSecure::isConnected() ?').'forbidden()'.($hasCheck?'':':CSecure::redirectToLogin()').';'
					.$methodBody;
			unset($mdef['annotations']['Acl']);
		}
		if($hasCheck){
			$checkAnnotation=isset($mdef['annotations']['Check'])?$mdef['annotations']['Check']:$this->_classAnnotations['Check'];
			if(is_string($checkAnnotation[0]))
				$methodBody=array_shift($checkAnnotation).'::checkAccess('.UPhp::exportCode($checkAnnotation,'').');'.$methodBody;
			else
				$methodBody='ACSecure::checkAccess('.UPhp::exportCode($checkAnnotation,'').');'.$methodBody;
			unset($mdef['annotations']['Check']);
		}
		
		if(isset($mdef['annotations']['Required'])){
			foreach($mdef['annotations']['Required'] as $required)
				$mdef['params'][$required]['annotations']['Required']=false;
			unset($mdef['annotations']['Required']);
		}
		
		foreach(array('NotEmpty','Id') as $annotationName)
			if(isset($mdef['annotations'][$annotationName])){
				if($annotationName==='Id' && empty($mdef['annotations'][$annotationName])) $mdef['annotations'][$annotationName]=array('id');
				foreach($mdef['annotations'][$annotationName] as $fieldName){
					$mdef['params'][$fieldName]['annotations']['Required']=false;
					$mdef['params'][$fieldName]['annotations'][$annotationName]=0;
				}
				unset($mdef['annotations']['NotEmpty']);
			}
		
		if(isset($mdef['annotations']['Valid'])){
			foreach($mdef['annotations']['Valid'] as $valid)
				$mdef['params'][$valid]['annotations']['Valid']=0;
			unset($mdef['annotations']['Valid']);
		}
		
		if(isset($mdef['annotations']['AllRequired'])){
			foreach($mdef['params'] as $paramName=>$param)
				$mdef['params'][$paramName]['annotations']['Required']=false;
			unset($mdef['annotations']['AllRequired']);
		}

		/* Les premiers à être testés */
		if(isset($mdef['annotations']['Post']) || isset($this->_classAnnotations['Post'])){
			$methodBody='if(empty($_POST)) /*#if DEV */throw new Exception("POST empty");/*#/if*//*#if PROD*/methodNotAllowed();/*#/if*/'.$methodBody;
			unset($mdef['annotations']['Post']);
		}
		if(isset($mdef['annotations']['Ajax']) || isset($this->_classAnnotations['Ajax'])){
			$methodBody='if(!CHttpRequest::isAjax()) /*#if DEV */throw new Exception("Should be ajax");/*#/if*//*#if PROD*/notFound();/*#/if*/'.$methodBody;
			unset($mdef['annotations']['Ajax']);
		}
		

		$this->_methodDefFiles[$this->_className.'-'.$matches[2]]='<?php return '.UPhp::exportCode($mdef).';';

		return 'public static function '.$matches[2].'('.$paramsString.'){'.PHP_EOL.$methodBody.PHP_EOL.'}';
	}

	public function getEnhancedDevContent(){
		$this->writeMethodDefFile();
		return parent::getEnhancedDevContent();
	}
	public function getEnhancedProdContent(){
		if(substr($this->_className,0,3)==='Dev') return false;
		$this->writeMethodDefFile();
		return parent::getEnhancedProdContent();
	}
	
	private function writeMethodDefFile(){
		$dirname=$this->currentDestFile->getPath();
		$folderMethods=new Folder(dirname($dirname).DS.'methods');
		$folderMethods->mkdirs(0775);
		$folderMethods=$folderMethods->getPath();
		
		//UExec::exec('cd / && rm -f '.UExec::rmEscape($folderMethods.$this->_className).'-*');
		UExec::exec('cd '.escapeshellarg($folderMethods).' && rm -f '.UExec::rmEscape($this->_className).'-*');
		foreach($this->_methodDefFiles as $filename=>$content){
			$file=new File($folderMethods.$filename);
			$file->write($content);
		}
		if(($entry=basename(dirname($dirname))) != 'controllers') $key=substr($entry,11).DS;
		else $key='';
		self::$defFiles[$key][$this->_className]=array_keys($this->_methodDefFiles);
	}

	private function parseMethodAnnotations($content){
		$annotations=array();$matches=array();$types=array();
		//[*] test > @Required
		preg_match_all($pattern='/[\*\s]*([A-Za-z0-9_]+)[\s]+>[\s]+@([A-Za-z0-9_]+)(?:\(([^\)]*)\))?/ms',$content,$matches);
		//matches : 1:paramName, 2:annotationName, 3:args
		foreach($matches[1] as $key=>$pname){
			if($matches[2][$key]=='Type'){
				$types[$pname]=$matches[3][$key];
				continue;
			}
			//if(!isset($annotations[$pname])) $annotations[$pname]=array();
			$annotations[$pname][$matches[2][$key]]=(empty($matches[3][$key]) ? false: eval("return array(".$matches[3][$key].");"));
		}/*
		foreach ($annotations as $pname=>$pannotations){
			$pannotations=rtrim($pannotations,',');
			$pannotations.=')';
			$annotations[$pname]=$pannotations;
		}*/
		
		$content=preg_replace($pattern,'',$content);
		$matches=array();
		preg_match_all('/[\*\s]*[\s]+@([A-Za-z0-9_]+)(?:\(([^\)]*)\))?/ms',$content,$matches);
		if(!empty($matches[1])){
			$annotations['!']=array();;
			foreach($matches[1] as $key=>$aname)
				$annotations['!'][$aname]=(empty($matches[2][$key]) ?false:eval("return array(".$matches[2][$key].");"));
		}
		return array($types,$annotations);
	}


	
	private static $defFiles,$controllersDeleted,$aclPermissionsConfig,$aclPermissionsChanges=false;
	public static function initFolder($folder,$config){
		$f=new File($folder->getPath().'config/jobs.php');
		if($f->exists()){
			//$f->moveTo($tmpFolder.'jobs.php');
			self::$aclPermissionsConfig=include $f->getPath();
		}else self::$aclPermissionsConfig=array();
		
		/*$entries=empty($config['entries']) ? array() : $config['entries']; 
		$entries[]='index';
		
		foreach($entries as $entry){
			$suffix= $entry==='index' ? '' : '.'.$entry;
			$d=new Folder($folder->getPath().'controllers'.$suffix.'/methods'); if($suffix) $d->mkdirs(0775);
			if($d->exists()) $d->moveTo($tmpFolder.'controllers'.$suffix.'/methods');
			$d->mkdirs(0775);
		}*/
	}
	
	public static function initEnhanceApp(){
		self::$defFiles=array();
	}
	public static function fileDeleted($file){
		$controllerName=substr($file->getName(),0,-(4+10));
		if(($entry=basename(dirname($file->getPath()))) != 'controllers') $key='.'.$entry;
		else $key='';
		self::$controllersDeleted[$key][]=$controllerName;
		
		self::_delAclPermissions($controllerName);
	}
	private static function _delAclPermissions($controllerName){
		if(!empty(self::$aclPermissionsConfig['controllers'][$controllerName])){
			self::$aclPermissionsChanges=true;
			foreach(self::$aclPermissionsConfig['controllers'][$controllerName] as $permission){
				unset(self::$aclPermissionsConfig['permissions'][$permission]['controllers'][$controllerName]);
				if(empty(self::$aclPermissionsConfig['permissions'][$permission]['controllers'])) 
					unset(self::$aclPermissionsConfig['permissions'][$permission]);
			}
		}
	}
	private static function _addAclPermission($controllerName,$permission){
		self::$aclPermissionsChanges=true;
		self::$aclPermissionsConfig['controllers'][$controllerName][]=$permission;
		self::$aclPermissionsConfig['controllers'][$controllerName]=array_unique(self::$aclPermissionsConfig['controllers'][$controllerName]);
		self::$aclPermissionsConfig['permissions'][$permission]['controllers'][$controllerName]=true;
	}
	
	public static function afterEnhanceApp(&$enhanced,&$dev,&$prod){
		if(self::$aclPermissionsChanges){
			$content='<?php return '.UPhp::exportCode(self::$aclPermissionsConfig).';';
			file_put_contents($dev->getPath().'config/aclPermissions.php',$content);
			file_put_contents($prod->getPath().'config/aclPermissions.php',$content);
		}
		if($enhanced->hasOldDef()){
			$paths=array($dev->getPath(),$prod->getPath());
			if(!empty(self::$controllersDeleted))
				foreach(self::$controllersDeleted as $key=>$controllers){
					foreach($controllers as $controller)
						foreach($paths as $path){
							UExec::exec('cd / && rm -Rf '.escapeshellarg($path.'controllers'.$key.'/methods/'.$controller.'-').'*');
						}
				}
			
			/*
			$def_controller=file_get_contents(json_decode($appDir.'enhance_def_controller.json',true));
			
			if(!empty(self::$controllersDeleted))
				foreach(self::$controllersDeleted as $key=>$name) unset($def_controller[$key][$name]);
			
			foreach(self::$defFiles as $key=>$controllers)
				foreach($controllers as $className=>$fileNames)
					unset($def_controller[$key][$className]);
			
			foreach($def_controller as $suffix=>$controllers){
				$path_part2='controllers'.$suffix.'/methods/';
				foreach(array($tmpDev=>$dev->getPath(),$tmpProd=>$prod->getPath()) as $src=>$dest){
					$f=new Folder($src.$path_part2);
					$f->mkdirs();
					foreach($controllers as $className=>$fileNames){
						foreach($fileNames as $fileName){
							$file=new File($src.$path_part2.$fileName);
							$destFile=new File($dest.$path_part2.$fileName);
							if(!$destFile->exists()) $file->moveTo($destFile->getPath());
						}
					}
				}
			}
			
			// add new or modified controllers
			foreach(self::$defFiles as $key=>$controllers)
				foreach($controllers as $className=>$fileNames)
					$def_controller[$key][$className]=$fileNames;
				*/
		}
		//file_put_contents($appDir.'enhance_def_controller.json',json_encode(self::$defFiles));
	}
}
