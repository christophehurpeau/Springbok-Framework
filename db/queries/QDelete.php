<?php
include_once __DIR__.DS.'AQuery.php';
/**
 * DELETE [LOW_PRIORITY] [QUICK] [IGNORE] FROM table_name
       [WHERE where_definition]
       [ORDER BY ...]
       [LIMIT row_count]
 * @author Christophe Hurpeau
 */
abstract class QDelete extends AQuery{
	protected $where,$orderBy,$limit;
	
	public function by($query,$values){
		$fields=explode('And',$query);
		$fields=array_map('lcfirst',$fields);
		$conds=array(); $length=count($fields); $i=-1;
		while(++$i<$length)
			$conds[lcfirst($fields[$i])]=$values[$i];
		$this->where=&$conds;
		return $this;
	}
	public function where($where){$this->where=$where;return $this;}
	public function addCondition($key,$value){$this->where[$key]=$value;return $this;}
	public function orderBy($orderBy){$this->orderBy=$orderBy;return $this;}
	/** (limit) or ($limit, down) */
	public function limit($limit,$down=0){
		if($down>0) $this->limit=((int)$down).','.((int)$limit);
		else $this->limit=$limit;
		return $this;
	}
	public function limit1(){$this->limit=1;return $this;}
	
	public function __call($method, $params){
        if (!preg_match('/^by(\w+)$/',$method,$matches))
            throw new \Exception("Call to undefined method {$method}");
        $this->by($matches[1],$params);
        return $this;
    }
	
	
	public function _toSQL(){
		$modelName=$this->modelName; $params=array();
		$sql='DELETE FROM '.$modelName::_fullTableName();
		
		if(isset($this->where)){
			$sql.=' WHERE ';
			$sql=$this->_condToSQL($this->where,'AND',$sql,'');
		}
		
		if(isset($this->groupBy)){
			$sql.=' GROUP BY `'.(is_string($this->groupBy)?$this->groupBy:implode('`,`',$this->groupBy)).'`';
			if(isset($this->having))
				$sql.=' HAVING '.$this->having;
		}
		if(isset($this->orderBy))
			$sql.=' ORDER BY `'.(is_string($this->orderBy)?$this->orderBy:implode('`,`',$this->orderBy)).'`';
		
		if(isset($this->limit) && !$this->_db instanceof DBSQLite) $sql.=' LIMIT '.$this->limit;
		
		return $sql;
	}
	
}
	