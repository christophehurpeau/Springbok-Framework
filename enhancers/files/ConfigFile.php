<?php
class ConfigFile extends PhpFile{
	public static $CACHE_PATH=false;
	
	public function getMd5Content(){
		//return '';
		$md5=$this->_srcContent;
		
		if($this->fileName()==='routes.php'){
			$routesLangsFileName=dirname($this->srcFile()->getPath()).'/routes-langs.php';
			if(file_exists($routesLangsFileName)) $md5.=file_get_contents($routesLangsFileName);
		}
		
		if($this->enhanced->isApp() && substr($this->fileName(),0,1) === '_'){
			if($this->fileName()!=='_.php'&&$this->fileName()!=='_.json')
				$md5.=file_get_contents(dirname($this->srcFile()->getPath()).'/_.'.UFile::extension($this->fileName()));
			
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
		return $this->md5=md5($md5);
	}
	
	public function processEhancing($devFile,$prodFile,$justDev=false,$isCore=false){
		$ext=UFile::extension($srcFilePath=$this->srcFile()->getPath());
		$configname=substr($this->fileName(),0,-(strlen($ext)+1));
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
		if($configname=='enhance'||$configname=='tests') ; //nothing
		elseif($this->enhanced->isPlugin()){
			if(substr($configname,0,5)==='lang.'){
				$fileLang=$this->enhanced->getAppDir().'db/'.substr($configname,5).'.db';
				if(file_exists($fileLang)){
					$db=new DBSQLite(false,array( 'file'=>$fileLang,'flags'=>SQLITE3_OPEN_READWRITE ));
					$pluginName=$this->enhanced->getName();
					if(($md5Value=$db->doSelectValue('SELECT t FROM t WHERE c=\'P\' AND s="plugin.'.$pluginName.'.md5"'))!==$this->md5){
						debugVar("UPDATE LANGS : ".$pluginName.' ('.$md5Value.' != '.$this->md5.')'); 
						$db->doUpdate('DELETE FROM t WHERE c=\'a\' AND EXISTS( SELECT 1 FROM t t2 WHERE t.s=t2.s AND t.t=t2.t AND t2.c=\'P\' AND t.s LIKE "plugin.'.$pluginName.'.%" )');
						$configArray=include $this->srcFile()->getPath();
						foreach($configArray as $key=>$value){
							if(substr($key,0,7)==='models.') $db->doUpdate('INSERT OR IGNORE INTO t (s,c,t) VALUES ('.$db->escape(substr($key,7)).',\'f\','.$db->escape($value).')');
							else $db->doUpdate('INSERT OR IGNORE INTO t (s,c,t) VALUES ('.$db->escape($key).',\'a\','.$db->escape($value).')');
							$db->doUpdate('REPLACE INTO t (s,c,t) VALUES ('.$db->escape($key).',\'P\','.$db->escape($value).')');
						}
						$db->doUpdate('REPLACE INTO t (s,c,t) VALUES ("plugin.'.$pluginName.'.md5",\'P\','.$db->escape($this->md5).')');
					}
				}
				$this->write($configname,'',$devFile,$prodFile);
			}
		}elseif(substr($configname,0,7)=='routes_'){
			throw new Exception('Define all routes in routes.php, now.');
		}elseif($configname=='routes'){
			/* ROUTES */
			$routes=self::incl($this->srcFile()->getPath());
			if(!isset($routes['index'])) $routes=array('index'=>$routes);
			$finalRoutes=array();
			foreach($routes as $entry=>$entryRoutes){
				if(isset($entryRoutes['includesFromEntry'])){
					if(is_string($entryRoutes['includesFromEntry'])) $entryRoutes['includesFromEntry']=array($entryRoutes['includesFromEntry']);
					foreach($entryRoutes['includesFromEntry'] as $includeFromEntry){
						if(is_string($includeFromEntry)) $entryRoutes=$entryRoutes+$routes[$includeFromEntry];
						else foreach($includeFromEntry as $includeRouteFromEntry) $entryRoutes[$includeRouteFromEntry]=$routes[$includeFromEntry][$includeRouteFromEntry];
					}
					unset($entryRoutes['includesFromEntry']);
				}
				foreach($entryRoutes as $url=>$route){
					$finalRoutes[$entry][$url][0]=$route[0]; $paramsDef=isset($route[1])?$route[1]:NULL; $langs=isset($route[2])?$route[2]:NULL;
					$finalRoutes[$entry][$url]['ext']=isset($route['ext'])?$route['ext']:NULL;
					$route=array('_'=>$url); if($langs !== NULL) $route=$route+$langs;
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
							function($m) use($paramsDef,$lang,&$paramsNames){
								if(!empty($m[1])) return $m[0];
								$paramsNames[]=$m[2];
								if(isset($paramsDef[$m[2]])){
									$paramDefVal=is_array($paramsDef[$m[2]]) ? $paramsDef[$m[2]][$lang] : $paramsDef[$m[2]];
									return $paramDefVal=='id' ? '([0-9]+)' : '('.str_replace('(','(?:',$paramDefVal).')'; /* can have 0 before : 001-Slug */
								}
								if(in_array($m[2],array('id'))) return '([0-9]+)';
								return '([^\/]+)';
							},$routeLangPreg).($finalRoutes[$entry][$url]['ext']===null?'':($finalRoutes[$entry][$url]['ext']==='html'?'(\.html|)':'(\.'.$finalRoutes[$entry][$url]['ext'].')')),
							1=>str_replace('/*','%s',str_replace(array('?','(',')'),'',preg_replace('/(\:[a-zA-Z_]+)/m','%s',$routeLang)))
						);
						if($routeLang[1]!=='/') $routeLang[1]=rtrim($routeLang[1],'/');
						$finalRoutes[$entry][$url][$lang]=$routeLang;
						if(!empty($paramsNames)) $finalRoutes[$entry][$url][':']=$paramsNames;
					}
					$finalRoutes[$entry][$url]['paramsCount']=substr_count($finalRoutes[$entry][$url]['_'][1],'%s');
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
							$finalTranslations['->'.$lang][strtolower($s)]=$s2;
							$finalTranslations[$lang.'->'][strtolower($s2)]=$s;
						}
					}
				}
			}
			
			$finalProdContent=UPhp::exportCode(array('routes'=>$finalRoutes,'langs'=>$finalTranslations));
			$finalRoutes['index']=array('/dev/:controller(/:action/*)?'=>array('Dev!::!',
				':'=>array('controller','action'),'paramsCount'=>3,'ext'=>null,
				'_'=>array('\/Dev\/([^\/]+)(?:\/([^\/]+)(?:\/(.*))?)?','/Dev/%s/%s%s'),
				'fr'=>array('\/Dev\/([^\/]+)(?:\/([^\/]+)(?:\/(.*))?)?','/Dev/%s/%s%s')
				))+$finalRoutes['index'];
			$finalDevContent=UPhp::exportCode(array('routes'=>$finalRoutes,'langs'=>$finalTranslations));
			$this->write($configname,$finalProdContent,$devFile,$prodFile,$finalDevContent);
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
			$configArray=self::incl($srcFilePath,$ext);
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
						foreach(array('php','json') as $extPlugin){
							if(file_exists($pluginConfigPath=($devPluginPath.'config/'.$configname.'.'.$extPlugin)))
								$configArray=UArray::union_recursive($configArray,self::incl($pluginConfigPath,$extPlugin));
						}
					}
				
				$configArray=$this->mergeWithPluginsConfig('_',$configArray);
				$configArray=$this->mergeWithPluginsConfig($configname,$configArray);
				$configArray['models_infos']=$configArray['autoload_default'].'infos/';
				if(!empty($this->enhanced->config['modelParents'])){
					$configArray['modelParents']=array('type2model'=>$this->enhanced->config['modelParents']);
				}
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
				$configArray=$this->mergeWithPluginsConfig($configname,$configArray);
				$this->write($configname,UPhp::exportCode($configArray),$devFile,$prodFile);
			}else parent::processEhancing($devFile,$prodFile,$justDev);
		}
		//else parent::processEhancing($devFile,$prodFile,$justDev);
	}

	private function mergeWithPluginsConfig($configname,$configArray){
		if($this->enhanced->configNotEmpty('plugins'))
			foreach($this->enhanced->config['plugins'] as $key=>$plugin){
				$devPluginPath=$this->enhanced->devConfig['pluginsPaths'][$plugin[0]].$plugin[1];
				foreach(array('php','json') as $extPlugin){
					if(file_exists($pluginConfigPath=($devPluginPath.'/config/'.$configname.'.'.$extPlugin)))
						$configArray=UArray::union_recursive($configArray,self::incl($pluginConfigPath,$extPlugin));
				}
			}
		return $configArray;
	}

	private function write($configname,$content,&$devFile,&$prodFile,$contentForDev=false){
		$content='<?php return '.str_replace("'".APP,"APP.'",$content).';';
		if($contentForDev!==false) $contentForDev='<?php return '.str_replace("'".APP,"APP.'",$contentForDev).';';
		foreach(array($devFile,$prodFile) as $k=>$dest){
			$dest=new File(dirname($dest).DS.$configname.'.php');
			$dest->write($contentForDev===false?$content:($k===0?$contentForDev:$content));
		}
	}

	private function writeClass($configname,&$configArray,&$devFile,&$prodFile){
		$content='<?php class Config{public static ';
		$afterContent="define('STATIC_URL',";
		if(isset($configArray['static_url'])){
			$configArray['static_url']=rtrim($configArray['static_url'],'/');
			$afterContent.=UPhp::exportString($configArray['static_url'].'/');
			unset($configArray['static_url']);
		}else $afterContent.="BASE_URL.'/web/'";
		$afterContent.=");define('WEB_URL',STATIC_URL.WEB_FOLDER);";
		
		if(isset($configArray['data_dir'])){
			$afterContent.="define('DATA',".$this->replaceAPP(UPhp::exportString(rtrim($configArray['data_dir'],'/').'/')).");";
			unset($configArray['data_dir']);
		}elseif(in_array($configname,array('_dev','_home','_work','_'.ENV))) $afterContent.="define('DATA',dirname(APP).'/data/');";
		else $afterContent.="define('DATA',APP.'data/');";
		
		foreach(array('img_dir'=>'IMGDIR') as $configK=>$constN)
			if(isset($configArray[$configK]))
				$afterContent.="define('".$constN."',".$this->replaceAppAndData(UPhp::exportString(rtrim($configArray[$configK],'/').'/')).");";
		
		$configArray['db']['_lang']=$configname==='_'.ENV ? dirname(APP).'/db/' : APP.'db/';
		
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
	
	public static function incl($path,$ext=null){
		if($ext===null) $ext=UFile::extension($path);
		return $ext==='php' ? include $path : UFile::getJSON($path);
	}
}
