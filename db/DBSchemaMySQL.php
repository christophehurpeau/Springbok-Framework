<?php
class DBSchemaMySQL extends DBSchema{
	//private static $allTables;
	
	private static $_allStatusTables=array();
	private function _tableStatus(){
		$dbName=$this->db->_getName();
		if(!isset(self::$_allStatusTables[$dbName]))
			self::$_allStatusTables[$dbName]=$this->db->doSelectListRows('SHOW TABLE STATUS');
		return isset(self::$_allStatusTables[$dbName][$this->tableName]) ? 
					self::$_allStatusTables[$dbName][$this->tableName] : null;
	}
	
	public function tableExist(){
		//return $this->db->doSelectValue("SHOW TABLES LIKE ".$this->db->escape($this->tableName));
		/*$dbName=$this->db->_getName();
		if(!isset(self::$allTables[$dbName]))
			self::$allTables[$dbName]=$this->db->doSelectValues('SHOW TABLES');
		return in_array($this->tableName,self::$allTables[$dbName]);*/
		return $this->_tableStatus()!==null;
	}
	
	public function createTable(){
		$sql='CREATE TABLE '.$this->db->formatTable($this->tableName).' ( ';
		foreach($this->modelInfos['columns'] as $colname=>&$coldef)
			$sql.=$this->db->formatColumn($colname).' '.self::_getColumnDef($coldef).', ';
		
		if(!empty($this->modelInfos['primaryKeys'])) $sql.='PRIMARY KEY (`'.implode('`,`',$this->modelInfos['primaryKeys']).'`)';
		else $sql=substr($sql,0,-2);
		$indexes=&$this->modelInfos['indexes'];
		if(!empty($indexes[0])) foreach($indexes[0] as $index)
			$sql.=', KEY '.$this->_getIndexDef($index,'',true);
		if(!empty($indexes[1])) foreach($indexes[1] as $index)
			$sql.=', CONSTRAINT '.$this->_getIndexDef($index,'UNIQUE',true);
		$sql.=') ENGINE='.(empty($this->modelInfos['Engine'])?'InnoDB':$this->modelInfos['Engine']).' DEFAULT CHARSET=utf8 COLLATE utf8_general_ci';
		$this->doUpdate($sql,true);
	}
	
	public function removeTable(){
		$this->doUpdate('DROP TABLE '.$this->db->formatTable($this->tableName),true);
	}
	
	public function reorderTable(){
		$sql='ALTER TABLE '.$this->db->formatTable($this->tableName);
		$prev=false;
		foreach($this->modelInfos['columns'] as $colname=>&$coldef){
			$sql.=' MODIFY '.$this->db->formatColumn($colname).' '.self::_getColumnDef($coldef)/*.($coldef['autoincrement']?' PRIMARY KEY':'')*/
								.' '.($prev?'AFTER '.$this->db->formatColumn($prev):'FIRST').', ';
			$prev=$colname;
		}
		$this->doUpdate(substr($sql,0,-2),true);
	}
	
	public function checkTable(){
		//$status=$this->db->doSelectRow('SHOW TABLE STATUS LIKE '.$this->db->escape($this->tableName));
		$status=$this->_tableStatus();
		return $status['Engine']===(empty($this->modelInfos['Engine'])?'InnoDB':$this->modelInfos['Engine']) && $status['Collation']==='utf8_general_ci'
			&& ((empty($this->modelInfos['comment']) && empty($status['Comment'])) || (!empty($this->modelInfos['comment']) && $this->modelInfos['comment']===$status['Comment']));
	}
	
	public function correctTable(){
		$sql='ALTER TABLE '.$this->db->formatTable($this->tableName)
			.' COMMENT='.(empty($this->modelInfos['comment'])?'""':$this->db->escape($this->modelInfos['comment']))
			.' ENGINE='.(empty($this->modelInfos['Engine'])?'InnoDB':$this->modelInfos['Engine'])
			.' DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci';
		//try{
			$this->doUpdate($sql,true);
		/*}catch(Exception $e){
			$foreignKeys=$this->getForeignKeys();
			foreach($foreignKeys as $col=>$fk) $this->removeForeignKey($fk);
			$this->doUpdate($sql,false);
		}*/
	}
	
	
	public function findColumnsInfos(){
		$this->getColumns();
		$this->getIndexes();
		$this->getForeignKeys();
	}
	
	public function getColumns(){
		$cols=$this->db->doSelectRows('SHOW FULL COLUMNS FROM '.$this->db->formatTable($this->tableName));
		$pks=array();
		foreach($cols as &$col){
			$col['name']=$col['Field'];
			$col['type']=$col['Type'];
			$col['notnull']=($col['Null']=='NO');
			$col['autoincrement']=($col['Extra']=='auto_increment');
			$col['default']=$col['Default'];
			$col['comment']=$col['Comment'];
			unset($col['Field'],$col['Type'],$col['Null'],$col['Default'],$col['Comment']);
			if($col['Key']==='PRI') $pks[]=$col['name'];
		}
		$this->primaryKeys=$pks;
		return $this->columns=$cols;
	}
	
	private $_alterOperations;
	public function resetColumnsModifications(){ $this->_alterOperations=array(); }
	public function applyColumnsModifications(){
		if(!empty($this->_alterOperations))
			$this->doUpdate('ALTER TABLE '.$this->db->formatTable($this->tableName).' '.implode(', ',$this->_alterOperations),true);
	}
	
	public function addColumn($colName,$prev=null){
		$colDef=&$this->modelInfos['columns'][$colName];
		$this->_alterOperations[]='ADD '.($fCol=$this->db->formatColumn($colName)).' '.self::_getColumnDef($colDef,true).($prev!==null?($prev?' AFTER '.$this->db->formatColumn($prev):' FIRST'):'');
		//.($colDef['autoincrement']?(',ADD PRIMARY KEY ('.$fCol.')'):($colDef['unique']?', ADD '.$this->_getIndexDef(array($fCol),'UNIQUE',false):($colDef['index']?', ADD '.$this->_getIndexDef(array($fCol),'',false):'')));
		if($colDef['unique']) $this->_alterOperations[]='ADD '.$this->_getIndexDef(array($colName),'UNIQUE',false);
		elseif($colDef['index']) $this->_alterOperations[]='ADD '.$this->_getIndexDef(array($colName),'',false);
	}
	public function changeColumn($colName,$oldColumn,$prev=null){
		$column=$this->db->formatColumn($colName);
		/*$this->db->doUpdate('ALTER TABLE '.$this->db->formatTable($this->tableName).' MODIFY '.$column.' '.self::_getColumnDef($this->modelInfos['columns'][$colName])
			.($prev!==null?($prev?' AFTER '.$this->db->formatColumn($prev):' FIRST'):''));*/
		if($oldColumn['type']==='datetime' && ($this->modelInfos['columns'][$colName]['type']==='int(11)' || $this->modelInfos['columns'][$colName]['type']==='int(10)')){
			$tmpColumn=$this->db->formatColumn($colName.'_temp');
			$this->doUpdate('ALTER TABLE '.$this->db->formatTable($this->tableName).' ADD '.$tmpColumn.' '.($colDefSQL=self::_getColumnDef($this->modelInfos['columns'][$colName])),true);
			$this->doUpdate('UPDATE '.$this->db->formatTable($this->tableName).' SET '.$tmpColumn.'=UNIX_TIMESTAMP('.$column.')',true);
			$this->_alterOperations[]='DROP '.$column;
			$this->_alterOperations[]='CHANGE '.$tmpColumn.' '.$column.' '.$colDefSQL;
			return;
		}elseif(($oldColumn['type']==='int(11)' || $oldColumn['type']==='int(10)') && $this->modelInfos['columns'][$colName]['type']==='datetime'){
			$tmpColumn=$this->db->formatColumn($colName.'_temp');
			$this->doUpdate('ALTER TABLE '.$this->db->formatTable($this->tableName).' ADD '.$tmpColumn.' '.($colDefSQL=self::_getColumnDef($this->modelInfos['columns'][$colName])),true);
			$this->doUpdate('UPDATE '.$this->db->formatTable($this->tableName).' SET '.$tmpColumn.'=FROM_UNIXTIME('.$column.')',true);
			$this->_alterOperations[]='DROP '.$column;
			$this->_alterOperations[]='CHANGE '.$tmpColumn.' '.$column.' '.$colDefSQL;
			return;
		}/*elseif($oldColumn['type']==='char(0)' && $this->modelInfos['columns'][$colName]['type']==='datetime'){}*/
		$this->_alterOperations[]='MODIFY '.$column.' '.self::_getColumnDef($this->modelInfos['columns'][$colName]).($prev!==null?($prev?' AFTER '.$this->db->formatColumn($prev):' FIRST'):'');
	}
	public function removeColumn($colName){
		try{
			$constraintname='fk_'.$this->tableName.'_'.$colName;
			$this->doUpdate('ALTER TABLE '.$this->db->formatTable($this->tableName).' DROP FOREIGN KEY `'.$constraintname.'`',true);
			$this->doUpdate('ALTER TABLE '.$this->db->formatTable($this->tableName).' DROP INDEX `'.$constraintname.'`',true);
		}catch(DBException $ex){}
		//$this->db->doUpdate('ALTER TABLE '.$this->db->formatTable($this->tableName).' DROP '.$this->db->formatColumn($colName));
		$this->_alterOperations[]='DROP '.$this->db->formatColumn($colName);
	}
	
	private static function _getColumnDef($col,$aiPk=false){
		$colType=strtolower($col['type']);
		return $col['type']
			.(substr($colType,0,7)==='varchar' || substr($colType,0,4)==='char' || substr($colType,0,4)==='enum'  ?' CHARACTER SET utf8 COLLATE utf8_general_ci':'')
			.($col['notnull']?' NOT NULL':'')
			.($col['autoincrement']?($aiPk?' PRIMARY KEY':'').' AUTO_INCREMENT':'')
			.($col['default']?' DEFAULT '.(is_numeric($col['default']) || $col['default']==='CURRENT_TIMESTAMP'?$col['default']:'"'.trim($col['default'],'"').'"'):'')
			.($col['default']===0?' DEFAULT 0':'')
			.($col['comment']?' COMMENT "'.$col['comment'].'"':'');
	}
	
	private function _getIndexDef($columns,$type='',$create){
		$_columns='`'.implode('`,`',$columns).'`';
		$name=(empty($type)?'':strtolower($type).'_').implode('|',$columns);
		return ($create?'':($type?' '.$type:' INDEX')).'`'.$name.'`'.($type==''||!$create?'':' '.$type).' ('.$_columns.')';
	}
	
	public function addIndex($name,$columns,$type=''){
		/*$indexes=$this->getIndexes();
		foreach($indexes as $key_name=>$index)
			if($index['key_name']==$name) return;*/
		$this->doUpdate('CREATE'.(empty($type)?'':' '.$type).' INDEX `'.$name.'` ON '.$this->db->formatTable($this->tableName).' ( `'.implode('`,`',$columns).'` )',true);
	}
	
	public function removeIndex($name){
		try{
			$this->doUpdate('ALTER TABLE '.$this->db->formatTable($this->tableName).' DROP INDEX `'.$name.'`',true);
		}catch(DBException $ex){}
	}
	
	public function getIndexes(){
		$tableIndex=$this->db->doSelectRows('SHOW INDEX FROM '.$this->db->formatTable($this->tableName));
		$indexes=array();
		foreach($tableIndex as $index)
			if($index['Key_name']!=='PRIMARY'){
				$indexTab=&$indexes[$index['Non_unique']?'nonunique':'unique'][$index['Key_name']];
				if($indexTab===null) $indexTab=array('collation'=>$index['Collation'],'Sub_part'=>$index['Sub_part'],'Packed'=>$index['Packed'],'Null'=>$index['Null'],'index_type'=>$index['Index_type'],'Comment'=>$index['Comment']);
				$indexTab['columns'][$index['Column_name']]=$index['Cardinality'];
			}
		return $this->indexes=$indexes;
	}
	
	public function getPrimaryKeys(){
		if($this->primaryKeys!==null) return $this->primaryKeys;
		return $this->db->doSelectValues('SELECT `COLUMN_NAME` FROM `information_schema`.`COLUMNS` WHERE `TABLE_SCHEMA`=DATABASE() AND `TABLE_NAME`='.$this->db->escape($this->tableName).' AND `COLUMN_KEY`="PRI"');
	}
	public function removePrimaryKey(){
		return $this->doUpdate('ALTER TABLE '.$this->db->formatTable($this->tableName).' DROP PRIMARY KEY',true);
	}
	public function addPrimaryKey(){
		return $this->doUpdate('ALTER TABLE '.$this->db->formatTable($this->tableName).' ADD PRIMARY KEY ('.'`'.implode('`,`',$this->modelInfos['primaryKeys']).'`)',true);
	}
	public function changePrimaryKey(){
		if(empty($this->modelInfos['primaryKeys'])){
			try{
				return $this->removePrimaryKey();
			}catch(DBException $e){
				return false;
			}
		}
		return $this->doUpdate('ALTER TABLE '.$this->db->formatTable($this->tableName).' DROP PRIMARY KEY,ADD PRIMARY KEY ('.'`'.implode('`,`',$this->modelInfos['primaryKeys']).'`)',true);
	}
	

	public function compareColumn($column,$modelInfo){
		return strtolower($column['type']) != strtolower($modelInfo['type'])
			|| !($column['Collation']===null || $column['Collation'] === 'utf8_general_ci' )
			|| ((bool)$column['notnull']) != ((bool)$modelInfo['notnull'])
			|| ($modelInfo['default']===0 && $column['default']!=='0')
			|| ($modelInfo['default']!==0 && !(
					(!$modelInfo['default'] && empty($column['default'])) ||
					$column['default']===$modelInfo['default'] ||
					($column['default']!==null && ((string)$column['default']) === trim((string)$modelInfo['default'],'"'))
				))
			|| ((bool)$column['autoincrement']) != ((bool)$modelInfo['autoincrement'])
			|| (!( (!$modelInfo['comment'] && empty($column['comment'])) ||
				$column['comment']===$modelInfo['comment'] || ($column['comment']!==null && ((string)$column['comment']) === trim((string)$modelInfo['comment'],'"'))))
		;
	}
	/*
	public function addForeignRelationships($tableName,$modelInfos){
		// ALTER TABLE `utilisateurs_relation`
			// ADD CONSTRAINT `utilisateurs_relation_ibfk_3` FOREIGN KEY (`d_groupe`) REFERENCES `groupes_personnels` (`uuid`) ON DELETE SET NULL,
			// ADD CONSTRAINT `utilisateurs_relation_ibfk_2` FOREIGN KEY (`lie_a`) REFERENCES `utilisateurs` (`login`) ON DELETE CASCADE ON UPDATE CASCADE;
		$constraints=array();
		foreach($modelInfos['columns'] as $name=>$col)
			if(isset($col['ForeignKey'])) $constraints[$name]=$col['ForeignKey'];
		if(!empty($constraints)){
			$sql='ALTER TABLE '.$this->db->formatTable($tableName);
			foreach($constraints as $colname=>$c){
				$constraintname='fk_'.$tableName.'_'.$colname;
				$sql.=' ADD CONSTRAINT `'.$constraintname.'` FOREIGN KEY ('.$this->formatColumn($colname).') REFERENCES '.$this->formatTable($c[0]::_fullTableName()).' ('.$this->formatColumn($c[1]).')';
				if(isset($c['onDelete'])) $sql.=' ON DELETE '.$c['onDelete'];
				if(isset($c['onUpdate'])) $sql.=' ON UPDATE '.$c['onUpdate'];
				$sql.=',';
			}
			$sql=substr($sql,0,-1);
			$this->doUpdate($sql);
		} 
	}*/
	public function disableForeignKeyChecks(){$this->doUpdate('SET FOREIGN_KEY_CHECKS=0',true);}
	public function activeForeignKeyChecks(){$this->doUpdate('SET FOREIGN_KEY_CHECKS=1',true);}
	
	public function addForeignKey($colName,$fk,$dropBefore,$colInfos){
		list($refTableName,$refColName,$onDelete,$onUpdate)=array(
							($fk[0]::$__dbName != $this->db->_getName() ? $fk[0]::$__modelDb->getDbName().'.' : '').$this->db->formatTable($fk[0]::_fullTableName()),$fk[1],
							isset($fk['onDelete'])?$fk['onDelete']:false,isset($fk['onUpdate'])?$fk['onUpdate']:false);
		$constraintname='fk_'.$this->tableName.'_'.$colName;
		$sql='ALTER TABLE '.$this->db->formatTable($this->tableName)
			.($dropBefore!==false?' DROP FOREIGN KEY `'.$dropBefore['name'].'`,':'')
			.' ADD '.($colInfos['unique']?'':'CONSTRAINT `'.$constraintname.'` ')
				.'FOREIGN KEY ('.$this->db->formatColumn($colName).') REFERENCES '.$refTableName.' ('.$this->db->formatColumn($refColName).')';
		if($onDelete) $sql.=' ON DELETE '.$onDelete;
		if($onUpdate) $sql.=' ON UPDATE '.$onUpdate;
		try{
			$this->doUpdate($sql,true);
		}catch(DBException $e){
			try{
				$this->removeForeignKey(array('name'=>$constraintname),false);
				$this->doUpdate($sql,false);
			}catch(DBException $e2){
				throw $e;
			}
		}
	}
	public function removeForeignKey($fk,$keepQuery=true){
		$this->doUpdate('ALTER TABLE '.$this->db->formatTable($this->tableName).' DROP FOREIGN KEY `'.$fk['name'].'`',$keepQuery);
		try{
			$this->doUpdate('ALTER TABLE '.$this->db->formatTable($this->tableName).' DROP INDEX `'.$fk['name'].'`',$keepQuery);
		}catch(DBException $ex){}
	}

	public function compareForeignKey($dbFk,$refDbName,$refTableName,$refColName,$onDelete=false,$onUpdate=false){
		if($onUpdate===false) $onUpdate='RESTRICT';
		if($onDelete===false) $onDelete='RESTRICT';
		return !(((empty($refDbName) && empty($dbFk['referenced_database'])) || $refDbName===$dbFk['referenced_database']) && $dbFk['referenced_table']===$refTableName && $dbFk['referenced_column']===$refColName && $dbFk['onUpdate']===$onUpdate &&$dbFk['onDelete']===$onDelete);
	}
	
	
	public function getForeignKeys(){
		$createTable=$this->db->doSelectRow_('SHOW CREATE TABLE '.$this->db->formatTable($this->tableName));
		$createTable=$createTable[1];
		//preg_match_all('/CONSTRAINT `(.*)` FOREIGN KEY \(`(.*)`\) REFERENCES `(.*)` \(`(.*)`\)/',$createTable,$matches);
		//debug($createTable);
		$foreignsKeys=array();
		if(preg_match_all('/\sCONSTRAINT `([\w\-]+)` FOREIGN KEY \(`([\w\-]+)`\) REFERENCES (?:`([\w\-]+)`.)?`([\w\-]+)` \(`([\w\-]+)`\)(?: ON DELETE (RESTRICT|CASCADE|SET NULL|NO ACTION))?(?: ON UPDATE (RESTRICT|CASCADE|SET NULL|NO ACTION))?,?\n/s', $createTable, $matches, PREG_SET_ORDER)){
			for ($i = 0; ($i < count($matches)); $i++){
				list($constraint,$name,$column,$referenced_database, $referenced_table,$referenced_column) = $matches[$i];
				$onDelete=empty($matches[$i][6])?'RESTRICT':$matches[$i][6];
				$onUpdate=empty($matches[$i][7])?'RESTRICT':$matches[$i][7];
				$foreignsKeys[$column]=compact('name','column','referenced_database','referenced_table','referenced_column','onDelete','onUpdate');
			}
		}
		//debugVar($createTable,$foreignsKeys);
		return $this->foreignKeys=$foreignsKeys;

		
		
		//return isset(self::$_allForeignKeys[$this->getDb()->_getName()][$this->tableName])?self::$_allForeignKeys[$this->getDb()->_getName()][$this->tableName]:array();
	}
	
	private static $_allForeignKeys;
	public function getHasManyForeignKeys(){
		if(!isset(self::$_allForeignKeys[$this->getDb()->_getName()])){
			$res=$this->db->doSelectRows_('SELECT SQL_CACHE  kcu.TABLE_NAME,kcu.CONSTRAINT_NAME,kcu.COLUMN_NAME,kcu.REFERENCED_TABLE_SCHEMA,kcu.REFERENCED_TABLE_NAME,kcu.REFERENCED_COLUMN_NAME,rc.UPDATE_RULE,rc.DELETE_RULE'
				.' FROM '.$this->db->formatTable('information_schema').'.'.$this->db->formatTable('KEY_COLUMN_USAGE').' kcu'
				.' LEFT JOIN '.$this->db->formatTable('information_schema').'.'.$this->db->formatTable('REFERENTIAL_CONSTRAINTS').' rc ON kcu.CONSTRAINT_SCHEMA=rc.CONSTRAINT_SCHEMA AND kcu.CONSTRAINT_NAME=rc.CONSTRAINT_NAME AND kcu.TABLE_NAME=rc.TABLE_NAME'
				.' WHERE kcu.CONSTRAINT_SCHEMA=DATABASE() AND kcu.REFERENCED_TABLE_NAME IS NOT NULL'
					/*.' AND kcu.TABLE_NAME='.$this->db->escape($this->tableName)*/);
			if(empty($res)) self::$_allForeignKeys[$this->getDb()->_getName()]=array();
			foreach($res as &$r)
				self::$_allForeignKeys[$this->getDb()->_getName()][$r[0]][$r[2]]=array_combine(array('tableName','name','column','referenced_database','referenced_table','referenced_column','onUpdate','onDelete'),$r);
		}
		
		
		$hM=array();
		foreach(self::$_allForeignKeys as $dbName=>$dbs){
			foreach($dbs as $tableName=>$fks){
				if($this->getDb()->_getName()===$dbName && $tableName===$this->tableName) continue;
				foreach($fks as &$fk){
					if($fk['referenced_database']==$this->getDb()->getDatabaseName() && $fk['referenced_table']===$this->tableName)
						$hM[]=&$fk;
				}
			}
		}
		return $hM;
	}


	/*
	public function removeForeignKeys($tableName){
		$rows=$this->doSelectValues('SELECT CONSTRAINT_NAME'
			.' FROM '.$this->formatTable('information_schema').'.'.$this->formatTable('KEY_COLUMN_USAGE')
			.' WHERE CONSTRAINT_SCHEMA=DATABASE() AND REFERENCED_TABLE_NAME IS NOT NULL'
				.' AND TABLE_NAME='.$this->escape($tableName));
		if(!empty($rows)){
			$sql='ALTER TABLE '.$this->formatTable($tableName);
			foreach($rows as $constraintname)
				$sql.=' DROP FOREIGN KEY `'.$constraintname.'`,';
			$sql=substr($sql,0,-1);
			$this->doUpdate($sql);
		}
	}
*/

	public function getTriggers(){
		return $this->db->doSelectListRows('SHOW TRIGGERS WHERE `table` LIKE '.$this->db->escape($this->tableName));
	}
}