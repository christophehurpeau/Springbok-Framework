<?php
include_once __DIR__.DS.'AQuery.php';
/**
 * Update Query
 * 
 * UPDATE [LOW_PRIORITY] [IGNORE] tbl_name
	SET col_name1=expr1 [, col_name2=expr2 ...]
	[WHERE where_definition]
	[ORDER BY ...]
	[LIMIT row_count]
 */
class QUpdate extends AQuery{
	private $values,$where,$limit=null,$ignore=false,$orderBy,$updatedField;
	
	/**
	 * @param string
	 * @param string
	 * @internal
	 */
	public function __construct($modelName,$updatedField=null){
		parent::__construct($modelName);
		$this->updatedField=$updatedField;
	}
	
	/**
	 * @param array
	 * @return QUpdate|self
	 */
	public function values($values){
		$this->values=$values;
		return $this;
	}
	
	/**
	 * @param array
	 * @return QUpdate|self
	 */
	public function set($values){
		$this->values=$values;
		return $this;
	}
	
	/**
	 * @param array
	 * @return QUpdate|self
	 */
	public function where($conditions){
		$this->where=$conditions;
		return $this;
	}
	
	/**
	 * Set the updated field
	 * 
	 * @param string
	 * @return QUpdate|self
	 */
	public function updatedField($field){
		$this->updatedField=$field;
		return $this;
	}
	/**
	 * Remove the updated field
	 * 
	 * @return QUpdate|self
	 */
	public function doNotUpdateUpdatedField(){
		$this->updatedField=null;
		return $this;
	}
	
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
	 * @return QUpdate|self
	 */
	public function by($query,$values){
		$fields=explode('And',$query);
		$fields=array_map('lcfirst',$fields);
		$conds=array(); $length=count($fields); $i=-1;
		while(++$i<$length)
			$conds[lcfirst($fields[$i])]=$values[$i];
		$this->where=$conds;
		return $this;
	}
	
	/**
	 * @param string
	 * @param array
	 * @return QUpdate|self
	 */
	public function __call($method, $params){
		if (!preg_match('/^by(\w+)$/',$method,$matches))
			throw new \Exception("Call to undefined method {$method}");
		$this->by($matches[1],$params);
		return $this;
	}
	
	/**
	 * @param array|string
	 * @return QUpdate|self
	 */
	public function orderBy($orderBy){
		$this->orderBy=$orderBy;
		return $this;
	}
	
	/**
	 * Add order by created field, DESC
	 * 
	 * @param string DESC or ASC
	 * @return QUpdate|self
	 */
	public function orderByCreated($orderWay='DESC'){
		$this->orderBy=array('created'=>$orderWay);
		return $this;
	}
	
	/**
	 * Add an order
	 * 
	 * @param int|string
	 * @return QUpdate|self
	 */
	public function addOrder($value){
		$this->orderBy[]=$value;
		return $this;
	}
	
	/**
	 * @return QUpdate|self
	 */
	public function ignore(){
		$this->ignore=true;
		return $this;
	}
	
	
	/** 
	 * (limit) or (limit, down)
	 * 
	 * @param string|int
	 * @param int
	 * @return QUpdate|self
	 */
	public function limit($limit,$down=0){
		if($down>0) $this->limit=((int)$down).','.((int)$limit);
		else $this->limit=$limit;
		return $this;
	}
	
	/**
	 * @return QUpdate|self
	 * @see limit
	 */
	public function limit1(){
		$this->limit=1;
		return $this;
	}
	
	/**
	 * @internal
	 * @return string
	 */
	public function _toSQL(){
		$modelName=$this->modelName;
		$sql='UPDATE ';
		if($this->ignore!==false) $sql.='IGNORE ';
		$sql.=$modelName::_fullTableName().' SET ';
		if(!empty($this->values)) foreach($this->values as $key=>$value){
			if($key===$this->updatedField){
				if($value===false) $this->updatedField=null;
				continue;
			}
			$sql.=$this->_db->formatField($key).'=';
			if($value===null) $sql.='NULL,';
			elseif(is_array($value)) $sql.=$value[0].',';
			elseif(is_int($value) || is_float($value)) $sql.=$value.',';
			elseif(is_bool($value)) $sql.=($value===true?'""':'NULL').',';
			else $sql.=$this->_db->escape($value).',';
		}
		if($this->updatedField!==null) $sql.=$this->_db->formatField($this->updatedField).'=NOW(),'; //UNIX_TIMESTAMP()
		$sql=substr($sql,0,-1);
		
		if(isset($this->where)){
			$sql.=' WHERE ';
			$sql=$this->_condToSQL($this->where,'AND',$sql,'');
		}
		
		if(isset($this->orderBy)){
			$sql.=' ORDER BY ';
			if(is_string($this->orderBy))
				$sql.=strpos($this->orderBy,'(')!==false ? $this->orderBy : $this->_db->formatField($this->orderBy);
			else{
				foreach($this->orderBy as $key=>$value){
					if(is_int($key)){
						if(is_array($value)){
							$sqlOrderBy=$value[0].(isset($value[1]) && $value[1]!==NULL?' '.$value:'').',';
							if(isset($value[2])) foreach($value[2] as $obK=>&$param) $sqlOrderBy=str_replace('$'.$obK,$this->_db->escape($param),$sqlOrderBy);
							$sql.=$sqlOrderBy;
						}elseif(strpos($value,'(')!==false) $sql.=$value.',';
						else $sql.=$this->_db->formatField($value).',';
					}else $sql.=$this->_db->formatField($key).' '.$value.',';
				}
				$sql=substr($sql,0,-1);
			}
		}
		
		if(isset($this->limit) && !$this->_db instanceof DBSQLite) $sql.=' LIMIT '.$this->limit;
		return $sql;
	}

	public function execute(){
		$res=$this->_db->doUpdate($this->_toSQL());
		return $res;
	}
}