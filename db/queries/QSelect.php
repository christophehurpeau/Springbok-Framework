<?php
include_once __DIR__.DS.'AQuery.php';

/**
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
abstract class QSelect extends AQuery{
	const LEFT=' LEFT JOIN ',INNER=' INNER JOIN ',RIGHT=' RIGHT JOIN ';
	
	protected $where,$groupBy,$having,$orderBy,$limit;//,$calcFoundRows=false;
	protected $sqlStart='',$calcFoundRows=false,$addByConditions=false;
	
	
	public function sqlSmallResult(){ $this->sqlStart.='SQL_SMALL_RESULT '; return $this; }
	public function sqlBigResult(){ $this->sqlStart.='SQL_BIG_RESULT '; return $this; }
	public function sqlBufferResult(){ $this->sqlStart.='SQL_BUFFER_RESULT '; return $this; }
	public function sqlCache(){ $this->sqlStart.='SQL_CACHE '; return $this; }
	public function sqlNoCache(){ $this->sqlStart.='SQL_NO_CACHE '; return $this; }
	public function highPriority(){ $this->sqlStart.='HIGH_PRIORITY '; return $this; }
	
	public function by($query,$values){
		$fields=explode('And',$query);
		$fields=array_map('lcfirst',$fields);
		$conds=array(); $length=count($fields); $i=-1;
		while(++$i<$length)
			$conds[lcfirst($fields[$i])]=$values[$i];
		$this->where=&$conds;
		$this->addByConditions=&$conds;
		return $this;
	}
	public function where($where){$this->where=$where;return $this;}
	public function addCondition($key,$value){$this->where[$key]=$value;return $this;}
	public function addCond($key,$value){$this->where[$key]=$value;return $this;}
	public function orderBy($orderBy){$this->orderBy=$orderBy;return $this;}
	public function orderByCreated($orderWay='DESC'){$this->orderBy=array('created'=>$orderWay);return $this;}
	public function addOrder($value){ $this->orderBy[]=$value; return $this; }
	public function addOrderDesc($value){ $this->orderBy[$value]='DESC'; return $this; }
	public function getOrderBy(){
		if(is_string($this->orderBy)) return $this->orderBy;
		foreach($this->orderBy as $k=>$v)
			return is_int($k) ? $v : $k;
	}
	
	public function groupBy($groupBy){
		if(is_string($groupBy)) $groupBy=explode(',',$groupBy);
		$this->groupBy=$groupBy;
		return $this;
	}
	public function addGroupBy($groupBy){
		$this->groupBy[]=$groupBy;
		return $this;
	}
	
	public function having($having){$this->having=$having;return $this;}
	public function addHavingCondition($key,$value){$this->having[$key]=$value;return $this;}
	
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
	
	protected function _SqlStart(){
		$sql='SELECT '.$this->sqlStart;
		if($this->calcFoundRows) $sql.='SQL_CALC_FOUND_ROWS ';
		return $sql;
	}
	
	protected function _afterWhere(&$sql,$fieldPrefix=''){
		if(isset($this->groupBy)){
			$sql.=' GROUP BY ';
			foreach($this->groupBy as $field)
				$sql.=$this->formatField($field,false).',';
			$sql=substr($sql,0,-1);
		}
		if(isset($this->having)){
			$sql.=' HAVING ';
			$sql=$this->_condToSQL($this->having,'AND',$sql,'');
		}
		if(isset($this->orderBy)){
			$sql.=' ORDER BY ';
			if(is_string($this->orderBy))
				$sql.=strpos($this->orderBy,'(')!==false ? $this->orderBy : $this->_db->formatField($this->orderBy,false);
			else{
				foreach($this->orderBy as $key=>$value){
					if(is_int($key)){
						if(is_array($value)){
							$sqlOrderBy=$value[0].(isset($value[1]) && $value[1]!==NULL?' '.$value:'').',';
							if(isset($value[2])) foreach($value[2] as $obK=>&$param) $sqlOrderBy=str_replace('$'.$obK,$this->_db->escape($param),$sqlOrderBy);
							$sql.=$sqlOrderBy;
						}elseif(strpos($value,'(')!==false) $sql.=$value.',';
						else $sql.=$this->formatField($value,$value==='created'||$value==='updated'?$fieldPrefix:false).',';
					}else $sql.=$this->formatField($key,$key==='created'||$key==='updated'?$fieldPrefix:false).' '.$value.',';
				}
				$sql=substr($sql,0,-1);
			}
		}
		
		if(isset($this->limit)) $sql.=' LIMIT '.$this->limit;
		return $sql;
	}
}