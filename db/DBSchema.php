<?php
/**
 * Handle Schema comparisons between the app's models and the database
 * @see DBSchemaProcessing
 */
abstract class DBSchema{
	private $tableExist=true;
	protected $schemaProcessing,$modelName,$tableName,$modelInfos,$isEntity,
		/* columns infos */
		$columns=false,$primaryKeys,$indexes,$foreignKeys;
	/**  @var DB */
	protected $db;
	
	public function getModelName(){ return $this->modelName; }
	
	public static function create(&$schemaProcessing,$modelName,$isEntity=false){
		$modelName=trim($modelName,'.');
		$db=$modelName::getDB();
		$schemaClass='DBSchema'.$db->_getType();
		$dbSchema=new $schemaClass($db,$modelName::_fullTableName());
		$dbSchema->schemaProcessing=$schemaProcessing;
		$dbSchema->modelName=$modelName;
		$dbSchema->isEntity=$isEntity;
		return $dbSchema;
	}
	
	public static function get($db,$tableName){
		$schemaClass='DBSchema'.$db->_getType();
		$dbSchema=new $schemaClass($db,$tableName);
		return $dbSchema;
	}
	
	public function __construct(&$db,$tableName){
		$this->db=&$db;
		$this->tableName=$tableName;
	}
	
	public function getDB(){return $this->db;}
	public function setModelInfos($modelInfos){$this->modelInfos=&$modelInfos;}
	
	public function process(){
		$modelName=$this->modelName;
		if(isset($modelName::$__modelInfos)) $this->modelInfos=&$modelName::$__modelInfos;
		else $this->modelInfos=false;
		
		if($this->isGenerateSchema() && $this->schemaProcessing->isGenerate()){
			if(!($this->tableExist=$this->tableExist())){
				$this->log('Create table');
				if($this->shouldApply()){
					$this->createTable();
					$this->findColumnsInfos();
					$this->generatePropDefs();
					if(method_exists($modelName,'afterCreateTable')) $modelName::afterCreateTable();
				}
			}else{
				if(!$this->checkTable()){
					$this->log('Check table failed!');
					if($this->shouldApply()) $this->correctTable();
				}
				$this->findColumnsInfos();
				$this->compareTableAndApply();
				$this->generatePropDefs();
			}
		}else{
			$this->findColumnsInfos();
			$this->generatePropDefs();
		}
	}
	
	public function log($log){
		$this->schemaProcessing->log($this->db->_getName(),$this->tableName,$log);
	}
	public function doUpdate($sql,$keep=true){
		$this->db->doUpdate($sql);
		if($keep) $this->schemaProcessing->query($this->db->_getName(),$sql);
	}
	
	
	public function isGenerateSchema(){
		$modelName=$this->modelName;$dbName=$modelName::$__dbName;
		if(isset(Config::$db[$dbName]['generate'])) return Config::$db[$dbName]['generate'];
		return isset($this->modelInfos['generate'])?Config::$generate[$this->modelInfos['generate']]:true;
	}
	
	public function shouldApply(){return $this->schemaProcessing->shouldApply(); }
	
	public function compareTableAndApply(){
		if($this->modelInfos===false) return;
		if($this->columns===false) $this->findColumnsInfos();
		$columns=&$this->columns;$modelInfos=$this->modelInfos;
		$icolumns=array(); $prev=false; $allPrev=array();
		foreach($columns as $col) $icolumns[$col['name']]=$col;
		foreach($modelInfos['columns'] as $name=>$col){
			$allPrev[$name]=$prev;
			if(isset($icolumns[$name])) $prev=$name;
		}//debug(array_keys($icolumns));debug($allPrev);exit;
		
		$this->resetColumnsModifications();
		
		// Add cols
		foreach($a2=array_diff_key($modelInfos['columns'],$icolumns) as $name=>$col){
			$this->log('Add col : '.$name.' after '.$allPrev[$name]);
			$prev=$allPrev[$name];
			if($this->shouldApply()) $this->addColumn($name,$prev);
			while(($next=array_search($prev,$allPrev))!==false)
				$allPrev[$next]=$name;
			$allPrev[$name]=$prev;
			
		}
		// Remove cols
		$a1=array_diff_key($icolumns,$modelInfos['columns']);
		if(!empty($a1)){
			$pks=$this->primaryKeys;
			foreach($a1 as $name=>$col){
				$this->log('Remove col : '.$name);
				if($this->shouldApply()){
					if(in_array($name,$pks)){
						foreach($this->foreignKeys as $fk)
							try{ $this->removeForeignKey($fk); }catch(DBException $e){ }
						foreach($this->indexes as $indexes)
							foreach($indexes as $iName=>$iFields) $this->removeIndex($iName);
						$this->removePrimaryKey();
					}
					$this->removeColumn($name);
				}
			}
		}
		// Change cols
		$a3=array();
		foreach(array_diff_key($modelInfos['columns'],$a1,$a2) as $name=>$col)
			if($this->compareColumn($icolumns[$name],$col)){
				$this->log('Change col : '.$name);
				//debugVar($tableName.':'.$name); debugVar($icolumns[$name]);debugVar($col);
				$a3[]=$name;
				if($this->shouldApply()) $this->changeColumn($name,$icolumns[$name]);
			}
		
		if($this->shouldApply()){
			try{
				$this->applyColumnsModifications();
			}catch(DBException $e){
				foreach($this->foreignKeys as $fk)
					try{ $this->removeForeignKey($fk); }catch(DBException $e){ }
				foreach($this->indexes as $indexes)
					foreach($indexes as $iName=>$iFields) $this->removeIndex($iName);
				$this->applyColumnsModifications();
			}
		}
		
		// reorder
		if($this->shouldApply()){
			if(!empty($a1)||!empty($a2)||!empty($a3)) $this->findColumnsInfos();
			$columns=$this->columns;
			$actualAllPrev=array();
			$actualPrev=false;
			foreach($columns as $col){
				$name=$col['name'];
				$actualAllPrev[$name]=$actualPrev;
				$actualPrev=$name;
			}
			foreach($modelInfos['columns'] as $name=>$col){
				$prev=$allPrev[$name];
				if($prev!==$actualAllPrev[$name]){
					$this->log('Reorder table');
					if($this->shouldApply()) $this->reorderTable();
					break;
					/*debugVar($tableName.': Move col : '.$name.' after '.$prev);
					if($hasModifsCallback){ call_user_func($hasModifsCallback); $hasModifsCallback=false; }
					$this->changeColumn($tableName,$name,$col,$modelInfos,$columns,$prev);
					if(($next=array_search($prev,$actualAllPrev))!==false)
						$actualAllPrev[$next]=$name;
					if(($prevOld=array_search($name,$actualAllPrev))!==false)
						$actualAllPrev[$prevOld]=$actualAllPrev[$name];
					$actualAllPrev[$name]=$prev;
					/*debugVar($actualAllPrev);*/
				}
			}
		}
		
		// Primary keys
		//if(!empty($a1) || !empty($a2) || !empty($a3)){
			$primaryKeys=&$modelInfos['primaryKeys'];
			$pks=$this->getPrimaryKeys();
			if($primaryKeys!=$pks){
				$this->log('Change Pks : '.(empty($pks)?'':implode(',',$pks)).' ===> '.(empty($primaryKeys)?'':implode(',',$primaryKeys)));
				if($this->shouldApply()){
					if($pks) $this->changePrimaryKey();
					else $this->addPrimaryKey();
				}
			}
		/*}*/
	}

	private function buildIndexName($columns,$prefix=''){
		return $name=(empty($prefix)?'':strtolower($prefix).'_').implode('|',$columns);
	}
	public function compareIndexes(){
		if(!$this->isGenerateSchema()) return;
		$indexes=$this->indexes;
		$currentIndex=$currentUniqueIndex=array();
		foreach(array('nonunique'=>'currentIndex','unique'=>'currentUniqueIndex') as $keyI=>$tabIN){
			$tabI=$$tabIN;
			if(!empty($indexes[$keyI])) 
				foreach($indexes[$keyI] as $iName=>$iFields) if(substr($iName,0,3)!=='fk_') $tabI[$iName]=$iFields;
			$$tabIN=$tabI;
		}
		/* done in ModelFile
		foreach($this->modelInfos['columns'] as $name=>$column){
			if($column['index']) $modelIndex[]=array($name);
			if($column['unique']) $modelUniqueIndex[]=array($name);
		}*/
		
		// 0 = non unique, 1 =unique
		$iPrefix=array(0=>'',1=>'unique');
		$modelIndexes=$this->modelInfos['indexes'];
		
		$modelIndex=$modelUniqueIndex=array(); $array=array();
		if(!empty($modelIndexes[0])) $array[0]=array('name'=>'modelIndex','tab'=>$modelIndexes[0]);
		if(!empty($modelIndexes[1])) $array[1]=array('name'=>'modelUniqueIndex','tab'=>$modelIndexes[1]);
		foreach($array as $key=>$_){
			$name=&$_['name'];$tab=&$_['tab'];
			$modelIndexTab=&$$name;
			foreach($tab as $fields)
				$modelIndexTab[$this->buildIndexName($fields,$iPrefix[$key])]=$fields;
		}
		unset($array);
		
		foreach(array(array($modelIndex,$currentIndex),array($modelUniqueIndex,$currentUniqueIndex)) as $key=>$array){
			// Add index
			foreach($a2=array_diff_key($array[0],$array[1]) as $indexName=>$fields){
				$this->log('Add index '.$indexName);
				if($this->shouldApply()) $this->addIndex($indexName,$fields,$iPrefix[$key]);
			}
			
			// Remove index
			foreach($a1=array_diff_key($array[1],$array[0]) as $indexName=>$fields){
				$this->log('Remove index '.$indexName);
				if($this->shouldApply()) $this->removeIndex($indexName);
			}
			
			// Change index
			foreach($a3=array_diff_key($array[0],$a1,$a2) as $indexName=>$fields){
				if($fields!==array_keys($array[1][$indexName]['columns'])){
					$this->log('Change index '.$indexName);
					if($this->shouldApply()){
						$this->removeIndex($indexName);
						$this->addIndex($indexName,$fields,$iPrefix[$key]);
					}
				}
			}
		}
	}
	
	
	public function compareForeignKeys(){
		if(!$this->isGenerateSchema() || $this->modelInfos===false) return;
		$modelName=&$this->modelName;
		$currentConstraints=$this->foreignKeys;
		$constraints=array();
		foreach($this->modelInfos['columns'] as $colname=>$col)
			if(isset($col['ForeignKey'])) $constraints[$colname]=&$col['ForeignKey'];
		
		//debugVar($constraints,$currentConstraints);
		// Add fk
		foreach($a2=array_diff_key($constraints,$currentConstraints) as $col=>$fk){
			$this->log('Add foreign key on '.$col);
			if($this->shouldApply()) $this->addForeignKey($col,$fk,false,$this->modelInfos['columns'][$col]);
		}
		
		// Remove fk
		foreach($a1=array_diff_key($currentConstraints,$constraints) as $col=>$fk){
			$this->log('Remove foreign key on '.$col);
			if($this->shouldApply()) $this->removeForeignKey($fk);
		}
		
		// Change fk
		$a3=array();
		foreach(array_diff_key($constraints,$a1,$a2) as $col=>$fk){
			$fdbName=$fk[0]::$__modelDb->getDatabaseName();
			if($fdbName===$modelName::$__modelDb->getDatabaseName()) $fdbName='';
			if($this->compareForeignKey($currentConstraints[$col],$fdbName,$fk[0]::_fullTableName(),$fk[1],isset($fk['onDelete'])?$fk['onDelete']:false,isset($fk['onUpdate'])?$fk['onUpdate']:false)){
				$this->log('Change foreign key on '.$col);
				if($this->shouldApply()){
					$this->removeForeignKey($currentConstraints[$col]);
					$this->addForeignKey($col,$fk,false,$this->modelInfos['columns'][$col]/*,$currentConstraints[$col]*/);
				}
			}
		}
	}
	
	
	public function generatePropDefs(){
		if(!$this->tableExist || $this->modelInfos===false) return;
		if($this->columns===false) $this->findColumnsInfos();
		$modelName=&$this->modelName;
		$properties=$this->createModelPropDef();
		
		if(empty($properties)) $properties=null;
		
		$modelInfos=include ($filename=($this->isEntity?APP.'models/infos/':Config::$models_infos).$this->modelName);
		$modelInfos['props']=$properties;
		file_put_contents($filename,'<?php return '.UPhp::exportCode($modelInfos).';');
		$modelName::$__PROP_DEF=$properties;
	}

	public  function createModelInfos(){
		return array('primaryKeys'=>$this->getPrimaryKeys(),'columns'=>$this->columns,'isAI'=>false,'indexes'=>array());
	}
	
	public function createModelPropDef(){
		$tableName=&$this->tableName;$db=&$this->db;$modelName=&$this->modelName;
		$columns=$this->columns;
		
		$properties=array();
		
		foreach($this->columns as $col){
			$pname=$col['name'];
			$annotations=!empty($modelName::$__modelInfos['annotations'][$pname])?$modelName::$__modelInfos['annotations'][$pname]:array();
			if(isset($annotations['var'])) $ptype=$annotations['var'];
			else{
				if(!isset($col['type'])) throw new Exception($modelName.': field "'.$pname.'" has no @SqlType !');
				if($max=strpos($sqltype=$col['type'],'(')) $sqltype=substr($sqltype,0,$max);
				if($min=strpos($col['type'],'(')){
					$sqltypeparams=substr($col['type'],$min+1,-1);
					if($max=strpos($sqltypeparams,')')) $sqltypeparams=substr($sqltypeparams,0,$max);
				} 
				else $sqltypeparams=false;
				
				switch (strtolower($sqltype)){
					case 'char':
						if($sqltypeparams!==false && $sqltypeparams=='0'){
							$ptype=$sqltype='boolean';
							break;
						}
					case 'varchar': case 'bit':
					case 'date': case 'datetime': case 'timestamp':
					case 'enum':
					case 'set':
						$ptype='string'; break;
					case 'tinytext': case 'text': case 'mediumtext': case 'longtext':
						$annotations['Text']=0;
						$ptype='string'; break;
					case 'int': case 'tinyint': case 'smallint': case 'mediumint': case 'bigint': case 'serial': case 'integer': case 'year':
						$ptype='int'; break;
					case 'boolean': case 'bool':
						$ptype='boolean'; break;
					case 'float': case 'decimal':
						$ptype='float'; break;
					case 'double':
						$ptype='double'; break;
					case 'real':
						$ptype='real'; break;
					case 'blob': case 'clob': case 'binary': case 'varbinary': case 'tinyblob': case 'longblob':
						$ptype='binary'; break;
					default: exit('Unknow type: '.$sqltype);
				}
			}
			
			$propDef=array('type'=>$ptype);
			// $annotations.="'".$pname."'=>".(empty($matches[2][$key]) ? 'false': "array(".$matches[2][$key].")").',';
			if($col['notnull'] && !isset($col['AutoIncrement'])) $annotations['Required']=0;
			switch(strtolower($sqltype)){
				case 'char': case 'varchar': case 'tinytext':
					$annotations['MaxLength']=array($sqltypeparams?(int)$sqltypeparams:255); break;
				case 'tinyint':
					$annotations['MaxLength']=array($sqltypeparams?(int)$sqltypeparams:4); break;
				case 'smallint':
					$annotations['MaxLength']=array($sqltypeparams?(int)$sqltypeparams:6); break;
				case 'mediumint':
					$annotations['MaxLength']=array($sqltypeparams?(int)$sqltypeparams:9); break;
				case 'int':
					$annotations['MaxLength']=array($sqltypeparams?(int)$sqltypeparams:11); break;
				case 'bigint':
					$annotations['MaxLength']=array($sqltypeparams?(int)$sqltypeparams:20); break;
			}
			
			if(!empty($annotations)) $propDef['annotations']=$annotations;
			$properties[$pname]=$propDef;
		}
		return $properties;
	}

	// static but always set to array in the end
	private static $hasManyThrough=array();
	public function addForeignKeysRelations(){
		$modelName=&$this->modelName;
		if($this->modelInfos===false) return;
		$relations=&$modelName::$__modelInfos['relations'];
		$addedRelations=array();
		
		foreach($modelName::$__modelInfos['columns'] as $fieldName=>$column){
			if(isset($column['ForeignKey'])){
				$rModelName=$column['ForeignKey'][0];
				if($rModelName==$modelName){
					if(!isset($relations[$rModelName]))
						$relations[$rModelName]=array('reltype'=>'belongsTo',0=>array($fieldName=>$column['ForeignKey'][1]),'alias'=>($modelName::$__alias).'2');
				}else{
					if(!isset($relations[$rModelName]))
						$relations[$rModelName]=array('reltype'=>'belongsTo',0=>array($fieldName=>$column['ForeignKey'][1]));
					$addedRelations[]=$rModelName;
					if(!isset($rModelName::$__modelInfos['relations'][$modelName]))
						$rModelName::$__modelInfos['relations'][$modelName]=array(
							'reltype'=>(count($modelName::$__modelInfos['primaryKeys'])===1 && $modelName::$__modelInfos['primaryKeys'][0]===$fieldName) ? 'hasOne' : 'hasMany', //TODO OR hasUnique
							0=>array($column['ForeignKey'][1]=>$fieldName)
						);
				}
			}
		}
		
		foreach($addedRelations as $key=>$rModelName1){
			foreach($addedRelations as $key2=>$rModelName2){
				if($key!==$key2 && ((count($modelName::$__modelInfos['primaryKeys'])===2 && count($modelName::$__modelInfos['columns'])<4) || (stripos($modelName,$rModelName1)!==false && stripos($modelName,$rModelName2)!==false)))
				self::$hasManyThrough[]=array($rModelName1,$rModelName2,$modelName);
			}
		}
	}

	public function addForeignKeysHasManyThroughRelations(){
		foreach(self::$hasManyThrough as &$keys){
			list($rModelName1,$rModelName2,$modelName)=$keys;
			if(!isset($rModelName1::$__modelInfos['relations'][$rModelName2]))
				$rModelName1::$__modelInfos['relations'][$rModelName2]=array('reltype'=>'hasManyThrough','joins'=>$modelName);
			if(!isset($rModelName2::$__modelInfos['relations'][$rModelName1]))
				$rModelName2::$__modelInfos['relations'][$rModelName1]=array('reltype'=>'hasManyThrough','joins'=>$modelName);
		}
		self::$hasManyThrough=array();
	}
	
	public static function defaultRelationDataName($key,$modelName,$pluralized=true){
		$pModelName=$pluralized?UInflector::pluralize($modelName):false;
		if($pluralized && startsWith($key,$pModelName)) $dataName=substr($key,strlen($pModelName));
		elseif(startsWith($key,$modelName)) $dataName=substr($key,strlen($modelName));
		else $dataName=self::defaultRelationDataNamePrefixless($key,$modelName,$pluralized);
		if($dataName===false) $dataName=$key;
		return $pluralized?UInflector::pluralize(lcfirst($dataName)):lcfirst($dataName);
	}
	public static function defaultRelationDataNamePrefixless($key,$modelName){
		$firstPart=preg_replace('/^((?:[A-Z]+|[0-9]+)[a-z_]*).*$/','$1',$key);
		if($firstPart!==$key && preg_match('/^'.preg_quote($firstPart).'/',$modelName)) $dataName=substr($key,strlen($firstPart));
		else $dataName=$key;
		if($dataName===false) $dataName=$key;
		return lcfirst($dataName);
	}
	
	public function createRelations(){
		$modelName=$this->modelName;
		if($this->modelInfos===false) return;
		$contentInfos=$modelName::$__modelInfos;
		
		if($contentInfos['relations']){
			foreach($contentInfos['relations'] as $key=>&$relation){
				$type=$relation['reltype'];
				
				if($type === 'belongsToType'){
					if(!isset($relation['fieldType'])) $relation['fieldType']=$key;
					$relation['join']=null; $relation['isCount']=false;
					$internalRelations=array();
					/*debugVar($relation['types']);*/
					foreach($relation['relations'] as $key2=>$rel2){
						$rel2['reltype']='belongsTo';
						$rel2['dataName']=$relation['dataName'];
						if(!isset($rel2['foreignKey'])) $rel2['foreignKey']='rel_id';
						
						self::_defaultsRelation($modelName,$rel2['reltype'],$key2,$rel2);
						$internalRelations[$key2]=$rel2;
					}
					$relation['relations']=$internalRelations;
				}else{
					self::_defaultsRelation($modelName,$type,$key,$relation);
				}
			}
			$this->_writeContentInfos($contentInfos);
		}
	}

	private function _writeContentInfos($contentInfos){
		$modelName=$this->modelName;
		
		$content='<?php return '.UPhp::exportCode($contentInfos).';';
		if(/*$write*/true) file_put_contents($filename=($this->isEntity?APP.'models/infos/':Config::$models_infos).$modelName,$content);
		$modelName::$__modelInfos=$contentInfos;
		$modelName::$_relations=$contentInfos['relations'];
	}
	public function createAutoParentRelations(){
		$modelName=$this->modelName;
		if($this->modelInfos===false) return;
		$contentInfos=&$modelName::$__modelInfos;
		
		if(in_array('BChild',class_uses($modelName,true))){
			$relations=$modelName::$__modelInfos['relations'];
			$parentRelation=$relations['Parent'];
			if(isset($parentRelation[0]['id']) && $parentRelation[0]['id']==='id'){
				$contentInfos=&$modelName::$__modelInfos;
				$parentModelName=$parentRelation['modelName'];
				foreach($parentModelName::$_relations as $key=>$relation){
					if(!isset($contentInfos['relations'][$key])) $contentInfos['relations'][$key]=$relation;
				}
				$this->_writeContentInfos($contentInfos);
			}
		}
	}

	private static function _defaultsRelation(&$modelName,&$type,&$key,&$relation){
		if(!isset($relation['modelName'])) $relation['modelName']=$key;
		try{
			class_exists($relation['modelName'],true);
		}catch(Exception $e){
			throw new Exception('Creating relations for model '.$modelName.': model does NOT exists "'.$relation['modelName'].'"',
				1,$e);
		}
		if(!isset($relation['alias'])) $relation['alias']=$relation['modelName']::$__alias;
		if(in_array($type,array('hasMany','belongsTo','hasOne')))
			$relation+=array('fieldsInModel'=>false,'isCount'=>false,'isDistinct'=>false,'join'=>null,'type'=>' LEFT JOIN ','fields'=>null);
		else $relation+=array('fields'=>null,'join'=>null,'isCount'=>false);
		
		$keyDataName=$key[0]==='E' && strtoupper(substr($key,0,3))==substr($key,0,3) ? substr($key,2) : $key;
		if(!isset($relation['conditions'])){
			switch($type){
				case 'hasMany':
					if(!isset($relation[0])){
						if(!isset($relation['foreignKey'])) $relation['foreignKey']=$modelName::_getPkName();
						if(!isset($relation['associationForeignKey'])) $relation['associationForeignKey']=self::_defaultForeignKey($modelName);
					}
					if(!isset($relation['dataName'])) $relation['dataName']=self::defaultRelationDataName($keyDataName,$modelName);
					break;
				case 'belongsTo': // many to one
					if(!isset($relation[0])){
						if(!isset($relation['foreignKey'])) $relation['foreignKey']=self::_defaultForeignKey($relation['modelName']);
						if(!isset($relation['associationForeignKey'])) $relation['associationForeignKey']=$relation['modelName']::_getPkName();
					}
					if(!isset($relation['dataName'])) $relation['dataName']=self::defaultRelationDataNamePrefixless($keyDataName,$modelName);
					break;
				case 'hasOne': // one to one
					if(!isset($relation[0])){
						if(!isset($relation['foreignKey'])) $relation['foreignKey']=$modelName::_getPkName();
						//if(!isset($relation['foreignKey'])) $relation['foreignKey']=self::_defaultForeignKey($relation['modelName']);
						if(!isset($relation['associationForeignKey'])) $relation['associationForeignKey']=self::_defaultForeignKey($modelName);
					}
					if(!isset($relation['dataName'])) $relation['dataName']=self::defaultRelationDataName($keyDataName,$modelName,false);
					break;
				case 'hasManyThrough':
				case 'hasOneThrough':
					if(is_string($relation['joins'])) $relation['joins']=explode(',',$relation['joins']);
					if(!isset($relation['relName'])){
						$relation['relName']=key($relation['joins']);
						if(is_int($relation['relName'])) $relation['relName']=current($relation['joins']);
						$relation['joins']=array_reverse($relation['joins'],true);
					}
					
					if(!isset($relation['dataName'])) $relation['dataName']= $type==='hasOneThrough' ? self::defaultRelationDataNamePrefixless($keyDataName,$modelName) : self::defaultRelationDataName($keyDataName,$modelName);
					break;
				default: throw new Exception($modelName.': Unknown type: '.$type);
			}
			if($type!=='hasManyThrough' && $type!=='hasOneThrough' && !isset($relation[0]))
					$relation[0]=array($relation['foreignKey']=>$relation['associationForeignKey']);
			unset($relation['foreignKey'],$relation['associationForeignKey']);
		}
	}


	private static function _defaultForeignKey($modelName){
		$pkName=$modelName::_getPkName();
		if ("id"==$pkName || "uuid"==$pkName)
			return UInflector::singularizeUnderscoredWords($modelName::$__tableName).'_'.$pkName;
		return $pkName;
	}
	
	public abstract function tableExist();
	public abstract function createTable();
	public abstract function removeTable();
	public abstract function reorderTable();
	
	public abstract function checkTable();
	public abstract function correctTable();
	
	public abstract function findColumnsInfos();
	
	/** Return if change or not */
	public abstract function compareColumn($column,$modelInfo);
	
	public abstract function removePrimaryKey();
	public abstract function addPrimaryKey();
	public abstract function changePrimaryKey();
	
	
	public abstract function addForeignKey($colName,$fk,$dropBefore,$colInfos);
	public abstract function removeForeignKey($colName);
	
	/** Return if change or not */
	public abstract function compareForeignKey($modelFk,$refDbName,$refTableName,$refColName,$onDelete=false,$onUpdate=false);
	
	public abstract function disableForeignKeyChecks();
	public abstract function activeForeignKeyChecks();
	
	public abstract function getForeignKeys(); 
	//public abstract function removeAllForeignKeys();
	//public abstract function removeForeignKeys($tableName);
	
	public abstract function addColumn($colName,$prev=null);
	public abstract function changeColumn($colName,$oldColumn,$prev=null);
	public abstract function removeColumn($colName);
	public abstract function resetColumnsModifications();
	public abstract function applyColumnsModifications();
	
	public abstract function addIndex($name,$columns,$type='');
	public function addUniqueIndex($columns){
		$this->addIndex($name, $columns,'UNIQUE');
	}
	public abstract function removeIndex($name);
}