<?php
class DBSchemaMongo extends DBSchema{
	public function col(){
		$m=$this->modelName;
		return $m::$__collection;
	}
	
	public function tableExist(){
		debugVar($this->db->getTables());exit;
		return $this->db->getTables();
	}
	
	public function createTable(){
		$m::$__collection=$this->db->createCollection($this->tableName);
	}
	public function removeTable(){
		$this->col()->drop();
	}
	public function reorderTable(){}
	
	public function checkTable(){return true;}
	public function correctTable(){}
	
	public function getColumns(){ return array(); }
	
	public function compareColumn($column,$modelInfo){
		return false;
	}
	
	public function getIndexes(){
		return $this->col()->getIndexInfo();
	}
	
	public function getPrimaryKeys(){
		return array('_id');
	}
	public function removePrimaryKey(){}
	public function addPrimaryKey(){}
	public function changePrimaryKey(){}
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
	
	
	
	public function addColumn($colName,$prev=null){}
	public function changeColumn($colName,$oldColumn,$prev=null){}
	public function removeColumn($colName){}
	public function resetColumnsModifications(){}
	public function applyColumnsModifications(){}
	
	public function addIndex($name,$columns,$type=''){
		
	}
	public function removeIndex($name){}
	
}