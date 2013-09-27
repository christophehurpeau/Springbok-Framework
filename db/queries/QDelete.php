<?php
include_once __DIR__.DS.'AQuery.php';
/**
 * Create a DELETE Query
 * 
 * DELETE [LOW_PRIORITY] [QUICK] [IGNORE] FROM table_name
	   [WHERE where_definition]
	   [ORDER BY ...]
	   [LIMIT row_count]
 * @author Christophe Hurpeau
 * @see http://dev.mysql.com/doc/refman/5.0/fr/delete.html
 */
abstract class QDelete extends AQuery{
	protected $where,$orderBy,$limit;
	
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
	 * @return QDelete|self
	 */
	public function by($query,$values){
		$fields=explode('And',$query);
		$fields=array_map('lcfirst',$fields);
		$conds=array(); $length=count($fields); $i=-1;
		while(++$i<$length)
			$conds[lcfirst($fields[$i])]=$values[$i];
		$this->where=&$conds;
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
	 * @return QDelete|self
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
	 * @return QDelete|self
	 */
	public function addCondition($key,$value){
		$this->where[$key]=$value;
		return $this;
	}
	
	/**
	 * Add an order
	 * 
	 * @param array|string
	 * @return QDelete|self
	 */
	public function orderBy($orderBy){
		$this->orderBy=$orderBy;
		return $this;
	}
	/** 
	 * (limit) or (limit, down)
	 * 
	 * @param string|int
	 * @param int
	 * @return QDelete|self
	 */
	public function limit($limit,$down=0){
		if($down>0) $this->limit=((int)$down).','.((int)$limit);
		else $this->limit=$limit;
		return $this;
	}
	/**
	 * @return QDelete|self
	 * @see limit
	 */
	public function limit1(){
		$this->limit=1;
		return $this;
	}
	
	/** @ignore */
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
	