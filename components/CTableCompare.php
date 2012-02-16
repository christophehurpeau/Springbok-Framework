<?php
class CTableCompare extends CTable{
	/**
	 * @return CTableCompare
	 */
	public static function create($modelCompared,$query=null,$fields=null,$displayedModelFields='id',$withParams=null){
		return new CTableCompare($modelCompared,$query,$fields,$displayedModelFields,$withParams);
	}
	
	public $displayedModelFields,$comparedKeys;
	private $modelCompared,$fieldsCompared;
	public function __construct($modelCompared,&$query,&$fields,&$displayedModelFields,&$withParams){
		$filter=false;
		$query->setFields($this->displayedModelFields=explode(',',$displayedModelFields));
		$this->comparedKeys=array($modelCompared.'1',$modelCompared.'2');
		
		if($fields===null){
			$this->fieldsCompared=$modelCompared::$__modelInfos['colsName'];
			$withParams=array();
		}else{
			if(is_string($fields)) $fields=explode(',',$fields);
			$this->fieldsCompared=$fields;
			
			if($withParams!==null){
				$withParams=array('fields'=>$fields,'with'=>$withParams);
			}else $withParams=array('fields'=>$fields);
		}
		foreach($this->comparedKeys as $key) $query->with($key,$withParams);
		
		$this->modelCompared=&$modelCompared;
		parent::__construct($query,$filter);
		
		if(isset($withParams['with'])){
			foreach($withParams['with'] as $with=>&$opt){
				//$this->fieldsCompared[]=$modelCompared::$_relations[$with]['dataName'];
			}
		}
		
		$modelName=$this->modelName; $relations=$modelName::$_relations;
		foreach($this->comparedKeys as $key=>&$value) $this->comparedKeys[$key]=$relations[$value]['alias'];
	}
	
	public function _setFields($fields,$fromQuery,$export=false){
		$displayedModelFields=array();
		foreach($this->displayedModelFields as $field) $displayedModelFields[$field]=array('title'=>_tF($this->modelName,$field));
		parent::_setFields(array_merge($displayedModelFields,$this->fieldsCompared),false,$export);
		$left=count($displayedModelFields);
		foreach($this->fields as &$field){
			if($left-->0) continue;
			if(isset($field['widthPx'])){
				$field['widthPx']+=29;
			}
		}
	}
	
	public function &_fieldsCompared(){
		return $this->fieldsCompared;
	}
}