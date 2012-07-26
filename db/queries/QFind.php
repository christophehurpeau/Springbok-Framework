<?php
/**
 * SELECT [STRAIGHT_JOIN]
       [SQL_SMALL_RESULT] [SQL_BIG_RESULT] [SQL_BUFFER_RESULT]
       [SQL_CACHE | SQL_NO_CACHE] [SQL_CALC_FOUND_ROWS] [HIGH_PRIORITY]
       [DISTINCT | DISTINCTROW | ALL]
    select_expression,...
    [INTO {OUTFILE | DUMPFILE} 'nom_fichier' export_options]
    [FROM table_references
      [WHERE where_definition]
      [GROUP BY {unsigned_integer | nom_de_colonne | formula} [ASC | DESC], ...
      [HAVING where_definition]
      [ORDER BY {unsigned_integer | nom_de_colonne | formula} [ASC | DESC] ,...]
      [LIMIT [offset,] lignes]
      [PROCEDURE procedure_name(argument_list)]
      [FOR UPDATE | LOCK IN SHARE MODE]]

 * @author Christophe Hurpeau
 */
abstract class QFind extends QSelect{
	protected static $FORCE_ALIAS=false;
	protected $fields=array(0=>null),$alias,$joins=array(),$with=array(),$queryResultFields,$objData,$joinData,$objFields,$allFields=array();

	//private $equalsFieldsInConditions;
	//public function &calcFoundRows(){$this->calcFoundRows=true;return $this;}

	public function __construct($modelName){
		parent::__construct($modelName);
		$this->alias=$modelName::$__alias;
	}


	/** @return QSelect */
	public function fields($fields){$this->fields[0]=explode(',',$fields);return $this;}
	/** @return QSelect */
	public function field($field){$this->fields[0]=array($field);return $this;}
	/** @return QSelect */
	public function setFields($fields,$params=NULL){$this->fields[0]=$fields;/* DEV */if($params !== NULL) throw new Exception('NOT SUPPORTED !'); /* /DEV */return $this;}
	public function noFields(){ $this->fields[0]=false; return $this; }
	
	
	public function addField($field){$this->fields[0][]=$field;return $this;}
	public function addFieldWithAlias($field,$alias){$this->fields[0][$field]=$alias;return $this;}
	
	public function getFields(){return $this->fields[0]; }
	

	public function alias($alias){ $this->alias=$alias; return $this; }

	public function join($modelName,$fields=null,$conditions=array(),$options=array()){
		$this->_join(',',$modelName,$fields,$conditions,$options);
		return $this;
	}
	public function leftjoin($modelName,$fields=null,$onConditions=array(),$options=array()){
		$this->_join(QSelect::LEFT,$modelName,$fields,$onConditions,$options);
		return $this;
	}
	public function innerjoin($modelName,$fields=null,$onConditions=array(),$options=array()){
		$this->_join(QSelect::INNER,$modelName,$fields,$onConditions,$options);
		return $this;
	}
	public function rightjoin($modelName,$fields=null,$onConditions=array(),$options=array()){
		$this->_join(QSelect::RIGHT,$modelName,$fields,$onConditions,$options);
		return $this;
	}
	public function fulljoin($modelName,$fields=null,$onConditions=array(),$options=array()){
		$this->_join(' FULL JOIN ',$modelName,$fields,$onConditions,$options);
		return $this;
	}
	/** options: withoutFields, fieldsInModel, dataName */
	private function _join($type,$modelName,$fields,$onConditions,$options){
		$join=array('type'=>$type,'modelName'=>$modelName,'onConditions'=>$onConditions)+$options
			+array('fieldsInModel'=>false,'dataName'=>lcfirst($modelName),'isCount'=>false,'fields'=>$fields);
		if(!isset($join['alias'])) $join['alias']=$modelName::$__alias;
		$this->joins[$join['alias']]=$join;
		$this->fields[$join['alias']]=$fields;
	}
	
	public function setAllWith($with){
		foreach($with as $key=>&$options){
			if(is_numeric($key)){ $key=$options; $options=array();}
			$this->_addWithToQuery($key,$options);
		};
		return $this;
	}
	public function with($with,$options=array()){if(!is_array($options)) $options=array('fields'=>$options); $this->_addWithToQuery($with,$options);return $this;}
	
	public function withLang($options=array(),$lang=false){
		if($lang===false) $lang=CLang::get();
		if(is_string($options)) $options=array('fields'=>$options);
		$options+=array('fieldsInModel'=>true,'join'=>true,'onConditions'=>array('lang'=>$lang));
		$mL=$this->modelName.'Lang';
		$this->_addWithToQuery($mL,$options);
		return $this;
	}

	public function withForce($with,$options=array()){
		if(is_string($options)) $options=array('fields'=>$options);
		elseif(!isset($options['fields'])) $options['fields']=false;
		$options['join']=true;
		$this->_addWithToQuery($with,$options);
		return $this;
	}
	public function withParent($options=array()){
		return $this->with('Parent',$options);
	}
	
	protected function _addWithToQuery($key,$options){
		$foptions=self::_addWith($this->with,$key,$options,$this->modelName);
		if($this->_addWithInJoin($this->modelName,$this->alias,$key,$foptions)===false) return;
		unset($this->with[$key]);
		if(isset($foptions['with'])) $this->_recursiveWith($foptions['with'],$foptions['modelName'],$foptions['alias']);
	}
	public static function _addWith(&$withArray,&$key,&$options,$modelName){
		/* DEV */if(!isset($modelName::$_relations[$key])) throw new Exception($modelName.' does not have a relation named "'.$key.'"'."\n".'Known relations : '.implode(', ',array_keys($modelName::$_relations))); /* /DEV */
		$relation=$modelName::$_relations[$key];
		/* DEV */
		if(!is_array($options)) throw new Exception('options is not array : '.print_r($options,true));
		if(!is_array($relation)) throw new Exception('relation is not array : '.print_r($relation,true));
		/* /DEV */
		$foptions=$options+$relation;
	
		if(isset($foptions['fields']) && is_string($foptions['fields'])) $foptions['fields']=explode(',',$foptions['fields']);
		if(isset($foptions['with'])) foreach($foptions['with'] as $kW=>&$opW){ if(is_int($kW)){unset($foptions['with'][$kW]); $kW=$opW;$opW=array();} self::_addWith($foptions['with'],$kW,$opW,$foptions['modelName']); }
		return $withArray[$key]=$foptions;
	}
	
	
	public function _setWith(&$with){
		foreach($with as $key=>&$options){
			if($this->_addWithInJoin($this->modelName,$this->alias,$key,$options)===false) $this->with[$key]=$options;
			else{
				if(isset($options['with'])) $this->_recursiveWith($options['with'],$options['modelName'],$options['alias']);
			}
		}
		return $this;
	}
	public function _setJoin(&$join){$this->joins=&$join;return $this;}
	
	private function _addWithInJoin($modelName,$modelAlias,&$key,&$join,$inRecursive=false){
		// $join['join'] can be false (fore to not join), true (force to join) or null (auto)
		if($join['join']===false || !($join['join']===true || $join['reltype']==='belongsTo' || $join['reltype']==='hasOne' || $join['reltype']==='hasOneThrough' || $join['isCount']===true)) return false;
		$joinModelName=$join['modelName'];
		if($modelName::$__dbName!==$joinModelName::$__dbName && !$modelName::$__modelDb->isInSameHost($joinModelName::$__modelDb)) return false;
		//if(!empty($join['with'])) return false; //TODO should be handled someplace else because here generate a lot of requests... 
		if($join['reltype'] === 'hasOneThrough' || $join['reltype'] === 'hasManyThrough'){
			$lastAlias=$modelAlias;$lastModelName=$this->modelName;
			foreach($join['joins'] as $relName=>$options){
				if(is_int($relName)){ $relName=$options; $options=array(); }
				$options+=array('fields'=>false,'join'=>true);
				/* DEV */if(!isset($lastModelName::$_relations[$relName])) throw new Exception($lastModelName.' does not have a relation named "'.$relName.'"'."\n".'Known relations : '.implode(', ',array_keys($lastModelName::$_relations))); /* /DEV */
				$options+=$lastModelName::$_relations[$relName];
				
				$onConditions=array($lastAlias.'.'.$options['foreignKey'].'='.$options['alias'].'.'.$options['associationForeignKey']);
				if(isset($options['onConditions'])) $options['onConditions']=array_merge($options['onConditions'],$onConditions);
				else $options['onConditions']=$onConditions;
				
				$lastAlias=$options['alias'];$lastModelName=$options['modelName'];
				$this->joins[$lastAlias]=$options;
			}
			
			$options=$join+$lastModelName::$_relations[$key];
			$onConditions=array($lastAlias.'.'.$options['foreignKey'].'='.$options['alias'].'.'.$options['associationForeignKey']);
			if(isset($options['onConditions'])) $options['onConditions']=array_merge($options['onConditions'],$onConditions);
			else $options['onConditions']=$onConditions;
			$options['reltype']=substr($join['reltype'],0,-7);
			unset($options['joins']);
			$this->_addJoinInJoin($options);
		}else{
			if($join['foreignKey']!==false){
				$onConditions=array($modelAlias.'.'.$join['foreignKey'].'='.$join['alias'].'.'.$join['associationForeignKey']);
				if(isset($join['onConditions'])) $join['onConditions']=array_merge($join['onConditions'],$onConditions);
				else $join['onConditions']=$onConditions;
			}
			$this->_addJoinInJoin($join);
		}
		return true;
	}

	private function _addJoinInJoin(&$join){
		if(is_string($join['fields'])) $join['fields']=explode(',',$join['fields']);
		$this->fields[$join['alias']]=$join['fields'];
		$this->joins[$join['alias']]=$join;
	}
	
	private function _recursiveWith(&$with,$modelName,$alias){
		foreach($with as $key=>&$join){
			if($this->_addWithInJoin($modelName,$alias,$key,$join)===false) continue;
			unset($with[$key]);
			if(isset($join['with'])) $this->_recursiveWith($join['with'],$join['modelName'],$join['alias'],true);
		}
	}
	
	
	public function reexecute(){
		$this->objFields=$this->queryResultFields=$this->objData=array();
		return $this->execute();
	}
	
	public function _toSQL($currentDb=NULL){
		$modelName=$this->modelName;
		
		$modelAlias=(!empty($this->with) || !empty($this->joins)?$this->alias:null); 
		
		$fieldPrefix=$modelAlias!==null?$modelAlias.'.':'';

		$sql=$this->_SqlStart();
		
		$fields=$this->fields;
		$modelFields=&$fields[0];
		if($modelFields!==null){
			if($modelFields){
				foreach($modelFields as $field=>$alias){
					if(is_int($field)){ $field=$alias; $alias=false;}
					/*if($aspos=strpos($field,' AS')){
						$fieldAlias=$this->allFields['_'][]=substr($field,$aspos+4);
						$sql.=substr($field,0,$aspos).' '.$this->_db->formatField($fieldAlias);
					}else */
					if(($fpos=strpos($field,'('))!==false){
						$sql.=$field;
					}elseif(substr($field,0,4)==='CASE'){
						$fpos=true;
						$sql.=$field;
					}elseif(substr($field,0,8)==='DISTINCT'){
						$field=substr($field,9);
						$sql.='DISTINCT '.$fieldPrefix.$this->_db->formatField($field);
					}else $sql.=is_numeric($field)?$field:$fieldPrefix.$this->_db->formatField($field);
					if($alias!==false){
						$this->objFields[]=$alias;
						//$this->objData[$alias]=null;
						$this->queryResultFields[]=&$this->objData[$alias];
						if($fpos!==false||static::$FORCE_ALIAS){
							$modelAlias=$this->alias;
							$sql.=' AS '.$this->_db->formatField($alias);
						}
					}else{
						$this->objFields[]=$field;
						//$this->objData[$field]=null;
						$this->queryResultFields[]=&$this->objData[$field];
					}
					$sql.=',';
				}
			}
		}else{
			$this->objFields=$modelName::$__modelInfos['colsName'];
			foreach($this->objFields as $field){
				//$this->objData[$field]=null;
				$this->queryResultFields[]=&$this->objData[$field];
			}	
			$sql.=$fieldPrefix.'*,';
		}
		
		unset($fields[0]);
		
		if(!empty($fields)){
			$hasCount=false;
			foreach($fields as $joinKey=>$joinFields){
				$join=$this->joins[$joinKey];
				$joinModelName=$join['modelName'];
				if(isset($join['fields'])){
					if($join['fields']!==false){
						foreach($join['fields'] as $field=>$alias){
							if(is_int($field)){ $field=$alias; $alias=false;}
							if($fpos=strpos($field,'(')){
								$sql.=$field;
							}else{
								if(substr($field,0,8)==='DISTINCT'){
									$field=substr($field,9);
									$sql.='DISTINCT '.$join['alias'].'.'.$this->_db->formatField($field);
								}else{
									$sql.=is_int($field)?$field:$join['alias'].'.'.$this->_db->formatField($field);
								}
							}
							if($alias){
								$this->allFields[$join['alias']][]=$alias;
								//$this->joinData[$join['alias']][$alias]=null;
								$this->queryResultFields[]=&$this->joinData[$join['alias']][$alias];
								if($fpos||static::$FORCE_ALIAS) $sql.=' AS '.$this->_db->formatField($alias);
							}else{
								$this->allFields[$join['alias']][]=$field;
								//$this->joinData[$join['alias']][$field]=null;
								$this->queryResultFields[]=&$this->joinData[$join['alias']][$field];
							}
							$sql.=',';
						}
					}
				}elseif($join['isCount']){
					$hasCount=true;
					$sql.='COUNT(';
					if($join['isDistinct']) $sql.='DISTINCT ';
					if($join['isCount']===true) $sql.=$join['alias'].'.'.$joinModelName::_getPkName();
					else $sql.=$join['isCount'];
					$sql.=') AS '.$this->_db->formatField($join['dataName']).',';
					$this->objFields[]=$join['dataName'];
					//$this->objData[$join['dataName']]=null;
					$this->queryResultFields[]=&$this->objData[$join['dataName']];
				}else{
					$this->allFields[$join['alias']]=$joinModelName::$__modelInfos['colsName'];
					foreach($this->allFields[$join['alias']] as $field){
						//$this->joinData[$join['alias']][$field]=null;
						$this->queryResultFields[]=&$this->joinData[$join['alias']][$field];
					}
					$sql.=$join['alias'].'.*,';
				}
			}
			if($hasCount && empty($this->groupBy)) $this->groupBy=array($fieldPrefix.$modelName::_getPkName());
		}
		
		$sql=substr($sql,0,-1).' FROM '.($currentDb!==null && $currentDb->getDbName() !== $modelName::$__modelDb->getDbName()?$modelName::$__modelDb->getDbName().'.':'').$modelName::_fullTableName();
		if($modelAlias!==null) $sql.=' '.$modelAlias;
		
		if(!empty($this->joins)){
			foreach($this->joins as &$join){
				$sql.=$join['type'].($modelName::$__dbName!==$join['modelName']::$__dbName || ($currentDb!==NULL && $currentDb->getDbName() !== $join['modelName']::$__modelDb->getDbName())?$join['modelName']::$__modelDb->getDbName().'.':'')
					.$join['modelName']::_fullTableName().' '.$join['alias'];
				if(!empty($join['onConditions'])){
					$sql.=' ON ';
					$sql=$this->_condToSQL($join['onConditions'],'AND',$sql);
				}
			}
		}
		
		if(!empty($this->where)){
			$sql.=' WHERE ';
			$sql=$this->_condToSQL($this->where,'AND',$sql,$fieldPrefix);
		}

		$sql=$this->_afterWhere($sql,$fieldPrefix);
		return $sql;
	}

	/*
	protected function createEqualsFields(){
		$this->equalsFieldsInConditions=array();
	}
	*/
	
	public function _createObject($row){
		$data=array();
		$pdoI=0;
		if($this->objFields){
			foreach($this->objFields as &$fieldName) $data[$fieldName]=$row[$pdoI++];
			/*if($this->fields !== NULL && $this->addByConditions !== false){
				foreach($this->addByConditions as $fieldName=>&$value) $data[$fieldName]=$value;
			}*/
		}
		$obj=CBinder::_bindObjectFromDB($this->modelName,$data);
		foreach($this->allFields as $alias=>&$fields){
			$join=$this->joins[$alias];
			$data=array();
			foreach($fields as &$fieldName) $data[$fieldName]=$row[$pdoI++];
			$data=CBinder::_bindObjectFromDB($join['modelName'],$data);
			if($join['fieldsInModel']){
				foreach($data as $fieldname=>$v) $obj->_set($fieldname,$v);
			}else $obj->$join['dataName']=$data;
		}
		return $obj;
	}
	
	public function _createObj(){
		$type=$this->modelName;
		$obj=new $type();
		if($this->objData) $obj->_copyData($this->objData);
		if($this->joinData !== NULL) foreach($this->joinData as $alias=>&$joinData){
			$join=$this->joins[$alias];
			if($join['fieldsInModel']){
				if($join['fieldsInModel']===true || !isset($this->joins[$join['fieldsInModel']])) foreach($joinData as $key=>$val) $obj->_set($key,$val);//copy
				else{
					$objJoined=$obj->_get($this->joins[$join['fieldsInModel']]['dataName']);
					foreach($joinData as $key=>$val) $objJoined->_set($key,$val);//copy
				}
			}else{
				$type=$join['modelName'];
				/* DEV */ if(!is_string($type)) throw new Exception('Type is not a string : '.short_debug_var($type)); /* /DEV */
				$joinObj=new $type();
				$joinObj->_copyData($joinData);
				$obj->$join['dataName']=$joinObj;
			}
		}
		return $obj;
	}
	
	public function getModelFields(){
		$modelFields=array();$modelName=$this->modelName;
		if($this->objFields===NULL) foreach($modelName::$__modelInfos['colsName'] as $field) $modelFields[$field]=$modelName;
		elseif($this->objFields!==false) foreach($this->objFields as $field) $modelFields[$field]=$modelName;
		foreach($this->allFields as $alias=>&$fields){
			$join=$this->joins[$alias];
			foreach($fields as $field) $modelFields[$field]=$join['modelName'];
		}
		return $modelFields;
	}
	
	
	protected function _afterQuery_obj(&$obj){
		self::AfterQuery_obj($this->with,$obj);
	}
	
	
	public static function createWithQuery(&$obj,&$w,$query=null){
		if($query===null && $w['isCount']) $query=new QCount($w['modelName']);
		switch($w['reltype']){
			case 'belongsTo':
			case 'hasOne':
				$objField = $w['foreignKey'];
				$resField = $w['associationForeignKey'];
				
				return self::_createBelongsToAndHasOneQuery($query,$w,$obj->_get($objField),$resField);
			case 'hasMany':
				$objField = $w['foreignKey'];
				$resField = $w['associationForeignKey'];
				
				return self::_createHasManyQuery($query,$w,$obj->_get($objField),$resField);
			case 'hasOneThrough':
				$withMore=array(); reset($w['joins']);
				self::_recursiveThroughWith($withMore,$w['joins'],$w);
				$rel=$obj::$_relations[$w['relName']];
				
				$objField = $rel['foreignKey'];
				$resField = $rel['associationForeignKey'];
				
				return self::_createBelongsToAndHasOneQuery($query,$w,$obj->_get($objField),$resField,false,$withMore['with'],$rel['alias']);
			case 'hasManyThrough':
				$withMore=array(); reset($w['joins']);
				self::_recursiveThroughWith($withMore,$w['joins'],$w);
				$rel=$obj::$_relations[$w['relName']];
				
				$objField = $rel['foreignKey'];
				$resField = $rel['associationForeignKey'];
				
				return self::_createHasManyQuery($query,$w,$obj->_get($objField),$resField,false,$withMore['with'],$rel['alias']);
			case 'belongsToType':
				$type=$obj->_get($w['fieldType']);
				if(empty($w['types'][$type])) return false;
				
				$objField = $w['foreignKey'];
				$resField = $w['associationForeignKey'];
				
				return self::_createBelongsToAndHasOneQuery($query,$w['relations'][$w['types'][$type]],$obj->_get($objField),$resField);
			default:
				throw new Exception('Unknown relation; '.$w['reltype']);
		}
	}
	
	private static function AfterQuery_obj(&$with,$obj){
		foreach($with as $key=>&$w){
			$query=self::createWithQuery($obj,$w);
			if($query!==false){
				$res=$query->execute();
				$obj->_set($w['dataName'],$res);
			}
			unset($with[$key]);
		}
	}

	private static function _recursiveThroughWith(&$with,&$joins,$w=array()/*,&$lastModelName*/){
		$relName=key($joins); $options=current($joins);
		if(is_int($relName)){ $relName=$options; $options=array(); }
		$options+=array('fields'=>false,'join'=>true);
		/* DEV *///if(!isset($lastModelName::$_relations[$relName])) throw new Exception($lastModelName.' does not have a relation named "'.$relName.'"'."\n".'Known relations : '.implode(', ',array_keys($lastModelName::$_relations))); /* /DEV */
		//$options+=$lastModelName::$_relations[$relName];
		if(isset($w['withOptions'][$relName])) $options=$w['withOptions'][$relName]+$options;// can override 'fields'
		$with['with']=array($relName=>$options);
		if(next($joins)===false) return;
		self::_recursiveThroughWith($with['with'][$relName],$joins/*,$lastModelName::$_relations[$relName]['modelName']*/);
	}
	
	protected function _afterQuery_objs($objs){
		self::AfterQuery_objs($this->with,$objs);
	}
	
	private static function AfterQuery_objs(&$with,$objs){
		if(empty($objs)) return;
		foreach($with as $key=>&$w){
			switch($w['reltype']){
				case 'belongsTo':
				case 'hasOne':
					$objField = $w['foreignKey'];
					$resField = $w['associationForeignKey'];
					
					$values=self::_getValues($objs,$objField);
					if(!empty($values)){
						$listRes = self::_createHasManyQuery($w['fieldsInModel']===true?new QFindRows($w['modelName']):null,$w,$values,$resField,true)->execute();
						
						if($listRes){
							if($w['fieldsInModel']===true){
								foreach($objs as $obj)
									foreach($listRes as $res)
										if($res[$resField] == $obj->_get($objField)){
											foreach($res as $k=>$v)
												if($k!==$resField) $obj->_set($k,$v);
											break;
										}
							}else{
								foreach($objs as $obj)
									foreach($listRes as $res)
										if ($res->_get($resField) == $obj->_get($objField)){
											$obj->_set($w['dataName'],$res);
											break;
										}
							}
						}
					}
					unset($with[$key]);
					break;
				case 'hasMany':
					$objField = $w['foreignKey'];
					
					$values=self::_getValues($objs,$objField);
					if(!empty($values)){
						$resField = $w['associationForeignKey'];
						
						$oneField=count($w['fields'])===1?$w['fields'][0]:false;
						$listRes=self::_createHasManyQuery(null,$w,$values,$resField,true)->execute();
						if($listRes) foreach($objs as $key=>$obj){
							$listObjsRes=array();
							foreach($listRes as &$res){
								if($res->_get($resField) == $obj->_get($objField)){
									if($oneField===false)
										$listObjsRes[] = $res;
									else
										$listObjsRes[]=$res->_get($oneField);
								}
							}
							$obj->_set($w['dataName'],$listObjsRes);
						}
					}
					break;
					
				case 'hasManyThrough':
					reset($objs);
					$obj=current($objs);
					$rel=$obj::$_relations[$w['relName']];
					
					$objField = $rel['foreignKey'];
					
					$values=self::_getValues($objs,$objField);
					if(!empty($values)){
						$withMore=array(); reset($w['joins']);
						self::_recursiveThroughWith($withMore,$w['joins'],$obj::$__className);
						
						$resField = $rel['associationForeignKey'];
						$oneField=count($w['fields'])===1 && !isset($w['with'])?$w['fields'][0]:false;
						
						/* DEV */if(empty($w['fields'])) throw new Exception('You must specify fields...');/* /DEV */
						$w['fields']['('.$rel['alias'].'.`'.$resField.'`)']=$resField;
						
						if(isset($w['groupBy'])) $w['groupBy']=$rel['alias'].'.'.$resField.','.$w['groupBy'];
						
						$listRes=self::_createHasManyQuery(null,$w,$values,$resField,false,$withMore['with'],$rel['alias'])->execute();
						if($listRes!==false){
							foreach($objs as $k=>&$obj){
								$listObjsRes=array();
								foreach($listRes as $res){
									if($res->_get($resField) == $obj->_get($objField)){
										if($oneField===false) $listObjsRes[] = $res;
										else $listObjsRes[]=$res->_get($oneField);
									}
								}
								$obj->_set($w['dataName'],$listObjsRes);
							}
							unset($obj);
						}
					}
					
					break;
					
				case 'belongsToType':
					$types=array();
					foreach($objs as $k=>&$obj){
						$type=$obj->_get($w['fieldType']);
						if(empty($w['types'][$type])) continue;
						if($type!==null) $types[$w['types'][$type]][]=$obj;
					}
					
					foreach($types as $relName=>&$objsTyped){
						$with=array($w['relations'][$relName]);
						if(!empty($with[0]['with'])){
							$with2=array();
							foreach($with[0]['with'] as $k=>&$options){
								if(is_numeric($k)){ $k=$options; $options=array();}
								self::_addWith($with2,$k,$options,$with[0]['modelName']);
							}
							$with[0]['with']=$with2;
						}
						
						self::AfterQuery_objs($with,$objsTyped);
					}
					
					break;
			default:
					throw new Exception('Unknown relation; '.$w['reltype']);
			}
		}
	}

	public static function findWith($model,$key,$options){
		$with=array();
		self::_addWith($with,$key,$options,$model::$__className);
		self::AfterQuery_obj($with,$model);
	}
	
	public static function findWithPaginate($paginateClass,$model,$key,$options){
		$w=array();
		self::_addWith($w,$key,$options,$model::$__className);
		$query=self::createWithQuery($model,$w[$key]);
		if($query===false) return false;
		$res=$paginateClass::create($query);
		$model->_setRef($w[$key]['dataName'],$res); //not executed, but should be a reference to the variable
		unset($w[$key]);
		return $res;
	}

	public static function findMWith($model,$mwith){
		$with=array();$modelName=$model::$__className;
		foreach($mwith as $key=>&$options){
			if(is_numeric($key)){ $key=$options; $options=array();}
			self::_addWith($with,$key,$options,$modelName);
		}
		self::AfterQuery_obj($with,$model);
	}
	
	private static function _getValues($objs,$objField){
		$values=array();
		foreach($objs as &$obj){
			$value=$obj->_get($objField);
			if($value !== NULL) $values[]=$value;
		}
		return array_unique($values);
	}
	
	private static function _createBelongsToAndHasOneQuery($query,$w,$values,&$resField,$addResField=false,$moreWith=NULL,$fieldTableAlias=NULL){
		if($query===null) $query=new QFindOne($w['modelName']);
		$query->setFields($addResField ? self::_addFieldIfNecessary($w['fields'],$resField) : $w['fields']);
		if(isset($w['where'])) $where=$w['where']; else $where=array();
		if($fieldTableAlias !== NULL) $resField=$fieldTableAlias.'.'.$resField;
		$where[$resField]=$values;
		$query->where($where);
		if(isset($w['with'])) $query->_setWith($w['with']);
		if($moreWith!==NULL) $query->setAllWith($moreWith);
		return $query;
	}
	
	private static function _createHasManyQuery($query,$w,$values,$resField,$addResField=false,$moreWith=NULL,$fieldTableAlias=NULL){
		if($query===null){
			if($addResField===false && count($w['fields'])===1 && !isset($w['with'])) $query=new QFindValues($w['modelName']);
			else $query = new QFindAll($w['modelName']);
		}
		$query->setFields($addResField ? self::_addFieldIfNecessary($w['fields'],$resField) : $w['fields']);
		if(isset($w['where'])) $where=$w['where']; else $where=array();
		if($fieldTableAlias !== NULL) $resField=$fieldTableAlias.'.'.$resField;
		$where[$resField]=$values;
		$query->where($where);
		if(isset($w['orderBy'])) $query->orderBy($w['orderBy']);
		if(isset($w['groupBy'])) $query->groupBy($w['groupBy']);
		if(isset($w['with'])) $query->_setWith($w['with']);
		if(isset($w['limit'])) $query->limit($w['limit']);
		if($moreWith!==NULL) $query->setAllWith($moreWith);
		if(isset($w['groupResBy'])) $query->groupResBy($w['groupResBy']);
		if(isset($w['tabResKey'])) $query->tabResKey($w['tabResKey']);
		return $query;
	}
	
	private static function _addFieldIfNecessary(&$fields,$field){
		if(empty($fields) || in_array($field,$fields)) return $fields;
		$fields[]=$field;
		return $fields;
	}
}