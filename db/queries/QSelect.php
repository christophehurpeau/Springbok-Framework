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
	
	
	/**
	 * @return QSelect|self
	 */
	public function sqlSmallResult(){ $this->sqlStart.='SQL_SMALL_RESULT '; return $this; }
	/**
	 * @return QSelect|self
	 */
	public function sqlBigResult(){ $this->sqlStart.='SQL_BIG_RESULT '; return $this; }
	/**
	 * @return QSelect|self
	 */
	public function sqlBufferResult(){ $this->sqlStart.='SQL_BUFFER_RESULT '; return $this; }
	/**
	 * @return QSelect|self
	 */
	public function sqlCache(){ $this->sqlStart.='SQL_CACHE '; return $this; }
	/**
	 * @return QSelect|self
	 */
	public function sqlNoCache(){ $this->sqlStart.='SQL_NO_CACHE '; return $this; }
	/**
	 * @return QSelect|self
	 */
	public function highPriority(){ $this->sqlStart.='HIGH_PRIORITY '; return $this; }
	
	/**
	 * by something
	 * 
	 * <code>
	 * ->by('idAndStatus',array($id,Post::VALID));
	 * </code>
	 * 
	 * You can use the magic method :
	 * 
	 * <code>
	 * ->byIdAndStatus($id,Post::VALID);
	 * </code>
	 * 
	 * @param string
	 * @param array
	 * @return QSelect|self
	 */
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

	/**
	 * Add conditions to the query
	 * 
	 * <code>
	 * ->where(array('status=2')) => WHERE status=2
	 * ->where(array('status'=>2)) => WHERE `status`=2
	 * ->where(array('a'=>1,'b !='=>2)) => WHERE `a`=1 AND `b`!=2
	 * ->where(array('OR'=>array('a'=>1,'b'=>2))) => WHERE `a`=1 OR `b`=2
	 * ->where(array('AND'=>array('a'=>1,'OR'=>array('c'=>1,'b'=>2)))) => WHERE `a`=1 AND (`c`=1 OR `b`=2)
	 * ->where(array('AND'=>array('a'=>1,'AND'=>array(OR'=>array('c'=>1,'b'=>2),'OR'=>array('d'=>3,'e'=>4))))) => WHERE `a`=1 AND ((`c`=1 OR `b`=2) AND (`d`=3 OR `e`=4))
	 * 
	 * ->where(array('status'=>array(1,2,3))) => WHERE `status` IN (1,2,3)
	 * ->where(array('status LIKE'=>'a')) => WHERE `status` LIKE "a"
	 * ->where(array('status LIKE'=>array('a','b'))) => WHERE `status` LIKE "a" OR `status` LIKE "b"
	 * ->where(array('status'=>true)) => WHERE `status` IS NOT NULL
	 * ->where(array('status'=>false)) => WHERE `status` IS NULL
	 * ->where(array('status'=>null)) => WHERE `status` IS NULL
	 * ->where(array('table2.status'=>null)) => WHERE table2.`status` IS NULL
	 * </code>
	 * 
	 * @param array
	 * @param bool
	 * @return QSelect|self
	 */
	public function where($where/*#if DEV*/,$force=false/*#/if*/){
		/*#if DEV*/ if(!$force && !empty($this->where)) throw new Exception('where is not empty ! Are you sure you want erase it ?'); /*#/if*/
		$this->where = $where;
		return $this;
	}
	
	/**
	 * Add one condition to the query
	 * 
	 * @param string|int
	 * @param mixed
	 * @return QSelect|self
	 */
	public function addCondition($key,$value){
		$this->where[$key]=$value;
		return $this;
	}
	
	
	/**
	 * Add one condition to the query
	 * 
	 * @param string|int
	 * @param mixed
	 * @return QSelect|self
	 */
	public function addCond($key,$value){
		$this->where[$key]=$value;
		return $this;
	}
	
	/**
	 * Add one condition to the query
	 * 
	 * @param string|array
	 * @return QSelect|self
	 */
	public function addMCond($value){
		$this->where[]=$value;
		return $this;
	}
	
	
	/**
	 * Set the order
	 * 
	 * @param array|string
	 * @return QSelect|self
	 */
	public function orderBy($orderBy){
		$this->orderBy=$orderBy;
		return $this;
	}
	
	/**
	 * Add order by created field, DESC
	 * 
	 * @param string DESC or ASC
	 * @return QSelect|self
	 */
	public function orderByCreated($orderWay='DESC'){
		$this->orderBy=array('created'=>$orderWay);
		return $this;
	}
	
	/**
	 * Add an order
	 * 
	 * @param int|string
	 * @return QSelect|self
	 */
	public function addOrder($value){
		$this->orderBy[]=$value;
		return $this;
	}
	
	/**
	 * Add an order DESC
	 * 
	 * @param string
	 * @return QSelect|self
	 */
	public function addOrderDesc($value){
		$this->orderBy[$value]='DESC';
		return $this;
	}
	
	/**
	 * Returns the first orderBy
	 * 
	 * @return string
	 */
	public function getOrderBy(){
		if(is_string($this->orderBy)) return $this->orderBy;
		foreach($this->orderBy as $k=>$v)
			return is_int($k) ? $v : $k;
	}
	
	/**
	 * Set the group by
	 * 
	 * @param array|string
	 * @return QSelect|self
	 */
	public function groupBy($groupBy){
		if(is_string($groupBy)) $groupBy=explode(',',$groupBy);
		$this->groupBy=$groupBy;
		return $this;
	}
	
	/**
	 * add a group by
	 * 
	 * @param string
	 * @return QSelect|self
	 */
	public function addGroupBy($groupBy){
		$this->groupBy[]=$groupBy;
		return $this;
	}
	
	/**
	 * Set the having condition
	 * 
	 * @param array|string
	 * @return QSelect|self
	 */
	public function having($having){
		$this->having=$having;
		return $this;
	}
	
	/**
	 * Add a having condition
	 * 
	 * @param array|string
	 * @return QSelect|self
	 */
	public function addHavingCondition($key,$value){
		$this->having[$key]=$value;
		return $this;
	}
	
	/** 
	 * (limit) or (limit, down)
	 * 
	 * @param string|int
	 * @param int
	 * @return QSelect|self
	 */
	public function limit($limit,$down=0){
		if($down>0) $this->limit=((int)$down).','.((int)$limit);
		else $this->limit=$limit;
		return $this;
	}
	
	/**
	 * @return QSelect|self
	 * @see limit
	 */
	public function limit1(){
		$this->limit=1;
		return $this;
	}
	
	
	public function __call($method, $params){
		if (!preg_match('/^by(\w+)$/',$method,$matches))
			throw new \Exception("Call to undefined method {$method}");
		$this->by($matches[1],$params);
		return $this;
	}
	
	/**
	 * @internal
	 * @return string
	 */
	protected function _SqlStart(){
		$sql='SELECT '.$this->sqlStart;
		if($this->calcFoundRows) $sql.='SQL_CALC_FOUND_ROWS ';
		return $sql;
	}
	
	/**
	 * @internal
	 * @param string
	 * @param string
	 * @return string
	 */
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