<?php
class QFindAll extends QFind{
	private $tabResKey,$groupResBy;
	
	public function execute(){
		//$res=$this->_db->doSelectRows_($query);
		if($this->tabResKey !== null) $res=$this->_db->doSelectAssocObjects($this->_toSQL(),$this,$this->queryResultFields,$this->tabResKey);
		else $res=$this->_db->doSelectObjects($this->_toSQL(),$this,$this->queryResultFields);
		
		if($res){
			if($this->groupResBy!==null){
				$grbf=$this->groupResBy;
				$finalRes=array();
				foreach($res as $key=>&$row) $finalRes[$row->$grbf][$key]=$row;
				$res=$finalRes;
			}
		}
		
		if($this->calcFoundRows===true) $this->calcFoundRows=(int)$this->_db->doSelectValue('SELECT FOUND_ROWS()');
		
		if($res) $this->_afterQuery_objs($res);
		return $res;
	}
	
	public function callback($callback,$callback2=null){
		$sql=$this->_toSQL();
		if($callback2!==null){
			$callback($this->getModelFields());
			$callback=$callback2;
		}
		$this->_db->doSelectObjectsCallback($sql,$this,$this->queryResultFields,$callback);
	}
	
	public function toArray(){
		return SModel::mToArray($this->execute());
	}
	
	public function createCountQuery(){
		$modelName=$this->modelName;
		$countQuery = new QCount($modelName);
		
		$join=$this->joins;$with=$this->with;
		if(!empty($this->where)){
		if($join)
			foreach($join as &$j) $j['fields']=false;
		/*if($with)
			foreach($with as &$w){
				$w['fields']=false;
				if(isset($w['with'])) foreach($w['with'] as &$w2) $w2['fields']=false;
			}
		*/
			$countQuery->where($this->where)->_setJoin($join);
				//->_setWith($with);
		}
		$countQuery->having($this->having);
		if($this->groupBy) $countQuery->setCountField((!empty($this->where)&&($join/*||$with*/)
					 && strpos($this->groupBy[0],'.')===false?$modelName::$__alias.'.':'').$this->groupBy[0],true);
		return $countQuery;
	}
	
	public function calcFoundRows(){
		$this->calcFoundRows=true;
		return $this;
	}
	public function noCalcFoundRows(){
		$this->calcFoundRows=null;
		return $this;
	}
	
	public function hasCalcFoundRows(){
		return $this->calcFoundRows;
	}
	
	public function foundRows(){
		return $this->calcFoundRows;
	}
	
	public function tabResKey($field='id'){
		$this->tabResKey=$field;
		return $this;
	}
	
	public function groupResBy($field){
		$this->groupResBy=$field;
		return $this;
	}
}