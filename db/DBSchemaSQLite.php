<?php
class DBSchemaSQLite extends DBSchema{
	
	public function tableExist(){
		return $this->db->doSelectValue("SELECT 1 FROM  sqlite_master WHERE type='table' AND name=".$this->db->escape($this->tableName));
	}
	
	
	public function createTable($tempTable=false){
		$sql='CREATE '.($tempTable?'TEMPORARY ':'').'TABLE '.$this->db->formatTable($tempTable===false?$this->tableName:$tempTable).' ( ';
		foreach($this->modelInfos['columns'] as $colname=>&$colDef){
			$sql.=$this->db->formatColumn($colname).' '.self::_getColumnDef($colDef).' ';
			if(in_array($colname,$this->modelInfos['primaryKeys']) && count($this->modelInfos['primaryKeys'])==1){
				$sql.='PRIMARY KEY';
				if(isset($colDef['AutoIncrement'])) $sql.=' AUTOINCREMENT';
			}
			$sql.=', ';
		}
		if(count($this->modelInfos['primaryKeys']) > 1) $sql.='PRIMARY KEY (`'.implode('`,`',$this->modelInfos['primaryKeys']).'`)';
		
		$sql=rtrim($sql,', ');
		$sql.=')';
		$this->db->doUpdate($sql);
	}
	
	public function removeTable($tempTable=false){
		$this->db->doUpdate('DROP TABLE '.$this->db->formatTable($tempTable===false?$this->tableName:$tempTable));
	}
	
	public function reorderTable(){
		list($table,$columns,$modelInfos)=array($this->tableName,$this->columns,$this->modelInfos);
		$schema=&$this;
		$fields=implode(',',array_map(array($this,'formatColumn'),array_keys($columns)));
		$this->db->transaction(function(DB $db) use(&$schema,&$fields){
			$tableF=$db->formatTable($table);
			$schema->createTable($tempTableName='t'.time().'_backup');
			//$db->doUpdate('CREATE TEMPORARY TABLE t1_backup AS SELECT '.$fields.' FROM '.$table);
			$db->doUpdate('INSERT INTO '.$db->formatTable($tempTableName).' SELECT '.$fields.' FROM '.$tableF);
			$schema->removeTable();
			$schema->createTable();
			$db->doUpdate('INSERT INTO '.$tableF.' SELECT '.$fields.' FROM '.$db->formatTable($tempTableName));
			$db->removeTable($tempTableName);
		});
	}
	
	
	public function checkTable(){return true;}
	public function correctTable(){}
	
	public function getColumns(){
		return $this->db->doSelectRows('PRAGMA table_info('.$this->db->escape($this->tableName).')');
	}
	
	private $_addColumns,$_hasChangedOrRemovedColumn;
	
	public function resetColumnsModifications(){
		$this->_addColumns=array();
		$this->_hasChangedOrRemovedColumn=false;
	}
	public function addColumn($colName,$prev=null){
		//$this->db->doUpdate('ALTER TABLE '.$this->db->formatTable($this->tableName).' ADD '.$this->db->formatColumn($colName).' '.self::_getColumnDef($this->modelInfos['columns'][$colName]));
		$this->_addColumns[]='ADD '.$this->db->formatColumn($colName).' '.self::_getColumnDef($this->modelInfos['columns'][$colName]);
	}
	
	public function applyColumnsModifications(){
		if(!empty($this->_addColumns)) $this->db->doUpdate('ALTER TABLE '.$this->db->formatTable($this->tableName).' '.implode(', ',$this->_addColumns));
		if($this->_hasChangedOrRemovedColumn){
			$fields=array(); $columns=$this->columns();
			foreach($columns as $k=>$col) $columns[$k]=$col['name'];
			foreach($modelInfos['columns'] as $name=>$col){
				if($name != $column) $fields[]= in_array($name,$columns) ? $this->db->formatColumn($name) : 'NULL';
			}
			$fields=implode(',',$fields);
			$this->db->beginTransaction();
			$tableF=$this->db->formatTable($schema->tableName);
			$this->createTable($tempTableName='t'.time().'_backup');
			//$this->db->doUpdate('CREATE TEMPORARY TABLE t1_backup AS SELECT '.$fields.' FROM '.$table);
			$this->db->doUpdate('INSERT INTO '.$this->db->formatTable($tempTableName).' SELECT '.$fields.' FROM '.$tableF);
			$this->removeTable();
			$this->createTable();
			$this->db->doUpdate('INSERT INTO '.$tableF.' SELECT '.$fields.' FROM '.$this->db->formatTable($tempTableName));
			$this->removeTable($tempTableName);
			$this->db->commit();
		}
	}
	
	public function changeColumn($colName,$oldColumn,$prev=null){
		$this->_hasChangedOrRemovedColumn=true;
		/*$fields=array(); $schema=&$this;
		foreach($this->modelInfos['columns'] as $name=>&$col) $fields[]=$this->db->formatColumn($name);
		if(empty($fields)){ $this->removeTable(); return; }
		$fields=implode(',',$fields);
		$this->db->beginTransaction();
			$tableF=$this->db->formatTable($schema->tableName);
			$this->createTable($tempTableName='t'.time().'_backup');
			//$this->db->doUpdate('CREATE TEMPORARY TABLE t1_backup AS SELECT '.$fields.' FROM '.$table);
			$this->db->doUpdate('INSERT INTO '.$this->db->formatTable($tempTableName).' SELECT '.$fields.' FROM '.$tableF);
			$this->removeTable();
			$this->createTable();
			$this->db->doUpdate('INSERT INTO '.$tableF.' SELECT '.$fields.' FROM '.$this->db->formatTable($tempTableName));
			$this->removeTable($tempTableName);
		$this->db->commit();*/
	}
	
	public function removeColumn($colName){
		$this->_hasChangedOrRemovedColumn=true;
		/*$columns=$this->columns();//$this->getColumns($table); $fields=array();//debugVar($modelInfos,$columns);
		foreach($columns as $k=>$col) $columns[$k]=$col['name'];
		foreach($modelInfos['columns'] as $name=>$col){
			if($name != $column){
				if(in_array($name,$columns)) $fields[]=$this->db->formatColumn($name);
				else $fields[]='NULL';
			}
		}
		if(empty($fields)){ $this->removeTable($table); return; }
		$fields=implode(',',$fields);
		$this->transaction(function(DB $db) use(&$fields,&$table,&$modelInfos){
			$tableF=$db->formatTable($table);
			$db->createTable($tempTableName='t'.time().'_backup', $modelInfos['columns'],$modelInfos['primaryKeys'],false,true);
			//$db->doUpdate('CREATE TEMPORARY TABLE t1_backup AS SELECT '.$fields.' FROM '.$table);
			$db->doUpdate('INSERT INTO '.$db->formatTable($tempTableName).' SELECT '.$fields.' FROM '.$tableF);
			$db->removeTable($table);
			$db->createTable($table, $modelInfos['columns'],$modelInfos['primaryKeys'],false);
			$db->doUpdate('INSERT INTO '.$tableF.' SELECT '.$fields.' FROM '.$db->formatTable($tempTableName));
			$db->removeTable($tempTableName);
		});*/
	}
	
	private static function _getColumnDef($col){
		return str_ireplace(' unsigned','',$col['type'])
			.($col['notnull']?' NOT NULL':'')
			.($col['unique']?' UNIQUE':'')
			.($col['default']?' DEFAULT '.$col['default']:'');
	}
	
	public function addIndex($name,$columns,$type=''){
		$columns='`'.implode('`,`',$columns).'`';
		$this->db->doUpdate('CREATE '.$type.' INDEX IF NOT EXISTS `'.$name.'` ON '.$this->db->formatTable($this->tableName).' ('.$columns.')');
	}
	public function removeIndex($name){
		$this->db->doUpdate('DROP INDEX `'.$name.'`');
	}

	public function compareColumn($column,$modelInfo){
		return strtolower($column['type']) != str_replace(' unsigned','',strtolower($modelInfo['type']))
			|| ((int)$column['notnull']) != ((int)$modelInfo['notnull'])
			|| ((string)$column['dflt_value']) != ((string)$modelInfo['default'])
		;
	}
	
	public function getIndexes(){
		$tableIndex=$this->db->doSelectRows('SELECT * FROM sqlite_master WHERE type="index" AND tbl_name='.$this->db->escape($this->tableName));
		$indexes=array();
		foreach($tableIndex as $index)
			if(substr($index['name'],0,17)!=='sqlite_autoindex_'){
				$startLength=strpos($index['sql'],'(')+1;
				$indexes[stripos($index['sql'],' UNIQUE ')?'unique':'nonunique'][$index['name']]
						=array('columns'=>array_flip(array_map(function(&$val){return trim($val,' `');},explode(',',substr($index['sql'],$startLength,strpos($index['sql'],')')-$startLength)))));
			}
		return $indexes;
	}
	
	public function getPrimaryKeys(){
		$pks=array();
		foreach($this->getColumns($this->tableName) as $col)
			if($col['pk']) $pks[]=$col['name'];
		return $pks;
	}
	
	public function removePrimaryKey(){}
	public function addPrimaryKey(){
		$schema=&$this;
		$this->transaction(function(DB $db) use(&$schema){
			$tableF=$db->formatTable($schema->tableName);
			$db->createTable($tempTableName='t'.time().'_backup');
			$db->doUpdate('INSERT INTO '.$db->formatTable($tempTableName).' SELECT * FROM '.$tableF);
			$db->removeTable();
			$db->createTable();
			$db->doUpdate('INSERT INTO '.$tableF.' SELECT * FROM '.$db->formatTable($tempTableName));
			$db->removeTable($tempTableName);
		});
	}
	public function changePrimaryKey(){ return $this->addPrimaryKey(); }
	
	public function disableForeignKeyChecks(){$this->db->doUpdate('PRAGMA foreign_keys = OFF');}
	public function activeForeignKeyChecks(){$this->db->doUpdate('PRAGMA foreign_keys = ON');}
	
	
	public function getForeignKeys(){return array();}
	public function removeForeignKeys(){return false;}
	
	public function addForeignKey($colName,$fk,$dropBefore){
	}
	public function removeForeignKey($colName){
	}
	public function compareForeignKey($dbFk,$refDbName,$refTableName,$refColName,$onDelete=false,$onUpdate=false){
	}
	
}