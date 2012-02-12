<?php
class ControllerFile extends PhpFile{
	private $_className,$_annotations=array(),$_classAnnotations;
	private $_methodDefFiles=array();//,$_methodAnnotations=array();

	protected function enhancePhpContent($phpContent,$false=false){
		$matches=array();
		preg_match('/(?:\/\*\*([^{]*)\*\/\s+)?class ([A-Za-z_0-9]+)Controller/U',$phpContent,$matches);//debug($matches);
		if(empty($matches[2])) return parent::enhancePhpContent($phpContent);
		$this->_className=$matches[2];
        $this->_classAnnotations=empty($matches[1])?array():PhpFile::parseAnnotations($matches[1]);
		
		//$content=preg_replace_callback('/(?:\/\*\*(.*)\*\/)?[\s]+public[\s]+function[\s]+([a-zA-Z0-9_ \$]+)[\s]*\((.*)\)[\s]*{([^{]*(?:{[^{]*(?:{[^{]*(?:{[^{]*(?:{[^{]*(?:{[^{]*(?:{[^{]*(?:{[^{]*(?:{[^{]*(?:{[^{]*(?:{[^{]*(?:{[^{]*(?:{[^{]*(?:{[^{]*(?:{[^{]*(?:{[^{]*(?:{.*})*[^{]*})*[^{]*})*[^{]*})*[^{]*})*[^{]*})*[^{]*})*[^{]*})*[^{]*})*[^{]*})*[^{]*})*[^{]*})*[^{]*})*[^{]*})*[^{]*})*[^{]*})*[^{]*)}/Ums',array($this,'enhanceMethodParams'),$content);
		$phpContent=preg_replace_callback('/(?:\/\*\*([^{]*)\*\/)\s+function\s+([a-zA-Z0-9_ \$]+)\s*\((.*)\)[\s]*{\s*(.*)\s*\n\t}\n/Ums',
				array($this,'enhanceMethodParams'),$phpContent);
		
		$phpContent=preg_replace('/(self::|\s+)(mset|set|set_|setForLayout|setForLayout_|setForLayoutAndView|setForLayoutAndView_|'
				.'uploadedFiles|moveUploadedFile|redirect|redirectLast|'
				.'render|_render|renderTable|renderJSON|renderText|renderHtml|renderFile|sendFile)\(/',
			' self::$2(',$phpContent);
		
		$phpContent=preg_replace_callback('/self::mset\(((?:\s*\$(?:[a-zA-Z0-9\_]+)\s*\,)*\s*\$(?:[a-zA-Z0-9\_]+)\s*)\);/mU',function(&$matches){
			$content='';
			foreach(explode(',',$matches[1]) as $varname){
				$content.="'".($varname=substr(trim($varname),1))."'".'=>&$'.$varname.',';
			}
			return 'self::mset(array('.rtrim($content,',').'));';
		},$phpContent);
		
		//$phpContent=preg_replace('/(extends [A-Za-z_]*Controller{)/','$1 protected static $_methodAnnotations='.UPhp::exportCode($this->_methodAnnotations).';', $phpContent);
		
		unset($this->_classAnnotations['Check'],$this->_classAnnotations['Post'],$this->_classAnnotations['Ajax']);
		
		parent::enhancePhpContent($phpContent,$this->_classAnnotations);
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
		if(isset($mdef['annotations']['Check']) || isset($this->_classAnnotations['Check'])){
			$checkAnnotation=isset($mdef['annotations']['Check'])?$mdef['annotations']['Check']:$this->_classAnnotations['Check'];
			if(is_string($checkAnnotation[0]))
				$methodBody=array_shift($checkAnnotation).'::checkAccess('.UPhp::exportCode($checkAnnotation,'').');'.$methodBody;
			else
				$methodBody='ACSecure::checkAccess('.UPhp::exportCode($checkAnnotation,'').');'.$methodBody;
			unset($mdef['annotations']['Check']);
		}
		if(isset($mdef['annotations']['Post']) || isset($this->_classAnnotations['Post'])){
			$methodBody='if(empty($_POST)) /* DEV */throw new Exception("POST empty");/* /DEV *//* PROD */notFound();/* /PROD */'.$methodBody;
			unset($mdef['annotations']['Post']);
		}
		if(isset($mdef['annotations']['Ajax']) || isset($this->_classAnnotations['Ajax'])){
			$methodBody='if(!CHttpRequest::isAjax()) /* DEV */throw new Exception("Should be ajax");/* /DEV *//* PROD */notFound();/* /PROD */'.$methodBody;
			unset($mdef['annotations']['Ajax']);
		}
		
		if(isset($mdef['annotations']['Required'])){
			foreach($mdef['annotations']['Required'] as $required)
				$mdef['params'][$required]['annotations']['Required']=false;
			unset($mdef['annotations']['Required']);
		}
		
		if(isset($mdef['annotations']['Valid'])){
			foreach($mdef['annotations']['Valid'] as $valid)
				$mdef['params'][$valid]['annotations']['Valid']=false;
			unset($mdef['annotations']['Valid']);
		}
		
		if(isset($mdef['annotations']['AllRequired'])){
			foreach($mdef['params'] as $paramName=>$param)
				$mdef['params'][$paramName]['annotations']['Required']=false;
			unset($mdef['annotations']['AllRequired']);
		}
		
		$this->_methodDefFiles[$this->_className.'-'.$matches[2]]='<?php return '.UPhp::exportCode($mdef).';';

		return 'public static function '.$matches[2].'('.$paramsString.'){'.PHP_EOL.$methodBody.PHP_EOL.'}';
	}

	public function getEnhancedDevContent(){
		$this->writeMethodDefFile();
		return parent::getEnhancedDevContent();
	}
	public function getEnhancedProdContent(){
		$this->writeMethodDefFile();
		return parent::getEnhancedProdContent();
	}
	
	private function writeMethodDefFile(){
		$dirname=$this->currentDestFile->getPath();
		$folderMethods=new Folder(dirname($dirname).DS.'methods');
		$folderMethods->mkdirs();
		$folderMethods=$folderMethods->getPath();
		UExec::exec('rm -f '.escapeshellarg($folderMethods.$this->_className.'-*'));
		foreach($this->_methodDefFiles as $filename=>$content){
			$file=new File($folderMethods.$filename);
			$file->write($content);
		}
		if(($entrance=basename(dirname($dirname))) != 'controllers') $key=substr($entrance,11).DS;
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



	private static $defFiles,$controllersDeleted;
	public static function initFolder($folder,&$config){
		/*$entrances=empty($config['entrances']) ? array() : $config['entrances']; 
		$entrances[]='index';
		
		foreach($entrances as $entrance){
			$suffix= $entrance==='index' ? '' : '.'.$entrance;
			$d=new Folder($folder->getPath().'controllers'.$suffix.'/methods'); if($suffix) $d->mkdirs(0775);
			if($d->exists()) $d->moveTo($tmpFolder.'controllers'.$suffix.'/methods');
			$d->mkdirs(0775);
		}*/
	}
	
	public static function initEnhanceApp(){
		self::$defFiles=array();
	}
	public static function fileDeleted($file){
		if(($entrance=basename(dirname($file->getPath()))) != 'controllers') $key='.'.$entrance;
		else $key='';
		self::$controllersDeleted[$key][]=substr($file->getName(),0,-(4+10));
	}
	public static function endEnhanceApp(){}
	
	public static function afterEnhanceApp($hasOldDef,&$newDef,&$appDir,&$dev,&$prod){
		if($hasOldDef){
			$paths=array($dev->getPath(),$prod->getPath());
			if(!empty(self::$controllersDeleted))
				foreach(self::$controllersDeleted as $key=>$controllers){
					foreach($controllers as $controller)
						foreach($paths as $path){
							UExec::exec('rm -Rf '.escapeshellarg($path.'controllers'.$key.'/methods/'.$controller.'-').'*');
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
