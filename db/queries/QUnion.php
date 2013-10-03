<?php
/** SELECT ...
UNION [ALL | DISTINCT]
SELECT ...
  [UNION [ALL | DISTINCT]
   SELECT ...]
*/
abstract class QUnion extends QSelect{
	protected $queries=array(),$all,$distinct;
	
	public function all(){
		$this->all=true;
		return $this;
	}
	
	public function distinct(){
		$this->distinct=true;
		return $this;
	}
	
	public function addQuery($query){
		$this->queries[]=$query;
		return $this;
	}
	
	public function _toSQL(){
		$sql='('.implode(') UNION '.($this->all!==NULL?'ALL ':($this->distinct!==NULL?'DISTINCT ':'')).'(',array_map(function(&$query){return $query->_toSQL();},$this->queries)).')';
		return $this->_afterWhere($sql);
	}
}
