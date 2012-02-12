<?php
class CTableOne extends CTable{
	public static function create($query){
		/* DEV */if(!($query instanceof QFindOne)) throw new Exception('Your query must be an instance of QFindOne'); /* /DEV */
		return new CTableOne($query);
	}
	
	private $result;
	public function execute($exportOutput=null){
		if($this->executed===true) return; $this->executed=true;
		$modelName=&$this->modelName;
		$this->queryFields=$fields=$this->query->getFields();
		if($fields===NULL) $fields=$modelName::$__modelInfos['colsName'];
		
		$belongsToFields=&$this->belongsToFields;
		if($belongsToFields && isset($modelName::$__modelInfos['belongsToRelations'])) foreach($modelName::$__modelInfos['belongsToRelations'] as $relationKey){
			$rel=$modelName::$_relations[$relationKey];
			if(in_array($fieldName=$rel['foreignKey'],$fields)) $belongsToFields[$fieldName]=$relationKey;
			foreach($belongsToFields as $field=>$relKey) $this->query->with($relKey,array('fields'=>'name'));
		}
		$this->result=$this->query->execute();
		$this->results=array(&$this->result);
		
		$this->modelFields=$this->query->getModelFields();
		
		if($this->result !== false){
			if($this->fields !== NULL) $this->_setFields($this->fields,false);
			else $this->_setFields($fields,true);
		}
	}

	public function getTotalResults(){return $this->result===false ? 0 : 1; }
	public function &getResult(){return $this->result; }
	public function &getResults(){return $this->results; }
	public function hasPager(){return false; }
}
