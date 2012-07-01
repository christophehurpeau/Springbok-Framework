<?php
class ConfigFile extends PhpFile{
	public function getMd5Content(){
		//return '';
		$md5=$this->_srcContent;
		
		if($this->fileName()==='routes.php' || substr($this->fileName(),0,7)==='routes_'){
			$routesLangsFileName=dirname($this->srcFile()->getPath()).'/routes-langs'.substr($this->fileName(),6);
			if(file_exists($routesLangsFileName)) $md5.=file_get_contents($routesLangsFileName);
			$routesLangsFileName=dirname($this->srcFile()->getPath()).'/routes-langs.php';
			if(file_exists($routesLangsFileName)) $md5.=file_get_contents($routesLangsFileName);
		}
		
		if($this->enhanced->isApp() && substr($this->fileName(),0,1) === '_'){
			if($this->fileName()!=='_.php') $md5.=file_get_contents(dirname($this->srcFile()->getPath()).'/_.php');
			
			if(!empty($this->enhanced->appConfig['plugins']))
				foreach($this->enhanced->appConfig['plugins'] as $key=>$plugin){
					$devPluginPath=$this->enhanced->pluginPath($plugin).'src/';
					if(file_exists($pluginConfigPath=($devPluginPath.'config/'.$this->fileName())))
						$md5.=file_get_contents($pluginConfigPath);
				}
				
			if($this->enhanced->configNotEmpty('plugins')){
				foreach($this->enhanced->config['plugins'] as $key=>$plugin){
					$devPluginPath=$this->enhanced->pluginPath($plugin);
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
		elseif($this->enhanced->isPlugin()){
			if(substr($configname,0,5)==='lang.'){
				$fileLang=$this->enhanced->getAppDir().'db/'.substr($configname,5).'.db';
				if(file_exists($fileLang)){
					$db=new DBSQLite(false,array( 'file'=>$fileLang,'flags'=>SQLITE3_OPEN_READWRITE ));
					$db->doUpdate('DELETE FROM t WHERE c=\'a\' AND EXISTS( SELECT 1 FROM t t2 WHERE t.s=t2.s AND t.t=t2.t AND t2.c=\'P\' )');
					$configArray=include $this->srcFile()->getPath();
					foreach($configArray as $key=>$value){
						$db->doUpdate('INSERT OR IGNORE INTO t (s,c,t) VALUES ('.$db->escape($key).',\'a\','.$db->escape($value).')');
						$db->doUpdate('REPLACE INTO t (s,c,t) VALUES ('.$db->escape($key).',\'P\','.$db->escape($value).')');
					}
				}
				$this->write($configname,'',$devFile,$prodFile);
			}
		}elseif(substr($configname,0,7)=='routes_'){
			throw new Exception('Define all routes in routes.php, now.');
		}elseif($configname=='routes'){
			/* ROUTES */
			$routes=include $this->srcFile()->getPath();
			if(!isset($routes['index'])) $routes=array('index'=>$routes);
			$finalRoutes=array();
			foreach($routes as $entry=>$entryRoutes){
				foreach($entryRoutes as $url=>$route){
					$finalRoutes[$entry][$url]['_']=$route[0]; $paramsDef=isset($route[1])?$route[1]:NULL; $langs=isset($route[2])?$route[2]:NULL;
					$finalRoutes[$entry][$url]['ext']=isset($route['ext'])?$route['ext']:NULL;
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
								if(isset($paramsDef[$m[2]])) return $paramsDef[$m[2]]=='id' ? '([0-9]+)' : '('.$paramsDef[$m[2]].')'; /* can have 0 before : 001-Slug */
								if(in_array($m[2],array('id'))) return '([0-9]+)';
								return '([^\/]+)';
							},$routeLangPreg).($finalRoutes[$entry][$url]['ext']===null?'':($finalRoutes[$entry][$url]['ext']==='html'?'(\.html|)':'(\.'.$finalRoutes[$entry][$url]['ext'].')')),
							1=>rtrim(str_replace('/*','%s',str_replace(array('?','(',')'),'',preg_replace('/(\:[a-zA-Z_]+)/m','%s',$routeLang))),'/')
						);
						$finalRoutes[$entry][$url][$lang]=$routeLang;
						if(!empty($paramsNames)) $finalRoutes[$entry][$url][':']=$paramsNames;
					}
					$finalRoutes[$entry][$url]['paramsCount']=substr_count($finalRoutes[$entry][$url]['en'][1],'%s');
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
			
			
			//$this->enhanced->appConfig['enhance_time']=time();
			//$this->write($configname,UPhp::exportCode($configArray),$devFile,$prodFile);
		}elseif($configname[0]==='_'){
			$configArray=include $this->srcFile()->getPath();
			if($this->enhanced->isApp()){
				foreach(array('siteUrl') as $attr)
					if(!isset($configArray[$attr])) throw new Exception('Missing attr config : '.$attr.' (file : '.$configname.')');
				
				if(!empty($this->enhanced->config['entries']))
					foreach($this->enhanced->config['entries'] as $entry)
						if(!isset($configArray['siteUrl'][$entry])) throw new Exception('Missing site url for entry : '.$entry.' (file : '.$configname.')');
				
				foreach($configArray['siteUrl'] as $key=>&$val) $val=rtrim($val,'/');
				if(!isset($configArray['cookie_domain'])) $configArray['cookie_domain']='';

				$configArray=UArray::union_recursive($configArray,$this->enhanced->appConfig);
				$configArray['autoload_default']=APP.'models/';
				if(!empty($this->enhanced->appConfig['plugins']))
					foreach($this->enhanced->appConfig['plugins'] as $key=>$plugin){
						if(isset($plugin[2])){
							$pluginPath=$configArray['pluginsPaths'][$plugin[0]].$plugin[1].DS.($configname==='_'.ENV?'dev/':'');
							$configArray['autoload_default']=$pluginPath.'models/';
						}
						$devPluginPath=$this->enhanced->devConfig['pluginsPaths'][$plugin[0]].$plugin[1].'/src/';
						if(file_exists($pluginConfigPath=($devPluginPath.'config/'.$configname.'.php')))
							$configArray=UArray::union_recursive($configArray,include $pluginConfigPath);
					}
				
				if($this->enhanced->configNotEmpty('plugins'))
					foreach($this->enhanced->config['plugins'] as $key=>$plugin){
						$devPluginPath=$this->enhanced->devConfig['pluginsPaths'][$plugin[0]].$plugin[1];
						if(file_exists($pluginConfigPath=($devPluginPath.'/config/'.$configname.'.php')))
							$configArray=UArray::union_recursive($configArray,include $pluginConfigPath);
					}
				
				$configArray['models_infos']=$configArray['autoload_default'].'infos/';
			}elseif($this->enhanced->isPlugin()){
				return;
			}else{
				$configArray['autoload_default']=NULL;
				$configArray['models_infos']=NULL;
			}
			$this->writeClass($configname,$configArray,$devFile,$prodFile);
		}elseif($configname==='basicSettings'){
			$configArray=include $this->srcFile()->getPath();
			if(!file_exists($settingsFile=($this->enhanced->getAppDir().'data/settings.php'))) $settingsData=$configArray;
			else $settingsData=(include $settingsFile)+$configArray;
			
			file_put_contents($settingsFile,'<?php return '.UPhp::exportCode($settingsData).';');
			$this->write($configname,UPhp::exportCode($configArray),$devFile,$prodFile);
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
			$configArray['static_url']=rtrim($configArray['static_url'],'/');
			$afterContent.=UPhp::exportString($configArray['static_url'].'/');
			/*unset($configArray['static_url']);*/
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
		$contentProd=$contentDev=$content;
		$contentDev.='/*if(class_exists("DB",false))*/ DB::loadConfig(true);';
		foreach($this->enhanced->config['base'] as $name){
			$content='include CORE.\'base/'.$name.'.php\';';
			$contentDev.=$content; $contentProd.=$content;
		}
		
		foreach(array($devFile=>$contentDev,$prodFile=>$contentProd) as $dest=>$content){
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
