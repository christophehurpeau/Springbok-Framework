<?php
/**
 * Abstract class for Query builders
 */
abstract class AQuery{
	private $_params=array();
	
	/**
	 * Name of the model
	 * 
	 * @var string
	 */
	protected $modelName;
	/**
	 * DB instance
	 * 
	 * @var DB
	 */
	protected $_db;
	
	/**
	 * @param string
	 */
	public function __construct($modelName){
		$this->modelName=$modelName;
		$this->_db=$modelName::$__modelDb;
	}
	
	/**
	 * @return string
	 */
	public function getModelName(){
		return $this->modelName;
	}
	
	public abstract function execute();
	
	/**
	 * @ignore
	 * Automaticly added in PhpFile
	 * 
	 * @return mixed
	 */
	public function _execute_(){
		return $this->execute();
	}
	/**
	 * @param mixed
	 * @param string
	 * @param string
	 * @param string
	 * @param bool
	 * @return string
	 */
	protected function _condToSQL($conds,$glue,$sql,$fieldPrefix='',$wrap=false){
		if($wrap) $sql.=' (';
		/*#if DEV */
		if(!is_array($conds)){
			debug('$conds is not array :');
			debugVar($conds);
			debugVar($this);
			exit;
		}
		/*#/if */
		foreach($conds as $key=>&$value){
			if($key==='AND' || $key==='OR') $sql=$this->_condToSQL($value,$key,$sql,$fieldPrefix,true);
			elseif(is_int($key)){
				if(is_array($value))  $sql=$this->_condToSQL($value,'AND',$sql,$fieldPrefix,true);
				else $sql.=$value;
			}else{
				if($pos=strrpos($key,' ')){
					$op=substr($key,$pos).' ';
					$key=substr($key,0,$pos);
					if($op===' NOTIN ') $op=' NOT IN ';
					elseif($op===' NOTLIKE ') $op=' NOT LIKE ';
				}elseif(is_array($value)){
					if(count($value)===1){
						$op='=';
						$value=current($value);
					}else $op=' IN ';
				}else $op='=';
				
				if(is_array($value)){
					if($op===' IN ' || $op===' NOT IN '){
						$values=array();
						foreach($value as &$v){
							if(is_float($v) || is_int($v)) $values[]=$v;
							else $values[]=$this->_db->escape($v);
						}
						$sql.=$this->formatField($key,$fieldPrefix).$op.'('.implode(',',$values).')';
					}elseif($op===' BETWEEN '){
						$sql.=$this->formatField($key,$fieldPrefix).$op.' '.((float)$value[0]).' AND '.((float)$value[1]);
					}else{
						$start=$this->formatField($key,$fieldPrefix).$op;$db=$this->_db;
						$sql.='('.implode($op===' NOT LIKE '?' AND ':' OR ',array_map(function($v) use($start,$db){return $start.$db->escape($v);},$value)).')';
					}
				/*}elseif($value instanceof AQuery){
					list($sqlQuery,$sqlParams)=$value->_toSQL($this->_db);
					$sql.=$this->formatField($key,$fieldPrefix).$op.$sqlQuery;
					$params=array_merge($params,$sqlParams);
				*/}else{
					$sql.=$this->formatField($key,$fieldPrefix);
					if(is_bool($value)) $sql.=($value===true?' IS NOT NULL':' IS NULL');
					else{
						$sql.=$op;
						if(is_float($value) || is_int($value)) $sql.=$value;
						else $sql.=$this->_db->escape($value);
					}
				}
			}
			$sql.=' '.$glue.' ';
		}
		return substr($sql,0,-(2+strlen($glue))).($wrap?')':'');
	}
	
	/**
	 * @param string
	 * @param bool
	 * @return string
	 */
	protected function formatField($field,$fieldPrefix=false){
		if(strpos($field,'(')!==false) return $field;
		if($pos=strpos($field,'.')){
			if($fieldPrefix!=='') $fieldPrefix=substr($field,0,$pos+1);
			$field=substr($field,$pos+1);
		}
		return $fieldPrefix.$this->_db->formatField($field);
	}
}