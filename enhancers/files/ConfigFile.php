<?php
class ConfigFile extends PhpFile{
	public static $baseConfigArray=array();
	public static function reset(){
		self::$baseConfigArray=array();
	}
	
	public function getMd5Content(){
		//return '';
		$md5=$this->_srcContent;
		
		if($this->fileName()==='routes.php' || substr($this->fileName(),0,7)==='routes_'){
			$routesLangsFileName=dirname($this->srcFile()->getPath()).'/routes-langs'.substr($this->fileName(),6);
			if(file_exists($routesLangsFileName)) $md5.=file_get_contents($routesLangsFileName);
		}
		
		if(!empty(self::$baseConfigArray) && substr($this->fileName(),0,1) == '_'){
			if($this->fileName()!=='_.php') $md5.=file_get_contents(dirname($this->srcFile()->getPath()).'/_.php');
			
			
			if(!empty(self::$baseConfigArray['plugins'])){
				$configArray=include $this->srcFile()->getPath();
				foreach(self::$baseConfigArray['plugins'] as $key=>$plugin){
					//$pluginPath=$configArray['pluginsPaths'][$plugin[0]].$plugin[1].DS.($this->fileName()==='_'.ENV.'.php'?'dev'.DS:'');
					$devPluginPath=include dirname($this->srcFile()->getPath()).'/_'.ENV.'.php';
					$devPluginPath=$devPluginPath['pluginsPaths'][$plugin[0]].$plugin[1].'/src/';
					if(file_exists($pluginConfigPath=($devPluginPath.'config/'.$this->fileName())))
						$md5.=file_get_contents($pluginConfigPath);
				}
			}
		}
		return md5($md5);
	}
	
	public function processEhancing($devFile,$prodFile,$justDev=false,$isCore=false){
		$configname=substr($this->fileName(),0,-4);
		/*if(substr($configname,0,2)=='db'){
			$env='';
			if(strpos($configname,'_')>0){
				$env=strstr($configname,'_');
				$configname=strstr($configname,'_',true);
			}
			$values=include $this->srcFile()->getPath();
			foreach($values as $key=>$val){
				if(!is_array($val)) continue;
				$content='<?php return '; PhpFile::recursiveArray($content,$val);

				foreach(array($devFile,$prodFile) as $dest){
					$dest=new File(dirname($dest).DS.$configname.'_'.$key.$env.'.php');
					$dest->write(str_replace(',)',')',rtrim($content,',').';'));
				}
			}
		}else*/
		if($configname=='enhance') ; //nothing
		elseif($configname=='routes'||substr($configname,0,7)=='routes_'){
			$routes=include $this->srcFile()->getPath();
			
			/* ROUTES */
			$finalRoutes=array();
			foreach($routes as $url=>$route){
				$finalRoutes[$url]['_']=$route[0]; $paramsDef=isset($route[1])?$route[1]:NULL; $langs=isset($route[2])?$route[2]:NULL;
				$finalRoutes[$url]['ext']=isset($route['ext'])?$route['ext']:NULL;
				$route=array('en'=>$url); if($langs !== NULL) $route=$route+$langs;
				foreach($route as $lang=>&$routeLang){
					$paramsNames=array();
					if($specialEnd=(substr($routeLang,-2)==='/*'))
						$routeLangPreg=substr($routeLang,0,-2);
					elseif($specialEnd2=(substr($routeLang,-4))==='/*)?')
						$routeLangPreg=substr($routeLang,0,-4).substr($routeLang,-2);
					else $routeLangPreg=$routeLang;
					$routeLangPreg=str_replace(array('/','-','*','('),array('\/','\-','(.*)','(?:'),$routeLangPreg);
					if($specialEnd) $routeLangPreg.='(?:\/(.*))?';
					elseif($specialEnd2) $routeLangPreg=substr($routeLangPreg,0,-2).'(?:\/(.*))?'.substr($routeLangPreg,-2);
					
					$routeLang=array(0=>preg_replace_callback('/(\(\?)?\:([a-zA-Z_]+)/',
						function($m) use(&$paramsDef,&$paramsNames){
							if(!empty($m[1])) return $m[0];
							$paramsNames[]=$m[2];
							if(isset($paramsDef[$m[2]])) return $paramsDef[$m[2]]=='id' ? '([0-9]+)' : '('.$paramsDef[$m[2]].')';
							if(in_array($m[2],array('id'))) return '([0-9]+)';
							return '([^\/]+)';
						},$routeLangPreg).($finalRoutes[$url]['ext']===null?'':($finalRoutes[$url]['ext']==='html'?'(?:\.html)?':'\.'.$finalRoutes[$url]['ext'])),
						1=>rtrim(str_replace('/*','%s',str_replace(array('?','(',')'),'',preg_replace('/(\:[a-zA-Z_]+)/m','%s',$routeLang))),'/')
					);
					$finalRoutes[$url][$lang]=$routeLang;
					if(!empty($paramsNames)) $finalRoutes[$url][':']=$paramsNames;
				}
			}
		
			/* LANGS */
			$finalTranslations=NULL;
			$langsFilePath=dirname($this->srcFile()->getPath()).DS.($configname=='routes'?'routes-langs':'routes-langs_'.substr($configname,7)).'.php';
			if(file_exists($langsFilePath)){
				$translations=include $langsFilePath;
				if($translations!==NULL){
					$finalTranslations=array();
					foreach($translations as $s=>$t){
						foreach($t as $lang=>$s2){
							$finalTranslations['en->'.$lang][$s]=$s2;
							$finalTranslations[$lang.'->en'][$s2]=$s;
						}
					}
				}
			}
			
			$finalArray=array('routes'=>$finalRoutes,'langs'=>$finalTranslations);
			$this->write($configname,UPhp::exportCode($finalArray),$devFile,$prodFile);
		}elseif($configname=='routes-langs'||substr($configname,0,13)=='routes-langs_'){
			//NOTHING
			
		/*}elseif($configname=='core'){
			$configArray=include $this->srcFile()->getPath();
			if(file_exists(($filename=dirname(CORE).DS.'config'.DS.'core.php')))
				$configArray=array_merge($configArray,include $filename);
			if(file_exists(($filename=dirname(CORE).DS.'config'.DS.$configArray['project_name'].'.php')))
				$configArray=array_merge($configArray,include $filename);
			
			$content='<?php return '.UPhp::exportCode($configArray);

			foreach(array($devFile,$prodFile) as $dest){
				$dest=new File(dirname($dest).DS.$configname.'.php');
				$dest->write(str_replace(',)',')',rtrim($content,',').';'));
			}
		*/}elseif($configname==='_'){
			
			
			//self::$baseConfigArray['enhance_time']=time();
			//$this->write($configname,UPhp::exportCode($configArray),$devFile,$prodFile);
		}elseif($configname[0]==='_'){
			$configArray=include $this->srcFile()->getPath();
			if(!empty(self::$baseConfigArray)){ // if ! config plugin
				foreach(array('site_url') as $attr)
					if(!isset($configArray[$attr])) throw new Exception('Missing attr config : '.$attr.' (file : '.$configname.')');
				
				foreach($configArray as $key=>&$val){
					if($key==='site_url' || substr($key,-9)==='_site_url'){
						$val=rtrim($val,'/');
					}
				}
				
				if(!isset($configArray['cookie_domain'])) $configArray['cookie_domain']='';

				$configArray=UArray::union_recursive($configArray,self::$baseConfigArray);
				$configArray['autoload_default']=APP.'models/';
				if(!empty(self::$baseConfigArray['plugins']))
					foreach(self::$baseConfigArray['plugins'] as $key=>$plugin){
						if(isset($plugin[2])){
							$pluginPath=$configArray['pluginsPaths'][$plugin[0]].$plugin[1].DS.($configname==='_'.ENV?'dev/':'');
							$configArray['autoload_default']=$pluginPath.'models/';
						}
						$devPluginPath=include dirname($this->srcFile()->getPath()).'/_'.ENV.'.php';
						$devPluginPath=$devPluginPath['pluginsPaths'][$plugin[0]].$plugin[1].'/src/';
						if(file_exists($pluginConfigPath=($devPluginPath.'config/'.$configname.'.php')))
							$configArray=UArray::union_recursive($configArray,include $pluginConfigPath);
					}
				$configArray['models_infos']=$configArray['autoload_default'].'infos/';
			}else{
				$configArray['autoload_default']=NULL;
				$configArray['models_infos']=NULL;
			}
			$this->writeClass($configname,$configArray,$devFile,$prodFile);
		}else{
			if(substr(file_get_contents($this->srcFile()->getPath()),0,12)=='<?php return'){
				$configArray=include $this->srcFile()->getPath();
				$this->write($configname,UPhp::exportCode($configArray),$devFile,$prodFile);
			}else parent::processEhancing($devFile,$prodFile,$justDev);
		}
		//else parent::processEhancing($devFile,$prodFile,$justDev);
	}

	private function write(&$configname,$content,&$devFile,&$prodFile){
		$content='<?php return '.str_replace("'".APP,"APP.'",$content).';';
		foreach(array($devFile,$prodFile) as $dest){
			$dest=new File(dirname($dest).DS.$configname.'.php');
			$dest->write($content);
		}
	}

	private function writeClass(&$configname,&$configArray,&$devFile,&$prodFile){
		$content='<?php class Config{public static ';
		$afterContent="define('STATIC_URL',";
		if(isset($configArray['static_url'])){
			$afterContent.=UPhp::exportString(rtrim($configArray['static_url'],'/').'/');
			unset($configArray['static_url']);
		}else $afterContent.="BASE_URL.'/web/'";
		$afterContent.=".WEB_FOLDER);";
		
		if(isset($configArray['data_dir'])){
			$afterContent.="define('DATA',".$this->replaceAPP(UPhp::exportString(rtrim($configArray['data_dir'],'/').'/')).");";
			unset($configArray['data_dir']);
		}elseif(in_array($configname,array('_dev','_home','_work'))) $afterContent.="define('DATA',dirname(APP).'/data/');";
		else $afterContent.="define('DATA',APP.'data/');";
		
		foreach(array('img_dir'=>'IMGDIR') as $configK=>$constN)
			if(isset($configArray[$configK]))
				$afterContent.="define('".$constN."',".$this->replaceAppAndData(UPhp::exportString(rtrim($configArray[$configK],'/').'/')).");";
		
		
		foreach($configArray as $key=>$val){
			$code=UPhp::exportCode($val);
			if(strpos($code,"'".APP)!==false){
				$content.='$'.$key.',';
				$afterContent.='Config::$'.$key.'='.$this->replaceAPP($code).';';
			}else $content.='$'.$key.'='.$code.',';
		}
		$content=substr($content,0,-1).';}'.$afterContent;
		foreach(array($devFile,$prodFile) as $dest){
			$dest=new File(dirname($dest).DS.$configname.'.php');
			$dest->write($content);
		}
	}
	
	private function replaceAPP($code){
		return str_replace("'".APP,"APP.'",$code);
	}
	
	private function replaceAppAndData($code){
		return str_replace("'".APP,"APP.'",str_replace("'".DATA,"DATA.'",$code));
	}
}
