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
			if($this->fileName()!=='_.php'&&$this->fileName()!=='_.yml')
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
	private static $_openedLangs=array();
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
		if($configname=='enhance'||$configname=='tests'||$configname==='deployments') ; //nothing
		elseif(substr($configname,0,5)==='lang.'){
			$lang=substr($configname,5);
			$fileLang=$this->enhanced->getAppDir().'db/'.$lang.'.db';
			if(file_exists($fileLang)){
				$db=isset(self::$_openedLangs[$lang])?self::$_openedLangs[$lang]:
					(self::$_openedLangs[$lang]=new DBSQLite(false,array( 'file'=>$fileLang,'flags'=>SQLITE3_OPEN_READWRITE )));
				$pluginName=$this->enhanced->getName();
				if(($md5Value=$db->doSelectValue('SELECT t FROM t WHERE c=\'P\' AND s="plugin.'.$pluginName.'.md5"'))!==$this->md5){
					$this->addWarning("UPDATE LANGS : ".$pluginName.' ('.$md5Value.' != '.$this->md5.')');
					$db->beginTransaction();
					$db->doUpdate('DELETE FROM t WHERE c=\'a\' AND EXISTS( SELECT 1 FROM t t2 WHERE t.s=t2.s AND t.t=t2.t AND t2.c=\'P\' AND t.s LIKE "plugin.'.$pluginName.'.%" )');
					$configArray=include $this->srcFile()->getPath();
					foreach($configArray as $key=>$value){
						if(substr($key,0,7)==='models.') $db->doUpdate('INSERT OR IGNORE INTO t (s,c,t) VALUES ('.$db->escape(substr($key,7)).',\'f\','.$db->escape($value).')');
						else $db->doUpdate('INSERT OR IGNORE INTO t (s,c,t) VALUES ('.$db->escape($key).',\'a\','.$db->escape($value).')');
						$db->doUpdate('REPLACE INTO t (s,c,t) VALUES ('.$db->escape($key).',\'P\','.$db->escape($value).')');
					}
					$db->doUpdate('REPLACE INTO t (s,c,t) VALUES ("plugin.'.$pluginName.'.md5",\'P\','.$db->escape($this->md5).')');
					$db->commit();
				}
			}
			$this->write($configname,'',$devFile,$prodFile);
		}elseif($this->enhanced->isPlugin()){
			
		}elseif(substr($configname,0,7)=='routes_'){
			$this->throwException('Define all routes in routes.php, now.');
		}elseif($configname=='routes'){
			/* LANGS */
			$finalTranslations=NULL;
			$langsFilePath=dirname($this->srcFile()->getPath()).DS.'routes-langs.php';
			if(file_exists($langsFilePath)){
				$translations=include $langsFilePath;
				if($translations!==NULL){
					$translations=$this->mergeWithPluginsConfig('routes-langs',$translations);
					$finalTranslations=array();
					foreach($translations as $s=>$t){
						if(!isset($t['en'])) $t['en']=$s;
						foreach($t as $lang=>$s2){
							$finalTranslations['->'.$lang][strtolower($s)]=$s2;
							$finalTranslations[$lang.'->'][strtolower($s2)]=$s;
						}
					}
				}
			}
			
			$translate=function($lang,$string) use($finalTranslations){
				$lstring=UString::low($string);
				if(!isset($finalTranslations['->'.$lang][$lstring]))
					$this->throwException('Missing route translation : "'.$string.'" for lang "'.$lang.'"');
				return $finalTranslations['->'.$lang][$lstring];
			};
			
			/* ROUTES */
			$routes=self::incl($this->srcFile()->getPath());
			if(!isset($routes['index'])) $routes=array('index'=>$routes);
			$finalRoutes=array();
			
			
			if(empty($this->enhanced->appConfig['availableLangs']))
				$this->throwException('Missing config "availableLangs" in config/_.php');
			$allLangs=empty($this->enhanced->appConfig['allLangs']) ? 
								$this->enhanced->appConfig['availableLangs'] : $this->enhanced->appConfig['allLangs'];
			
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
					$finalRoutes[$entry][$url][0]=explode('::',$route[0]); $paramsDef=isset($route[1])?$route[1]:null; $langs=isset($route[2])?$route[2]:null;
					$finalRoutes[$entry][$url]['ext']=isset($route['ext'])?$route['ext']:null;
					$route=array();
					
					if($finalRoutes[$entry][$url][0][0] === '!') $this->throwException('Controller "!" does not exists, set default controller to "Site" in routes');
					if($finalRoutes[$entry][$url][0][1] === '!') $this->throwException('Action "!" does not exists, set default action to "index" in routes');
					
					if($langs !== null){
						$route=$langs;
						foreach($allLangs as $lang){
							if(!isset($route[$lang])){
								if($lang==='en') $route['en']=$url;
								else $this->throwException('Missing lang "'.$lang.'" for route "'.$url.'"');
							}
						}
					}elseif(!preg_match('#/[a-zA-Z\_]#',$url)){
						foreach($allLangs as $lang) $route[$lang]=$url;
					}else{
						foreach($allLangs as $lang)
							$route[$lang]=preg_replace_callback('#/([a-zA-Z\_]+)#',function($r) use($translate,$lang){
											return '/'.$translate($lang,$r[1]); },$url);
						//$this->throwException('Missing langs for route : '.$url);
					}
					
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
						
						$routeLang=array(0=>preg_replace_callback('/(\(\?)?\:([a-zA-Z_\-]+)/',
							function($m) use($paramsDef,$lang,&$paramsNames,$translate){
								if(!empty($m[1])) return $m[0];
								$paramsNames[]=$m[2];
								if(isset($paramsDef[$m[2]])){
									if(is_array($paramsDef[$m[2]])) $paramDefVal=$paramsDef[$m[2]][$lang];
									else{
										$paramDefVal=$paramsDef[$m[2]];
										if(preg_match('/^[a-zA-Z\|\_]+$/',$paramDefVal))
											$paramDefVal=implode('|',array_map(function($s) use($translate,$lang){
													return $translate($lang,$s);
												},explode('|',$paramDefVal)));
									}
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
					$finalRoutes[$entry][$url]['paramsCount']=substr_count($finalRoutes[$entry][$url][$allLangs[0]][1],'%s');
				}
			}
		
			
			$finalProdContent=UPhp::exportCode(array('routes'=>$finalRoutes,'langs'=>$finalTranslations));
			$devRoute=array(array('Dev!','index'),
				':'=>array('controller','action'),'paramsCount'=>3,'ext'=>null,
				'_'=>array('\/Dev\/([^\/]+)(?:\/([^\/]+)(?:\/(.*))?)?','/Dev/%s/%s%s'));
			foreach($allLangs as $lang) $devRoute[$lang]=$devRoute['_'];
			$finalRoutes['index']=array('/dev/:controller(/:action/*)?'=>$devRoute)+$finalRoutes['index'];
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
					if(!isset($configArray[$attr])) $this->throwException('Missing attr config : '.$attr.' (file : '.$configname.')');
				
				if(is_string($configArray['siteUrl']))
					$configArray['siteUrl'] = array( 'index'=> $configArray['siteUrl'] );
				
				if(!isset($configArray['generate']))
					$configArray['generate'] = array( 'default'=> true );
				
				if(!empty($this->enhanced->config['entries']))
					foreach($this->enhanced->config['entries'] as $entry)
						if(!isset($configArray['siteUrl'][$entry])) $this->throwException('Missing site url for entry : '.$entry.' (file : '.$configname.')');
				
				foreach($configArray['siteUrl'] as $key=>&$val){
					$val=explode('://',$val,2);
					$val[0]= $val[0]==='HTTP_OR_HTTPS' ? null : $val[0].'://';
					$val[1]=rtrim($val[1],'/');
				}
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
						foreach(array('php','json','yml') as $extPlugin){
							if(file_exists($pluginConfigPath=($devPluginPath.'config/'.$configname.'.'.$extPlugin)))
								$configArray=UArray::union_recursive($configArray,self::incl($pluginConfigPath,$extPlugin));
						}
					}
				
				
				if(isset($configArray['default_lang']))
					$this->throwException('Please change in your config file "config/_.php" : '
								."'default_lang'=>'".$configArray['default_lang']."' into "
								."'availableLangs'=>array('".$configArray['default_lang']."')");
				
				if(empty($configArray['allLangs']))
					$configArray['allLangs']=$configArray['availableLangs'];
				
				if(is_string($configArray['cookie_domain'])){
					$cdomain=$configArray['cookie_domain'];
					$configArray['cookie_domain']=array_map(function() use($configArray){
						 return $configArray['cookie_domain']; },$configArray['siteUrl']);
				}else{
					foreach($configArray['siteUrl'] as $entry=>$siteUrl)
						if(!array_key_exists($entry,$configArray['cookie_domain']))
							throw new Exception('Missing cookie_domain for entry "'.$entry.'" (file : '.$configname.')');
				}
				
				if(!isset($configArray['cacheStore']))
					$configArray['cacheStore']=isset($configArray['db']['cache'])?'SViewCacheStoreMongo':'SViewCacheStoreFile';
				
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
			$ext = $this->srcFile()->getExt();
			if($ext === 'php' && $configname === 'aclGroups'){
				$this->throwException('aclGroups file is deprecated : use aclGroups.yml now !'."\n".yaml_emit(include $this->srcFile()->getPath()));
			}
			if(($ext === 'php' && substr(file_get_contents($this->srcFile()->getPath()),0,12)=='<?php return') || $ext === 'yml'){
				$configArray=self::incl($this->srcFile()->getPath(),$ext);
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
				foreach(array('php','json','yml') as $extPlugin){
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
		$content="define('STATIC_URL',"; $afterContent='';
		if(isset($configArray['static_url'])){
			if(substr($configArray['static_url'],0,16)==='HTTP_OR_HTTPS://') $configArray['static_url']=substr($configArray['static_url'],16);
			elseif(substr($configArray['static_url'],0,7)==='http://') $configArray['static_url']=substr($configArray['static_url'],7);
			$configArray['static_url']=rtrim($configArray['static_url'],'/');
			$content.='HTTP_OR_HTTPS.'.UPhp::exportString($configArray['static_url'].'/');
			unset($configArray['static_url']);
		}else $content.="BASE_URL.'/web/'";
		$content.=");define('WEB_URL',STATIC_URL.WEB_FOLDER);";
		
		if(isset($configArray['data_dir'])){
			$content.="define('DATA',".$this->replaceAPP(UPhp::exportString(rtrim($configArray['data_dir'],'/').'/')).");";
			unset($configArray['data_dir']);
		}elseif(in_array($configname,array('_dev','_home','_work','_'.ENV))) $afterContent.="defined('DATA')||define('DATA',dirname(APP).'/data/');";
		else $content.="define('DATA',APP.'data/');";
		
		foreach(array('img_dir'=>'IMGDIR') as $configK=>$constN)
			if(isset($configArray[$configK]))
				$content.="define('".$constN."',".$this->replaceAppAndData(UPhp::exportString(rtrim($configArray[$configK],'/').'/')).");";
		
		$configArray['db']['_lang']=$configname==='_'.ENV ? dirname(APP).'/db/' : APP.'db/';
		
		$content.='class Config{public static ';
		foreach($configArray as $key=>$val){
			$code=UPhp::exportCode($val);
			if(strpos($code,"'".APP)!==false||strpos($code,"'".DATA)!==false||strpos($code,"'HTTP_OR_HTTPS")!==false){
				$content.='$'.$key.',';
				$afterContent.='Config::$'.$key.'='.$this->replaceAppAndData($code).';';
			}else $content.='$'.$key.'='.$code.',';
		}
		$content='<?php '.substr($content,0,-1).';}'.$afterContent;
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
		return str_replace("'HTTP_OR_HTTPS","HTTP_OR_HTTPS.'",str_replace("'HTTP_OR_HTTPS://","HTTP_OR_HTTPS.'",str_replace("'".APP,"APP.'",str_replace("'".DATA,"DATA.'",$code))));
	}
	
	public static function incl($path,$ext=null){
		if($ext===null) $ext=UFile::extension($path);
		return $ext==='php' ? include $path : ($ext==='json'? UFile::getJSON($path) : UFile::getYAML($path));
	}
}
