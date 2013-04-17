<?php
class DBSchemaMongo extends DBSchema{
	
	public function compareIndexes(){
		//debug('compareIndexes');
		if(!$this->isGenerateSchema()) return;
		$__modelInfos=include Config::$models_infos.$this->modelName;
		
		$modelIndexes=array(0=>array(),1=>array()); $currentIndexes=array(0=>array(),1=>array());
		
		foreach($__modelInfos['indexes'] as $key=>$indexes){
			$changedIndexes=array();
			foreach($indexes as $index){
				$indexName='';
				foreach($index as $field=>$way) $indexName.=str_replace('.','_',$field).'_'.$way.'_';
				$indexName=substr($indexName,0,-1);
				$changedIndexes[$indexName]=$index;
			}
			$modelIndexes[$key]=$changedIndexes;
		}
		
		foreach($this->indexes as $index) $currentIndexes[isset($index['unique'])&&$index['unique']?1:0][$index['name']]=$index['key'];
		
		$modelIndexes[0]['_id_']=array('_id'=>1);
		
		if(!isset($modelIndexes[0])) $modelIndexes[0]=array();
		if(!isset($modelIndexes[1])) $modelIndexes[1]=array();
		
		
		//debug([$currentIndexes,$modelIndexes]);
		
		// 0 = non unique, 1 =unique
		$iPrefix=array(0=>'',1=>'unique');
		foreach(array(array($modelIndexes[0],$currentIndexes[0]),array($modelIndexes[1],$currentIndexes[1])) as $key=>$array){
			list($modelIndexes,$currentIndexes)=$array;
			// Add index
			foreach($a2=array_diff_key($modelIndexes,$currentIndexes) as $indexName=>$fields){
				$this->log('Add index '.$indexName);
				if($this->shouldApply()) debug($this->addIndex($indexName,$fields,$iPrefix[$key]));
			}
			
			// Remove index
			foreach($a1=array_diff_key($currentIndexes,$modelIndexes) as $indexName=>$fields){
				$this->log('Remove index '.$indexName);
				if($this->shouldApply()) debug($this->removeIndex($indexName));
			}
			
			// Change index
			foreach($a3=array_diff_key($modelIndexes,$a1,$a2) as $indexName=>$fields){
				if($fields!==$currentIndexes[$indexName]){
					$this->log('Change index '.$indexName);
					if($this->shouldApply()){
						$this->removeIndex($indexName);
						$this->addIndex($indexName,$fields,$iPrefix[$key]);
					}
				}
			}
		}
	}
	
	
	
	
	public function col(){
		$m=$this->modelName;
		return $m::$__collection;
	}
	
	public function tableExist(){
		foreach($this->db->getTables() as $collection)
			if($collection->getName()===$this->tableName) return true;
		return false;
	}
	
	public function createTable(){
		$m=$this->modelName;
		$m::$__collection=$this->db->db()->createCollection($this->tableName);
	}
	public function removeTable(){
		$this->col()->drop();
	}
	public function reorderTable(){}
	
	public function checkTable(){return true;}
	public function correctTable(){}
	
	public function findColumnsInfos(){
		$this->columns=array();
		$this->getPrimaryKeys();
		$this->getIndexes();
	}
	
	public function compareColumn($column,$modelInfo){
		return false;
	}
	
	public function getIndexes(){
		return $this->indexes=$this->col()->getIndexInfo();
	}
	
	public function getPrimaryKeys(){
		return $this->primaryKeys=array('_id');
	}
	public function removePrimaryKey(){}
	public function addPrimaryKey(){}
	public function changePrimaryKey(){}
	public function disableForeignKeyChecks(){ }
	public function activeForeignKeyChecks(){ }
	
	
	public function getForeignKeys(){return array();}
	public function removeForeignKeys(){return false;}
	
	public function addForeignKey($colName,$fk,$dropBefore,$colInfos){
	}
	public function removeForeignKey($colName){
	}
	public function compareForeignKey($dbFk,$refDbName,$refTableName,$refColName,$onDelete=false,$onUpdate=false){
	}
	
	
	
	public function addColumn($colName,$prev=null){}
	public function changeColumn($colName,$oldColumn,$prev=null){}
	public function removeColumn($colName){}
	public function resetColumnsModifications(){}
	public function applyColumnsModifications(){}
	
	public function addIndex($name,$columns,$type=''){
		return $this->col()->ensureIndex($columns,array('unique'=>$type==='unique','safe'=>true));
	}
	public function removeIndex($name){
		/* http://www.php.net/manual/en/mongocollection.deleteindex.php */
		return $this->db->db()->command(array("deleteIndexes"=>$this->tableName, "index"=>$name));
	}
	
}