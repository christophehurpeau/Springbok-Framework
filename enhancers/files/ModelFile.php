<?php
class ModelFile extends PhpFile{
	public static $CACHE_PATH=false;
	public $_className,$_contentInfos,$_classAnnotations;
	
	const REGEXP_FIELDS='/public\s+((?:\/\*\*[^;{]*\*\/\s+\$[A-Za-z0-9\s_]+\s*(?:,\s*)?)+\s*;)/Ums';
	const REGEXP_CLASS='/(?:\/\*\*([^{]*)\*\/\s+)?class ([A-Za-z_0-9]+)([^{]*){/s';
	const REGEXP_CONSTS='/\bconst\s+[^;]+\s*;/i';
	const REGEXP_TRAITS='/\{\s+(use\s+[^;]+\s*;)/i';
	const REGEXP_CLASS_WITH_ANNOTATIONS='/\/\*\*([^{]*)\*\/\s+class ([A-Za-z_0-9]+)([^{]*){/';
	
	
	public static function _getPath($m,&$controllersSrc,$enhanced,$withParam=false){
		eval('$eval=array('.$m[1].');');
		if(!isset($eval))
			throw new Exception('Error eval : '.$m[1]);
		$countEval=count($eval); $param=null;
		if($countEval===($withParam?3:2) && ($eval[0]==='core')||($eval[0]==='springbok')){
			array_shift($eval); $pluginKey='CORE';
			$modelPath=CORE.'models/'.$eval[0].'.php';
			if(!isset($controllersSrc[$pluginKey.'/'.$modelPath]))
				$controllersSrc[$pluginKey.'/'.$modelPath]=file_get_contents($modelPath);
		}else{
			if($countEval===($withParam?3:2)){
				$pluginKey=array_shift($eval);
				if(!isset($enhanced->config['plugins'][$pluginKey])){
					$enhanced->addWarning(null,'plugin doesnt exists: '.$pluginKey);
					return false;
				}
				$parentPath = $enhanced->pluginPathFromKey($pluginKey);
			}else $enhanced->getAppDir().'src/';
			$modelPath='models/'.($eval[0]).'.php';
			if(!isset($controllersSrc[$pluginKey.'/'.$modelPath]))
				$controllersSrc[$pluginKey.'/'.$modelPath]=file_get_contents($parentPath.$modelPath);
		}
		return $withParam? array($controllersSrc[$pluginKey.'/'.$modelPath],$eval[1]) : $controllersSrc[$pluginKey.'/'.$modelPath];
	}
	
	protected function loadContent($srcContent){//TODO mettre en commun le code avec ControllerFile dans PhpFile.
		$controllersSrc=array(); $enhanced=$this->enhanced; $extends=false;
		
		$srcContent=preg_replace_callback('/\/\*\s+@(Extends|Inherits)\(([^*]+)\)\s+\*\//',function($m) use(&$extends){
			if($m[1]==='Extends') $extends=array(1=>$m[2]);
			return "/* @ImportConsts(".$m[2].") */"
					."/* @ImportTraits(".$m[2].") */"
					."/* @ImportFields(".$m[2].") */"
					."/* @ImportArrayFields(".$m[2].",'#') */"
					."/* @ImportFunction(".$m[2].",'#') */";
		},$srcContent);
		if($extends!==false){
			$path=ModelFile::_getPath($extends, $controllersSrc, $enhanced);
			if(!$path) $this->throwException('Unknown path');
			preg_match(self::REGEXP_CLASS_WITH_ANNOTATIONS, $path, $extendsM);
			$srcContent=preg_replace(self::REGEXP_CLASS_WITH_ANNOTATIONS,'/** '.$extendsM[1].' $1 **/'."\n".'class $2$3{',$srcContent);
		}
		
		$srcContent=preg_replace_callback('/\/\*\s+@ImportFields\(([^*]+)\)\s+\*\//',function($m) use($enhanced,&$controllersSrc,$extends){
			$path=ModelFile::_getPath($m, $controllersSrc, $enhanced);
			if(!$path) return '';
			if(!preg_match(ModelFile::REGEXP_FIELDS,$path,$mFields)){
				if($extends!==false) return '';
				$this->throwException('Import fields : unable to find '.$path);
			}
			return $mFields[0];
		},$srcContent);
		
		$srcContent=preg_replace_callback('/\/\*\s+@ImportArrayFields\(([^*]+)\)\s+\*\//',function($m) use($enhanced,&$controllersSrc,$extends){
			$path=ModelFile::_getPath($m, $controllersSrc, $enhanced,true);
			if(!$path) return "\n";
			list($path,$fieldsNames)=$path;
			if(!preg_match_all(self::regexpArrayField($fieldsNames),$path,$mFields)){
				if($extends!==false) return '';
				$this->throwException('Import array fields : unable to find '.$path);
			}
			return implode("\n",$mFields[0]);
		},$srcContent);
		
		$srcContent=preg_replace_callback('/\/\*\s+@ImportConsts\(([^*]+)\)\s+\*\//',function($m) use($enhanced,&$controllersSrc,$extends){
			$path=ModelFile::_getPath($m, $controllersSrc, $enhanced);
			if(!$path) return "\n";
			if(!preg_match_all(ModelFile::REGEXP_CONSTS,$path,$mConsts)){
				if($extends!==false) return '';
				$this->throwException('Import consts : unable to find '.$path);
			}
			return implode("\n",$mConsts[0]);
		},$srcContent);
		
		$srcContent=preg_replace_callback('/\/\*\s+@ImportTraits\(([^*]+)\)\s+\*\//',function($m) use($enhanced,&$controllersSrc,$extends){
			$path=ModelFile::_getPath($m, $controllersSrc, $enhanced);
			if(!$path) return "\n";
			if(!preg_match_all(ModelFile::REGEXP_TRAITS,$path,$mTraits)){
				if($extends!==false) return '';
				$this->throwException('Import traits : unable to find '.$path);
			}
			return implode("\n",$mTraits[1]);
		},$srcContent);
		
		$srcContent=preg_replace_callback('/\/\*\s+@ImportFunction\(([^*]+)\)\s+\*\//',function($m) use($enhanced,&$controllersSrc,$srcContent,$extends){
			$path=ModelFile::_getPath($m, $controllersSrc, $enhanced,true);
			if(!$path) return "\n";
			list($path,$functionNames)=$path;
			if(is_string($functionNames)) $functionNames=array($functionNames);
			$res='';
			foreach($functionNames as $functionName){
				if(!preg_match_all(self::regexpFunction($functionName),$path,$mFunction)){
					if($extends!==false) continue;
					$this->throwException('Import Function : unable to find '.$path.' '.$functionName);
				}
				foreach($mFunction[0] as $kFunction=>$srcFunction){
					if(!preg_match(self::regexpFunction($mFunction[1][$kFunction]),$srcContent))
						$res.=$srcFunction."\n";
				}
			}
			return $res;
		},$srcContent);
		$this->_srcContent=$srcContent;
	}
	
	private $_loadedTraits=array();
	private function callTraitsBuilder($methodName,$args){
		foreach($this->_loadedTraits as $loadedTrait)
			if(method_exists($loadedTrait,$methodName))
				call_user_func_array(array($loadedTrait,$methodName),$args);
	}
	
	private static function __Annotation_isFromClass($array){
		if(count($array)===1 && isset($array[0]) && is_array($array[0])){
			$array=$array[0];
			return array(count($array)===2 && isset($array[0]) && isset($array[1]),$array);
		}
		return array(false,$array);
	}
	
	public function enhancePhpContent($content,$false=false){
		$matches=array();
		//preg_match('/class ([A-Za-z_0-9]+)(?:[^{]*){/',$content,$matches);
		//debug($matches);
		
		if(preg_match(self::REGEXP_CLASS_WITH_ANNOTATIONS,$content,$matches) && !empty($matches[2])
						 && (($isSQL=preg_match('/@TableAlias\(/',$matches[1])) || ($isDb=preg_match('/@Db\(/',$matches[1]))) ){
			
			// SQL MODEL
			//$content=parent::enhancePhp($content,false);
			
			$modelFile=$this;
			
			if(!empty($this->_traits)){
				foreach($this->_traits as $trait){
					$loaded=true; $pathBuild=substr($trait['path'],0,-4).'_build.php';
					if(!class_exists($trait[0].'_build',false) && ($loaded=file_exists($pathBuild))) include $pathBuild;
					if($loaded) $this->_loadedTraits[]=$trait[0].'_build';
				}
			}
			
			
			
			if($isSQL){
				$content=preg_replace_callback('/\/\*\*([^;{]*)\*\/\s+public\s+\$([A-Za-z0-9\s_]+);/Ums',array($this,'fields'),$content);
				$content=preg_replace_callback(self::REGEXP_FIELDS,array($this,'mfields'),$content);
	
				$contentInfos=array('primaryKeys'=>array(),'columns'=>array(),'isAI'=>false,'indexes'=>array(),'relations'=>array(),'generate'=>'default');
				$content=preg_replace_callback(self::REGEXP_CLASS,function($matches) use($modelFile,&$content,&$contentInfos){
					$annotations=empty($matches[1])?array():PhpFile::parseAnnotations($matches[1],true);
					$modelFile->_className=$matches[2];
					$classBeforeContent='';
					
					if(!isset($annotations['TableName'])) $annotations['TableName'][0]=array(UInflector::pluralizeUnderscoredWords(UString::underscore(substr($modelFile->_className,0,2)===strtoupper(substr($matches[2],0,2))?substr($matches[2],isset($annotations['Db'])?2:1):$matches[2])));
					if(!isset($annotations['TableAlias'])) $this->throwException('Table Alias is missing for : '.$modelFile->_className);
					$dbName=isset($annotations['Db'])?$annotations['Db'][0][0]:false;
					if(isset($annotations['Generate'])) $contentInfos['generate']=$annotations['Generate'][0][0];
					if(isset($annotations['Engine'])) $contentInfos['Engine']=$annotations['Engine'][0][0];
					$createdField=isset($annotations['CreatedField'])?$annotations['CreatedField'][0][0]:false;
					$updatedField=isset($annotations['UpdatedField'])?$annotations['UpdatedField'][0][0]:false;
					$createdByField=isset($annotations['CreatedByField'])?$annotations['CreatedByField'][0][0]:false;
					$orderByField=isset($annotations['OrderByField'])?$annotations['OrderByField'][0][0]:false;
					$cacheable=isset($annotations['Cacheable'])?$annotations['Cacheable'][0][0]:false;
					
					if(isset($annotations['Comment'])){
						if(is_array($annotations['Comment'][0][0]))
							$contentInfos['comment']='See in '.$annotations['Comment'][0][0][0].'::'.$annotations['Comment'][0][0][1].'()';
						else
							$contentInfos['comment']=str_replace('\\\'',"'",$annotations['Comment'][0][0]);
						
					}
					
					
					$indexes=&$contentInfos['indexes'];
					if(isset($annotations['Index'])){
						foreach($annotations['Index'] as $index) $indexes[0][]=$index;
					}
					if(isset($annotations['IndexUnique'])){
						foreach($annotations['IndexUnique'] as $index) $indexes[1][]=$index;
					}
					
					if(isset($annotations['Created'])){
						if(isset($modelFile->_fields['created'])) $this->throwException($modelFile->_className.' already contains a field "created"');
						$modelFile->_fields['created']=array('SqlType'=>array('datetime'),'NotNull'=>false,'NotBindable'=>false,'Index'=>false);
					}
					if(isset($annotations['CreatedBy'])){
						if(isset($modelFile->_fields['created_by'])) $this->throwException($modelFile->_className.' already contains a field "created_by"');
						$modelFile->_fields[$createdByField='created_by']=array('SqlType'=>array('int(10) unsigned'),'Null'=>false,'NotBindable'=>false,'Index'=>false);
					}
					if(isset($annotations['Updated'])){
						if(isset($modelFile->_fields['updated'])) $this->throwException($modelFile->_className.' already contains a field "updated"');
						$modelFile->_fields['updated']=array('SqlType'=>array('datetime'),'Null'=>false,'NotBindable'=>false,'Default'=>array(NULL),'Index'=>false);
					}
					
					$this->callTraitsBuilder('onBuild',array($modelFile,&$contentInfos,$annotations,$this->enhanced->config,&$classBeforeContent));
					$this->callTraitsBuilder('afterBuild',array($modelFile));
					
					// check
					$traitsClassNames=empty($this->_traits) ? array() : array_map(function($t){return $t[0];},$this->_traits);
					foreach(array('Parent'=>'BParent','ParentBigintId'=>'BChild','Child'=>'BChild',
								'Slug'=>'BSlug','Seo'=>'BSeo') as $annotation=>$traitName){
						if(isset($annotations[$annotation]) && !in_array($traitName,$traitsClassNames))
							$this->throwException($modelFile->_className.' need to use trait "'.$traitName.'"');
					}
					
					if(isset($annotations['LogChanges'])) $this->throwException('LogChanges : Use trait "BLogChanges" now.');
					
					
					
					$eventsArray=empty($this->_traits) ? array() : array_map(function($t){ return $t['content_build']; },$this->_traits);
					$eventsArray[]=$content;
					
					foreach(array('beforeSave'=>'','beforeInsert'=>'','beforeUpdate'=>'','beforeDelete'=>'',
						'afterUpdateCompare'=>'$data,$primaryKeys','afterSave'=>array('$data=null','$data'),
								'afterInsert'=>array('$data=null','$data'),'afterUpdate'=>array('$data=null','$data'),
								'afterDelete'=>'') as $event=>$params){
							$eventsCallbacks=array(); $regexp=PhpFile::regexpArrayField($event);
							foreach($eventsArray as $c){
								$c=preg_replace_callback($regexp,function($matches2) use(&$eventsCallbacks,$event,$params){
									$eval=dev_eval('return '.$matches2[1]);
									if(empty($eval) && !empty($matches2[1]) && !is_array($eval)) $this->throwException('Failed to eval :'."\n".$matches2[1]);
									foreach($eval as $key=>$callback) $eventsCallbacks[]='$this->'.$callback.'('.(is_string($params)?$params:$params[1]).')';
									return '';
								},$c);
							}
							if(!empty($eventsCallbacks) || !preg_match(self::regexpFunction($event),$content)){
								$classBeforeContent.="\n".'protected function '.$event.'('.(is_string($params)?$params:$params[0]).'){';
								if(!empty($eventsCallbacks))
									$classBeforeContent.=' return '.implode(' && ',array_unique($eventsCallbacks)).';';
								else
									$classBeforeContent.=' return true;';
								$classBeforeContent.='}';
							}
						}
					
					$pkAutoGenerated=false;$enums=$specialFields=array();
					foreach($modelFile->_fields as $name=>$field)
						if(isset($field['Pk'])){
							$contentInfos['primaryKeys'][]=$name;
							if(isset($field['Pk'][0])){
								if($pkAutoGenerated=$field['Pk'][0])
									$classBeforeContent.="\n".'protected function _beforeInsert(){$this->'.$contentInfos['primaryKeys'][0].'='.($pkAutoGenerated=='UUID'?'UGenerator::uuid()':'')
													.';return parent::_beforeInsert();}';
							}
						
						}
					foreach($modelFile->_fields as $name=>&$field){
						$column=array();
						if(isset($field['Format'])) $field['Format']=$field['Format'][0];
						if(isset($field['Boolean'])){
							$column['type']='char(0)';
							$column['default']=(isset($field['Default']) && $field['Default'][0]?'""':null);
							$column['notnull']=false;
							$specialFields[$name]='Boolean';
						}elseif(isset($field['BooleanInt'])){
							$column['type']='tinyint(1) unsigned';
							$column['default']=isset($field['Default'])?$field['Default'][0]:false;
							$column['notnull']=isset($field['Null'])?false:true;
							$specialFields[$name]='BooleanInt';
						}else{
							if(isset($field['Datetime'])){
								$column['type']='int(11)';
								unset($field['Datetime']);
								$field['Format']=$field['var']='datetime';
							}elseif(isset($field['Price'])){
								$column['type']='decimal('.$field['Price'][0].','.$field['Price'][1].')';
								unset($field['Price']);
								$field['Format']='price';
							}elseif(isset($field['SqlType'])) $column['type']=str_replace('"',"'",$field['SqlType'][0]);
							$column['default']=(isset($field['DefaultValue'])?$field['DefaultValue'][0]:(isset($field['Default'])?$field['Default'][0]:false));
							$column['notnull']=isset($field['Null'])?false:true;
						}
						$column['unique']=isset($field['Unique'])?true:false;
						$column['index']=isset($field['Index'])?true:false;
						if(isset($field['Comment']))
							$column['comment']=str_replace('\\\'',"'",$field['Comment'][0]);
						else if(isset($field['Enum'])){
							if(count($field['Enum'])===1 && isset($field['Enum'][0]) && is_array($field['Enum'][0]))
								$column['comment'] = 'See in '.$field['Enum'][0][0].'::'.$field['Enum'][0][1];
							else{
								$maxItemLength = count($field['Enum']) > 8 ? 10 : 20;
								$column['comment'] = implode(',',UArray::map($field['Enum'],
										function($k,$v) use($maxItemLength){ return $k.'='.UString::truncate($v,$maxItemLength);}));
							}
							if(strlen($column['comment']) > 1024) $column['comment'] = substr($column['comment'],0,1018).' [...]';
						}else $column['comment'] = false;
						if(isset($field['AutoIncrement'])){ $field['NotBindable']=0; $column['autoincrement']=true; $contentInfos['isAI']=true; }
						else $column['autoincrement']=false;
						if(isset($field['CreatedField']) || (!$createdField && isset($column['type']) && in_array($column['type'],array('DATE','DATETIME','date','datetime'))
									&& in_array($name,array('created','cdate','date_add')))){
							$field['NotBindable']=0;
							if($column['type']==='date'|| $column['type']==='DATE') $field['Format']='date_';
							elseif($column['type']==='datetime'||$column['type']==='DATETIME') $field['Format']='datetime_';
							elseif($column['type']==='int(10)'||$column['type']==='int(11)') $field['Format']='datetime';
							$createdField=$name;
						}
						if(isset($field['UpdatedField']) || (!$updatedField && isset($column['type']) && in_array($column['type'],array('DATE','DATETIME','date','datetime')) 
									&& in_array($name,array('updated','modified','udate','mdate','date_modified','date_updated','date_upd')))){
							$field['NotBindable']=0;
							if($column['type']==='date'|| $column['type']==='DATE') $field['Format']='date_';
							elseif($column['type']==='datetime'||$column['type']==='DATETIME') $field['Format']='datetime_';
							elseif($column['type']==='int(10)'||$column['type']==='int(11)') $field['Format']='datetime';
							$updatedField=$name;
						}
						if(isset($field['CreatedByField'])){
							$field['NotBindable']=0;
							$createdByField=$name;
						}
						if(isset($field['OrderByField']) || (!$orderByField && $name==='position' 
							&& ((substr($column['type'],0,4)==='int(') || substr($column['type'],0,8)==='tinyint(')) ){
							$orderByField=$name;
						}
						if(isset($field['ForeignKey'])) $column['ForeignKey']=$field['ForeignKey'];
						$contentInfos['columns'][$name]=$column;
						
						if(isset($field['Index'])) $indexes[0][]=array($name);
						if(isset($field['Unique'])) $indexes[1][]=array($name);
						if(isset($field['Enum'])){ $enums[$name]=$field['Enum']; $field['Enum']=UInflector::pluralizeUnderscoredWords($name); }
						if(isset($field['Icons'])){ if(count($field['Icons'])===1&&isset($field['Icons'][0])&&is_array($field['Icons'][0])) $field['Icons']=$field['Icons'][0]; }
						if(isset($field['Json'])){ $specialFields[$name]='Json';}
						
						unset($field['Pk'],$field['Boolean'],$field['SqlType'],$field['Null'],$field['NotNull'],$field['DefaultValue'],$field['Default'],$field['AutoIncrement'],
									$field['CreatedField'],$field['UpdatedField'],$field['CreatedByField'],$field['PositionField'],
									$field['ForeignKey'],$field['Index'],$field['Comment']);
						if(!empty($field)) $contentInfos['annotations'][$name]=$field;
					}
					
					if(empty($enums)) $enums='';
					else{
						$res='';
						foreach($enums as $fieldName=>$array){
							list($isFromClass,$array) = self::__Annotation_isFromClass($array);
							$res.="\n".'public static function '.$fieldName.'List(){';
							if($isFromClass){
								$res.='return '.$array[0].'::'.$array[1].'();';
							}else{
								$res.='return array(';
								foreach($array as $key=>$value)
									$res.=UPhp::exportCode($key).'=>_tF('.UPhp::exportCode($matches[2]).','.UPhp::exportCode($fieldName.'.Enum.'.$value).','.UPhp::exportCode($value).'),';
								$res=(empty($array)?$res:substr($res,0,-1)).');';
							}
							$res.='}public function '.$fieldName.'(){';
							if($isFromClass){
								//$res.='$list='.$array[0].'::'.$array[1].'();return $list[$this->'.$fieldName.'];';
								$res.='return '.$array[0].'::'.$array[1].'()[$this->'.$fieldName.'];';
							}else{
								$res.='$v=$this->'.$fieldName.';';
								foreach($array as $key=>$value) $res.='if($v==='.UPhp::exportCode($key).')return _tF('.UPhp::exportCode($matches[2]).','.UPhp::exportCode($fieldName.'.Enum.'.$value).','.UPhp::exportCode($value).');';
								$res.='return \'\';';
							}
							$res.='}';
							
							if($this->enhanced->getMinCoreVersion() < 4){
								$pluralizedUnderscoredWords = UInflector::pluralizeUnderscoredWords($fieldName);
								if($pluralizedUnderscoredWords !== $fieldName){
									$res.="\n".'public static function '.$pluralizedUnderscoredWords.'List(){ return static::'.$fieldName.'List(); }';
								}
							}
							/*foreach($array as $key=>$value){
								$res.='public function is'.ucfirst($fieldName).'{return $this->'.$fieldName.'==='.$key.'}';
							}*/
						}
						$classBeforeContent.=$res;
					}
					
					
					$specialFieldsSetData=$specialFieldsGetData=$specialFieldsBefore='';
					foreach($specialFields as $name=>$type){
						if($type==='Boolean'||$type==='BooleanInt'){
							$has=substr($name,0,4)==='has_';
							$camelized=UString::camelize($has?substr($name,4):$name,false);
							$prefix=$has?'has':'is';
							$specialFieldsBefore.="\n".'public function '.$prefix.$camelized.'(){return '.($type==='Boolean'?'$this->'.$name.'!==null&&$this->'.$name.'!==false&&$this->'.$name.'!==0':'$this->'.$name).';}';
							$specialFieldsBefore.="\n".'public function display'.$camelized.'(){ return $this->'.$prefix.$camelized.'() ? '."_tC('Yes') : _tC('No')".'; }';
						}elseif($type==='Json'){
							$fieldName=UString::camelize($name,true);
							$specialFieldsSetData.='if(isset($data[\''.$name.'\'])) $data[\''.$name.'\']=json_decode($data[\''.$name.'\'],true);'
								.' $this->'.$fieldName.'=&$data[\''.$name.'\'];';
							$specialFieldsGetData.='if(isset($d[\''.$name.'\'])){ unset($d[\''.$name.'\']); $d[\''.$name.'\']=json_encode($data[\''.$name.'\']);}';
							$specialFieldsBefore.="\n".'public $'.$fieldName.';';
						}
					}
				
					return 'class '.$matches[2].$matches[3].'{public static $__className=\''.$matches[2].'\',$__modelInfos,$__PROP_DEF,$_relations,'
						.'$__tableName='."'".$annotations['TableName'][0][0]."'".',$__alias='."'".$annotations['TableAlias'][0][0]."'"
						.',$__pluralized='."'".UInflector::pluralizeCamelizedLastWord($matches[2])."'"
						.($dbName?',$__dbName=\''.$dbName.'\',$__modelDb':'')
						.(isset($annotations['DisplayField'][0][0])?',$__displayField='.(count($annotations['DisplayField'][0])===1? '\''.$annotations['DisplayField'][0][0].'\'' 
																										: UPhp::exportCode($annotations['DisplayField'][0]) ):'')
						.($orderByField?',$__orderByField=\''.$orderByField.'\'':'')
						.',$__cacheable='.($cacheable?'true':'false')
						.';'
						.(empty($specialFields)?'':
							$specialFieldsBefore
							.(empty($specialFieldsSetData)?'':'public function _setData($data){'.$specialFieldsSetData.'parent::_setData($data);}')
							.(empty($specialFieldsGetData)?'':'public function &_getData(){$data=parent::_getData();$d=$data;'.$specialFieldsGetData.'return $d;}')
						)
						.($createdField||isset($annotations['CreatedBy'])||$createdByField||isset($annotations['Child'])?
							"\n".'public static function QInsert(){return new QInsert(self::$__className,'.($stringCreatedField=($createdField?UPhp::exportString($createdField):'null')).($createdByField?','.UPhp::exportString($createdByField):'').');}'
							."\n".'public static function QInsertSelect(){return new QInsertSelect(self::$__className,'.$stringCreatedField.');}'
							."\n".'public static function QReplace(){return new QReplace(self::$__className,'.$stringCreatedField.');}'
						:'')
						.($updatedField||isset($annotations['Child'])?/*'protected function _beforeUpdate(){if(!isset($this->'.$updatedField.')) $this->'.$updatedField.'=date(\'Y-m-d H:i:s\');return parent::_beforeUpdate();}'*/
						"\n".'public static function QUpdate(){return new QUpdate(self::$__className,'.($stringUpdatedField=($updatedField?UPhp::exportString($updatedField):'null')).');}'
						."\n".'public static function QUpdateOne(){return new QUpdateOne(self::$__className,'.$stringUpdatedField.');}'
						:'')
						.$classBeforeContent;
						//.implode('',array_map(function(&$field){return 'public function &'.UString::camelize($field,false).'($v){$this->_set('.UPhp::exportString($field).',$v);return $this;}';},array_keys($modelFile->_fields)))
						
				},$content,1);
				
				$contentInfos['colsName']=array_keys($contentInfos['columns']);
				
				$relations=&$contentInfos['relations'];
				
				foreach(array('hasMany','belongsTo','hasOne','hasOneThrough','hasManyThrough','belongsToType') as $relType){
					$content=preg_replace_callback(PhpFile::regexpArrayField($relType),function($matches2) use(&$relations,&$relType,&$contentInfos){
						$matches2[1]=preg_replace('/\s*\b([A-Z][A-Za-z\_]+)\s*\=\>/','"$1"=>',$matches2[1]);
						$eval=dev_eval('return '.$matches2[1]);
						if(empty($eval) && !empty($matches2[1]) && !is_array($eval))
							$this->throwException('Failed to eval :'."\n".$matches2[1]);
						foreach($eval as $key=>&$relation){
							if(is_numeric($key)){ $key=$relation; $relation=array(); }
							$relation['reltype']=$relType;
							$relations[$key]=$relation;
						}
						if($relType==='belongsTo') $contentInfos['belongsToRelations'][]=$key;
						return '';
					},$content);
				}
				
				BHistory_build::_history_beforeEnd($modelFile,$contentInfos,$content);
				$this->callTraitsBuilder('beforeEnd',array($modelFile,&$contentInfos,&$content));
			}else{
				// MongoDB
				$contentInfos=array('indexes'=>array());
				$content=preg_replace_callback(self::REGEXP_CLASS,function($matches) use(&$modelFile,&$contentInfos){
					$annotations=empty($matches[1])?array():PhpFile::parseAnnotations($matches[1],true);
					$modelFile->_className=$matches[2];
					$dbName=isset($annotations['Db'])?$annotations['Db'][0][0]:false;
					
					$indexes=&$contentInfos['indexes'];
					if(isset($annotations['Index'])){
						foreach($annotations['Index'] as $index) $indexes[0][]=$index;
					}
					if(isset($annotations['IndexUnique'])){
						foreach($annotations['IndexUnique'] as $index) $indexes[1][]=$index;
					}
					
					return 'class '.$matches[2].$matches[3].'{public static $__className=\''.$matches[2].'\',$__collection'
								.($dbName?',$__dbName=\''.$dbName.'\',$__modelDb':'')
								.';'
					;
				},$content);
			}
			$this->_contentInfos='<?php return '.UPhp::exportCode($contentInfos).';';
			$content.=/*'define(\''.$matches[2].'\',\''.$matches[2].'\');'.*/$matches[2].'::init("'.$matches[2].'");';
		}
		return $this->addExecuteToQueries($content,true);
	}

	private function writeInfos(){
		if(empty($this->_className)) return;
		$dirname=$this->currentDestFile->getPath();
		while(basename(($dirname=dirname($dirname))) != 'models');
		
		$file=new File($filename=$dirname.'/infos/'.$this->_className);
		$file->mkdirs();
		$file->write($this->_contentInfos);
	}

	public function getEnhancedDevContent(){
		$this->writeInfos();
		return parent::getEnhancedDevContent();
	}

	public function getEnhancedProdContent(){
		$this->writeInfos();
		return parent::getEnhancedProdContent();
	}
	
	
	public $_fields=array();
	public $_pks=array();
	private function fields($matches){
		$fieldName=$matches[2];
		
		$annotations=PhpFile::parseAnnotations($matches[1],false,null,true);
		$this->_fields[$fieldName]=$annotations;

		return empty($matches[3])?'':$matches[0];
	}
	private function mfields($matches){
		$matches2=array();
		preg_match_all('/\/\*\*([^;{]*)\*\/\s+\$([A-Za-z0-9\s_]+)[\s|,|;]/Ums',$matches[1],$matches2);
		foreach($matches2[1] as $key=>$comm) $this->fields(array(1=>$comm,$matches2[2][$key]));
	}
	
	
	public static function initFolder($folder,$config){
		$d=new Folder($folder->getPath().'models/infos');
		//if($d->exists()) $d->moveTo($tmpFolder.'models/infos');
		if(!$d->exists()) $d->mkdirs(0775);
	}
	public static function fileDeleted($file,$devAndProd=null){
		$fileName=$file->getName(); $fileNameWithoutExt=substr($fileName,0,-4);
		$devAndProdFiles=array();
		if(isset($devAndProd['dev'])) $devAndProdFiles[]=$devAndProd['dev'];
		if(isset($devAndProd['prod'])) $devAndProdFiles[]=$devAndProd['prod'];
		
		foreach($devAndProdFiles as $devOrProdFile){
			$modelsFolder=dirname($devOrProdFile).'/';
			UFile::rm($modelsFolder.'infos/'.$fileNameWithoutExt);
			UExec::exec('rm '.UExec::rmEscape($modelsFolder.$fileNameWithoutExt.'__').'*');
			UExec::exec('rm '.UExec::rmEscape($modelsFolder.'infos/'.$fileNameWithoutExt.'__').'*');
		}
	}
	
	/*public static function afterEnhanceApp($hasOldDef,$newDef,$appDir,$dev,$prod){
		if($hasOldDef){
			$changes=empty($newDef['changes']) ? false : $newDef['changes'];
			
			// MODELS
			$modelChanges=array();
			if($changes){
				if(!empty($changes['Model'])){
					$modelChanges=array();
					foreach($changes['Model'] as $mfile){
						$mfile=new File($mfile);
						$modelChanges[]=substr($mfile->getName(),0,-4);
					}
				}
			}
			
			$path_part2='models/infos/';
			foreach(array($tmpDev=>$dev->getPath(),$tmpProd=>$prod->getPath()) as $src=>$dest){
				$f=new Folder($src.$path_part2);
				if(!$f->exists()) continue;
				foreach($f->listAll() as $file){
					$filename=$file->getName();
					$destFile=new File($dest.$path_part2.$filename);
					if(!$destFile->exists() && !in_array(($filename=rtrim($filename,'_')),$modelChanges) && file_exists($dest.'models/'.$filename.'.php'))
						$file->moveTo($destFile->getPath());
				}
			}
		}
	}*/
}