<?php
/**
 * Abstract SELECT Query
 * 
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
	
	protected $fields=array(0=>null),$alias,$joins=array(),$with=array(),
			$queryResultFields,$objData,$joinData,$objFields,$allFields=array();

	//private $equalsFieldsInConditions;
	//public function &calcFoundRows(){$this->calcFoundRows=true;return $this;}
	
	/**
	 * @param string
	 */
	public function __construct($modelName){
		parent::__construct($modelName);
		$this->alias=$modelName::$__alias;
	}

	/**
	 * @param string fields separated by ,
	 * @return QFind|self
	 */
	public function fields($fields){
		$this->fields[0]=explode(',',$fields);
		return $this;
	}
	
	/**
	 * Set only one field
	 * 
	 * @return QFind|self
	 */
	public function field($field){
		$this->fields[0]=array($field);
		return $this;
	}
	
	/**
	 * Set fields
	 * 
	 * @param array
	 * @return QFind|self
	 */
	public function setFields($fields/*#if DEV */,$params=NULL/*#/if*/){
		$this->fields[0]=$fields;
		/*#if DEV */if($params !== NULL) throw new Exception('NOT SUPPORTED !'); /*#/if*/
		return $this;
	}
	
	/**
	 * Remove fields
	 * 
	 * @return QFind|self
	 */
	public function noFields(){
		$this->fields[0]=false;
		return $this;
	}
	
	/**
	 * Add a field
	 * 
	 * @param string
	 * @return QFind|self
	 */
	public function addField($field){
		$this->fields[0][]=$field;
		return $this;
	}
	/**
	 * Add a aliased field
	 * 
	 * @param string
	 * @param string
	 */
	public function addFieldWithAlias($field,$alias){
		$this->fields[0][$field]=$alias;
		return $this;
	}
	
	/**
	 * @return array
	 */
	public function getFields(){
		return $this->fields[0];
	}
	
	/**
	 * Set current model aliase
	 * @param string
	 * @return QFind|self
	 */
	public function alias($alias){
		$this->alias=$alias;
		return $this;
	}

	/**
	 * @param string
	 * @param string
	 * @param array
	 * @param array
	 * @return QFind|self
	 */
	public function join($modelName,$fields=null,$conditions=array(),$options=array()){
		$this->_join(',',$modelName,$fields,$conditions,$options);
		return $this;
	}
	
	/**
	 * @param string
	 * @param string
	 * @param array
	 * @param array
	 * @return QFind|self
	 */
	public function leftjoin($modelName,$fields=null,$onConditions=array(),$options=array()){
		$this->_join(QSelect::LEFT,$modelName,$fields,$onConditions,$options);
		return $this;
	}
	
	/**
	 * @param string
	 * @param string
	 * @param array
	 * @param array
	 * @return QFind|self
	 */
	public function innerjoin($modelName,$fields=null,$onConditions=array(),$options=array()){
		$this->_join(QSelect::INNER,$modelName,$fields,$onConditions,$options);
		return $this;
	}
	
	/**
	 * @param string
	 * @param string
	 * @param array
	 * @param array
	 * @return QFind|self
	 */
	public function rightjoin($modelName,$fields=null,$onConditions=array(),$options=array()){
		$this->_join(QSelect::RIGHT,$modelName,$fields,$onConditions,$options);
		return $this;
	}
	
	/**
	 * @param string
	 * @param string
	 * @param array
	 * @param array
	 * @return QFind|self
	 */
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
	
	/**
	 * @param array
	 * @return QFind|self
	 */
	public function setAllWith($with){
		foreach($with as $key=>&$options){
			if(is_numeric($key)){ $key=$options; $options=array();}
			$this->_addWithToQuery($key,$options);
		};
		return $this;
	}
	
	/**
	 * @param string
	 * @param array
	 * @return QFind|self
	 */
	public function with($with,$options=array()){
		if(!is_array($options)) $options=array('fields'=>$options);
		$this->_addWithToQuery($with,$options);
		return $this;
	}
	
	/**
	 * @param array
	 * @param string
	 * @return QFind|self
	 */
	public function withLang($options=array(),$lang=false){
		if($lang===false) $lang=CLang::get();
		if(is_string($options)) $options=array('fields'=>$options);
		$options+=array('fieldsInModel'=>true,'join'=>true,'onConditions'=>array('lang'=>$lang));
		$mL=$this->modelName.'Lang';
		$this->_addWithToQuery($mL,$options);
		return $this;
	}
	
	/**
	 * @param string
	 * @param array
	 * @return QFind|self
	 */
	public function withForce($with,$options=array()){
		if(is_string($options)) $options=array('fields'=>$options);
		elseif(!isset($options['fields'])) $options['fields']=false;
		$options['join']=true;
		$this->_addWithToQuery($with,$options);
		return $this;
	}
	
	/**
	 * @param string
	 * @param string
	 * @param array
	 * @return QFind|self
	 */
	public function withField($with,$field,$options=array()){
		$options['fields']=array($field);
		$options['fieldsInModel']=true;
		$this->_addWithToQuery($with,$options);
		return $this;
	}
	
	/**
	 * @param array
	 * @return QFind|self
	 */
	public function withParent($options=array()){
		return $this->with('Parent',$options);
	}
	
	/**
	 * @internal
	 * @param string
	 * @param array
	 * @return void
	 */
	protected function _addWithToQuery($key,$options){
		$foptions=self::_addWith($this->with,$key,$options,$this->modelName);
		if($this->_addWithInJoin($this->modelName,$this->alias,$key,$foptions)===false) return;
		unset($this->with[$key]);
		if(isset($foptions['with'])) $this->_recursiveWith($foptions['with'],$foptions['modelName'],$foptions['alias']);
	}
	
	/**
	 * @internal
	 */
	public static function _addWith(&$withArray,&$key,&$options,$modelName){
		/*#if DEV */if(!isset($modelName::$_relations[$key])) throw new Exception($modelName.' does not have a relation named "'.$key.'"'."\n".'Known relations : '.implode(', ',array_keys($modelName::$_relations))); /*#/if*/
		$relation=$modelName::$_relations[$key];
		/*#if DEV */
		if(!is_array($options)) throw new Exception('options is not array : '.print_r($options,true));
		if(!is_array($relation)) throw new Exception('relation is not array : '.print_r($relation,true));
		/*#/if*/
		$foptions=$options+$relation;
	
		if(isset($foptions['fields']) && is_string($foptions['fields'])) $foptions['fields']=explode(',',$foptions['fields']);
		if(isset($foptions['with'])){
			/*#if DEV */ if(!is_array($foptions['with'])) throw new Exception('$foptions["with"] is not array : '.print_r($foptions['with'],true)); /*#/if*/
			foreach($foptions['with'] as $kW=>&$opW){ if(is_int($kW)){unset($foptions['with'][$kW]); $kW=$opW;$opW=array();} self::_addWith($foptions['with'],$kW,$opW,$foptions['modelName']); }
		}
		return $withArray[$key]=$foptions;
	}
	
	
	/**
	 * @internal
	 * @return QFind|self
	 */
	public function _setWith(&$with){
		foreach($with as $key=>&$options){
			if($this->_addWithInJoin($this->modelName,$this->alias,$key,$options)===false) $this->with[$key]=$options;
			else{
				if(isset($options['with'])) $this->_recursiveWith($options['with'],$options['modelName'],$options['alias']);
			}
		}
		return $this;
	}
	
	/**
	 * @internal
	 * @return QFind|self
	 */
	public function _setJoin(&$join){
		$this->joins=&$join;
		return $this;
	}
	
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
				/*#if DEV */if(!isset($lastModelName::$_relations[$relName])) throw new Exception($lastModelName.' does not have a relation named "'.$relName.'"'."\n".'Known relations : '.implode(', ',array_keys($lastModelName::$_relations))); /*#/if*/
				$options+=$lastModelName::$_relations[$relName];
				
				/*#if DEV */
				if(!isset($options[0])) throw new Exception('$options[0] is not defined: '.print_r($options,true));
				if(isset($options['foreignKey'])) throw new Exception('foreignKey is defined: '.print_r($options,true));
				if(isset($options['associationForeignKey'])) throw new Exception('associationForeignKey is defined: '.print_r($options,true));
				/*#/if*/
				$onConditions=array();
				foreach($options[0] as $foreignKey=>$associationForeignKey)
					$onConditions[]=$lastAlias.'.`'.$foreignKey.'`='.$options['alias'].'.`'.$associationForeignKey.'`';
				if(isset($options['onConditions'])) $options['onConditions']=array_merge($options['onConditions'],$onConditions);
				else $options['onConditions']=$onConditions;
				
				$lastAlias=$options['alias'];$lastModelName=$options['modelName'];
				$this->joins[$lastAlias]=$options;
			}
			
			/*#if DEV */if(!isset($lastModelName::$_relations[$key])) throw new Exception($lastModelName.' does not have a relation named "'.$key.'"'."\n".'Known relations : '.implode(', ',array_keys($lastModelName::$_relations))); /*#/if*/
			$options=$join+$lastModelName::$_relations[$key];
			
			/*#if DEV */
			if(!isset($options[0])) throw new Exception('$options[0] is not defined: '.print_r($options,true));
			if(isset($options['foreignKey'])) throw new Exception('foreignKey is defined: '.print_r($options,true));
			if(isset($options['associationForeignKey'])) throw new Exception('associationForeignKey is defined: '.print_r($options,true));
			/*#/if*/
			
			
			$onConditions=array();
			foreach($options[0] as $foreignKey=>$associationForeignKey)
				$onConditions[]=$lastAlias.'.`'.$foreignKey.'`='.$options['alias'].'.`'.$associationForeignKey.'`';
			if(isset($options['onConditions'])) $options['onConditions']=array_merge($options['onConditions'],$onConditions);
			else $options['onConditions']=$onConditions;
			$options['reltype']=substr($join['reltype'],0,-7);
			unset($options['joins']);
			$this->_addJoinInJoin($options);
		}else{
			/*#if DEV */
			if(!isset($join[0])) throw new Exception('$join[0] is not defined: '.print_r($join,true));
			if(isset($join['foreignKey'])) throw new Exception('foreignKey is defined: '.print_r($join,true));
			if(isset($join['associationForeignKey'])) throw new Exception('associationForeignKey is defined: '.print_r($join,true));
			/*#/if*/
			if($join[0]!==false){
				$onConditions=array();
				foreach($join[0] as $foreignKey=>$associationForeignKey)
					$onConditions[]=$modelAlias.'.`'.$foreignKey.'`='.$join['alias'].'.`'.$associationForeignKey.'`';
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
	
	/**
	 * @internal
	 * 
	 * Reexecute an already executed query
	 * But with changes, like different conditions
	 * 
	 * @return mixed
	 */
	public function reexecute(){
		/*#if DEV*/ throw new Exception('Use ->refetch() instead of ->reexecute() !'); /*#/if*/
		return $this->refetch();
	}
	
	/**
	 * Refetch an already executed query
	 * But with changes, like different conditions
	 * 
	 * @return mixed
	 */
	public function refetch(){
		$this->objFields=$this->queryResultFields=$this->objData=array();
		return $this->fetch();
	}
	
	/**
	 * Execute the query
	 * 
	 * @return mixed
	 */
	abstract function fetch();
	
	/**
	 * Execute the query : warning use fetch
	 * 
	 * @return mixed
	 */
	public function execute(){
		/*#if DEV*/ throw new Exception('Use ->fetch() instead of ->execute() !'); /*#/if*/
		return $this->fetch();
	}
	
	/**
	 * @ignore
	 * Automaticly added in PhpFile
	 * 
	 * @return mixed
	 */
	public function _execute_(){
		return $this->fetch();
	}
	
	/**
	 * @internal
	 * 
	 * @param QFind
	 * @return void
	 */
	public function _copyJoinsAndConditions($newQuery){
		$join=$this->joins;//$with=$this->with;
		if(!empty($this->where)){
		if($join)
			foreach($join as &$j) $j['fields']=false;
		/*if($with)
			foreach($with as &$w){
				$w['fields']=false;
				if(isset($w['with'])) foreach($w['with'] as &$w2) $w2['fields']=false;
			}
		*/
			$newQuery->where($this->where)->_setJoin($join);
				//->_setWith($with);
		}
		$newQuery->having($this->having);
	}
	
	/**
	 * @internal
	 * @param string
	 * @return string
	 */
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
					$fpos=false;
					if(is_array($alias)){
						$_a=$field; $field=$alias; $alias=$_a; $isArrayField=true;
						/*#if DEV */ if($alias===false) throw new Exception('must have an alias'); /*#/if*/
						$sql.='CONCAT(';
						foreach($field as $concatField)
							$sql.= (is_numeric($concatField) || $concatField[0]==='"' ? $concatField : $fieldPrefix.$this->_db->formatField($concatField)) .',';
						$sql=substr($sql,0,-1).')';
					}elseif(($fpos=strpos($field,'('))!==false){
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
							
							$fpos=false;
							if(is_array($alias)){
								$_a=$field; $field=$alias; $alias=$_a; $isArrayField=true;
								$sql.='CONCAT(';
								foreach($field as $concatField)
									$sql.= (is_numeric($concatField) || $concatField[0]==='"' ? $concatField : $join['alias'].'.'.$this->_db->formatField($concatField)) .',';
								$sql=substr($sql,0,-1).')';
							}elseif($fpos=strpos($field,'(')){
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
				$sql.=$join['type'].($modelName::$__dbName!==$join['modelName']::$__dbName || ($currentDb!==NULL && $currentDb->getDbName() !== $join['modelName']::$__modelDb->getDbName())? $join['modelName']::$__modelDb->getDbName().'.':'')
					.$this->_db->formatTable($join['modelName']::_fullTableName()).' '.$join['alias'];
				if(!empty($join['onConditions'])){
					$sql.=' ON ';
					$sql=$this->_condToSQL($join['onConditions'],'AND',$sql,false);
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
	
	/**
	 * @internal
	 * @param array
	 * @return SModel
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
	
	/**
	 * @return SModel
	 */
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
				/*#if DEV */ if(!is_string($type)) throw new Exception('Type is not a string : '.UVarDump::dump($type)); /*#/if*/
				$joinObj=new $type();
				$joinObj->_copyData($joinData);
				$obj->$join['dataName']=$joinObj;
			}
		}
		return $obj;
	}
	
	/**
	 * @return array
	 */
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
	
	/**
	 * @param SModel
	 * @return void
	 */
	protected function _afterQuery_obj(&$obj){
		self::AfterQuery_obj($this->with,$obj);
	}
	
	/**
	 * @param SModel
	 * @param array
	 * @param QFind|null
	 * @return QFind
	 */
	public static function createWithQuery($obj,&$w,$query=null){
		if($query===null && $w['isCount']) $query=new QCount($w['modelName']);
		switch($w['reltype']){
			case 'belongsTo':
			case 'hasOne':
				$objFields = array_keys($w[0]);
				$resFields = $w[0];
				
				return self::_createBelongsToAndHasOneQuery($query,$w,$obj->_getFields($objFields),$resFields);
			case 'hasMany':
				$objFields = array_keys($w[0]);
				$resFields = $w[0];
				
				return self::_createHasManyQuery($query,$w,$obj->_getFields($objFields),$resFields);
			case 'hasOneThrough':
				$withMore=array(); reset($w['joins']);
				self::_recursiveThroughWith($withMore,$w['joins'],$w);
				$rel=$obj::$_relations[$w['relName']];
				
				$objFields = array_keys($rel[0]);
				$resFields = $rel[0];
				
				return self::_createBelongsToAndHasOneQuery($query,$w,$obj->_getFields($objFields),$resFields,false,$withMore['with'],$rel['alias']);
			case 'hasManyThrough':
				$withMore=array(); reset($w['joins']);
				self::_recursiveThroughWith($withMore,$w['joins'],$w);
				$rel=$obj::$_relations[$w['relName']];
				
				$objFields = array_keys($rel[0]);
				$resFields = $rel[0];
				
				return self::_createHasManyQuery($query,$w,$obj->_getFields($objFields),$resFields,false,$withMore['with'],$rel['alias']);
			case 'belongsToType':
				$type=$obj->_get($w['fieldType']);
				if(empty($w['types'][$type])) return false;
				
				$objFields = array_keys($w[0]);
				$resFields = $w[0];
				
				return self::_createBelongsToAndHasOneQuery($query,$w['relations'][$w['types'][$type]],$obj->_get($objFields),$resFields);
			default:
				throw new Exception('Unknown relation; '.$w['reltype']);
		}
	}
	
	/**
	 * @param array
	 * @param SModel
	 * @return void
	 */
	private static function AfterQuery_obj(&$with,$obj){
		foreach($with as $key=>&$w){
			$query=self::createWithQuery($obj,$w);
			if($query!==false){
				$res=$query->fetch();
				if(isset($w['fieldsInModel']) && $w['fieldsInModel']===true){
					if($res!==false) foreach($res as $k=>$v) $obj->_set($k,$v);
				}else $obj->_set($w['dataName'],$res);
			}
			unset($with[$key]);
		}
	}
	
	/**
	 * @param array
	 * @param array
	 * @param array
	 * @return void
	 */
	private static function _recursiveThroughWith(&$with,&$joins,$w=array()/*,&$lastModelName*/){
		$relName=key($joins); $options=current($joins);
		if(is_int($relName)){ $relName=$options; $options=array(); }
		$options+=array('fields'=>false,'join'=>true);
		/*#if DEV *///if(!isset($lastModelName::$_relations[$relName])) throw new Exception($lastModelName.' does not have a relation named "'.$relName.'"'."\n".'Known relations : '.implode(', ',array_keys($lastModelName::$_relations))); /*#/if*/
		//$options+=$lastModelName::$_relations[$relName];
		if(isset($w['withOptions'][$relName])) $options=$w['withOptions'][$relName]+$options;// can override 'fields'
		$with['with']=array($relName=>$options);
		if(next($joins)===false) return;
		self::_recursiveThroughWith($with['with'][$relName],$joins/*,$lastModelName::$_relations[$relName]['modelName']*/);
	}
	
	/**
	 * @param array
	 * @return void
	 */
	protected function _afterQuery_objs($objs){
		self::AfterQuery_objs($this->with,$objs);
	}
	
	/**
	 * @param array
	 * @param array
	 * @return void
	 */
	private static function AfterQuery_objs(&$with,$objs){
		if(empty($objs)) return;
		foreach($with as $key=>&$w){
			switch($w['reltype']){
				case 'belongsTo':
				case 'hasOne':
					$objFields = array_keys($w[0]);
					
					$values=self::_getValues($objs,$objFields);
					if(!empty($values)){
						$resFields = $w[0];
						$listRes = self::_createHasManyQuery($w['fieldsInModel']===true?new QFindRows($w['modelName']):null,$w,$values,$resFields,true)->fetch();
						
						if($listRes){
							if($w['fieldsInModel']===true){
								foreach($objs as $obj)
									foreach($listRes as $res){
										foreach($resFields as $keyField=>$resField)
											if($res[$resField] != $obj->_get($keyField)) goto endforeachlistres;
										
										foreach($res as $k=>$v)
											if($k!==$resField) $obj->_set($k,$v);
										break;
										endforeachlistres:
									}
							}else{
								foreach($objs as $obj)
									foreach($listRes as $res){
										foreach($resFields as $keyField=>$resField)
											if($res[$resField] != $obj->_get($keyField)) goto endforeachlistres2;
										
										$obj->_set($w['dataName'],$res);
										
										endforeachlistres2:
									}
							}
						}
					}
					unset($with[$key]);
					break;
				case 'hasMany':
					$objFields = array_keys($w[0]);
					
					$values=self::_getValues($objs,$objFields);
					if(!empty($values)){
						$resFields = $w[0];
						
						if(count($w['fields'])===1){
							$keyFirstField=key($w['fields']);
							$oneField=is_int($keyFirstField) ? $w['fields'][$keyFirstField] : $keyFirstField;
							if(substr($oneField,0,8)==='DISTINCT') $oneField=substr($oneField,9);
						}else $oneField=false;
						if(isset($w['tabResKey'])){ $tabResKey=$w['tabResKey']; unset($w['tabResKey']); }
						else $tabResKey=false;
						if(isset($w['groupResBy'])){ $groupResBy=$w['groupResBy']; unset($w['groupResBy']); }
						else $groupResBy=false;
						$query=self::_createHasManyQuery(new QFindAll($w['modelName']),$w,$values,$resFields,true);
						$listRes=$query->fetch();
						if($listRes) foreach($objs as $key=>$obj){
							$listObjsRes=array();
							foreach($listRes as &$res){
								foreach($resFields as $keyField=>$resField)
									if($res->_get($resField) != $obj->_get($keyField)) goto endforeachlistresHasMany;
								
								if($oneField===false){
									if($tabResKey!==false) $listObjsRes[$res->_get($tabResKey)]=$res;
									else $listObjsRes[]=$res;
								}else $listObjsRes[]=$res->_get($oneField);
								
								endforeachlistresHasMany:
							}
							if($groupResBy!==false){
								$finalRes=array();
								if(is_array($groupResBy)) foreach($listObjsRes as $key=>&$row) $finalRes[$row->{$groupResBy[0]}][$key]=$row->{$groupResBy[1]};
								else foreach($listObjsRes as $key=>&$row) $finalRes[$row->$groupResBy][$key]=$row;
								$listObjsRes=$finalRes;
							}
							$obj->_set($w['dataName'],$listObjsRes);
						}
					}
					break;
					
				case 'hasManyThrough':
					reset($objs);
					$obj=current($objs);
					$rel=$obj::$_relations[$w['relName']];
					
					$objFields = array_keys($rel[0]);
					
					$values=self::_getValues($objs,$objFields);
					if(!empty($values)){
						$withMore=array(); reset($w['joins']);
						self::_recursiveThroughWith($withMore,$w['joins'],$obj::$__className);
						
						$resFields = $rel[0];
						$oneField=(count($w['fields'])===1 && !isset($w['with'])) || isset($w['forceOneField']) ? $w['fields'][0] : false;
						
						/*#if DEV */if(empty($w['fields'])) throw new Exception('You must specify fields...');/*#/if*/
						foreach($resFields as $resField){
							$w['fields']['('.$rel['alias'].'.`'.$resField.'`)']=$resField;
							if(isset($w['groupBy'])) $w['groupBy']=$rel['alias'].'.'.$resField.','.$w['groupBy'];
						}
						
						$listRes=self::_createHasManyQuery(null,$w,$values,$resFields,false,$withMore['with'],$rel['alias'])->fetch();
						if($listRes!==false){
							foreach($objs as $k=>&$obj){
								$listObjsRes=array();
								foreach($listRes as $res){
									foreach($resFields as $keyField=>$resField)
										if($res->_get($resField) != $obj->_get($keyField)) goto endforeachlistresHasManyThrough;
									if($oneField===false) $listObjsRes[] = $res;
									else $listObjsRes[]=$res->_get($oneField);
									
									endforeachlistresHasManyThrough:
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
	
	/**
	 * @param SModel
	 * @param string
	 * @param array
	 * @return void
	 */
	public static function findWith($model,$key,$options){
		$with=array();
		self::_addWith($with,$key,$options,$model::$__className);
		self::AfterQuery_obj($with,$model);
	}
	
	/**
	 * @param string
	 * @param SModel
	 * @param string
	 * @param array
	 * @return mixed
	 */
	public static function findWithPaginate($paginateClass,$model,$key,$options){
		$w=array();
		self::_addWith($w,$key,$options,$model::$__className);
		$query=self::createWithQuery($model,$w[$key]);
		if($query===false) return false;
		$res=$paginateClass::_create($query);
		$model->_setRef($w[$key]['dataName'],$res); //not executed, but should be a reference to the variable
		unset($w[$key]);
		return $res;
	}
	
	/**
	 * @param SModel
	 * @param array
	 * @return void
	 */
	public static function findMWith($model,$mwith){
		$with=array();$modelName=$model::$__className;
		foreach($mwith as $key=>&$options){
			if(is_numeric($key)){ $key=$options; $options=array();}
			self::_addWith($with,$key,$options,$modelName);
		}
		self::AfterQuery_obj($with,$model);
	}
	
	/**
	 * @param array
	 * @param array
	 * @return array
	 */
	private static function _getValues($objs,$objFields){
		$values=array();
		foreach($objs as &$obj){
			foreach($objFields as $objField){
				$value=$obj->_get($objField);
				if($value !== null) $values[$objField][]=$value;
			}
		}
		foreach($values as &$v) $v=array_unique($v,SORT_REGULAR);
		return $values;
	}
	
	private static function _createBelongsToAndHasOneQuery($query,$w,$values,$resFields,$addResFields=false,$moreWith=null,$fieldTableAlias=null){
		if($query===null) $query=new QFindOne($w['modelName']);
		$query->setFields($addResFields ? self::_addFieldsIfNecessary($w['fields'],$resFields) : $w['fields']);
		if(isset($w['where'])) $where=$w['where']; else $where=array();
		if($fieldTableAlias !== null) foreach($resFields as &$resField) $resField=$fieldTableAlias.'.'.$resField;
		foreach($resFields as $keyField=>$resField) $where[$resField]=$values[$keyField];
		$query->where($where);
		if(isset($w['with'])) $query->_setWith($w['with']);
		if($moreWith!==null) $query->setAllWith($moreWith);
		return $query;
	}
	
	private static function _createHasManyQuery($query,$w,$values,$resFields,$addResFields=false,$moreWith=null,$fieldTableAlias=null){
		if($query===null){
			if($addResFields===false && ((count($w['fields'])===1 && !isset($w['with'])) || isset($w['forceOneField']))) $query=new QFindValues($w['modelName']);
			else $query = new QFindAll($w['modelName']);
		}
		$query->setFields($addResFields ? self::_addFieldsIfNecessary($w['fields'],$resFields) : $w['fields']);
		if(isset($w['where'])) $where=$w['where']; else $where=array();
		if($fieldTableAlias !== null) foreach($resFields as &$resField) $resField=$fieldTableAlias.'.'.$resField;
		foreach($resFields as $keyField=>$resField) $where[$resField]=$values[$keyField];
		$query->where($where);
		if(isset($w['orderBy'])) $query->orderBy($w['orderBy']);
		if(isset($w['groupBy'])){
			if($addResFields===true){
				if(is_string($w['groupBy'])) $w['groupBy']=array($w['groupBy']);
				$w['groupBy'][]=$resField;
			}
			$query->groupBy($w['groupBy']);
		}
		if(isset($w['with'])) $query->_setWith($w['with']);
		if(isset($w['limit'])) $query->limit($w['limit']);
		if($moreWith!==null) $query->setAllWith($moreWith);
		if(isset($w['groupResBy'])) $query->groupResBy($w['groupResBy']);
		if(isset($w['tabResKey'])) $query->tabResKey($w['tabResKey']);
		return $query;
	}
	
	private static function _addFieldsIfNecessary(&$fields,$addFields){
		if(empty($fields)) return $fields;
		foreach($addFields as $addField) if(!in_array($addField,$fields)) $fields[]=$addField;
		return $fields;
	}
}