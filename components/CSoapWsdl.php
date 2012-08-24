<?php
class CSoapWsdl{
	private $types=array();
	public function getTypes(){ return $this->types; }
	
	public function addArray($type){
		$this->types[]=new PhpWsdlComplex($type.'Array',array(/*new PhpWsdlElement('item',$type)*/),array('isarray'=>true));
	}
	
	public function addModel($modelName,$fields,$relations=array()){
		$el=array();
		$this->addFieldsFromModel($modelName,$el,$fields);
		foreach($relations as $relation=>$relFields){
			if(is_int($relation)){ $relation=$relFields; $relFields=array(); }
			$rel=$modelName::$_relations[$relation];
			if($rel['fieldsInModel']) $this->addFieldsFromModel($rel['modelName'],$el,$relFields);
			else $el[]=new PhpWsdlElement($rel['dataName'],$rel['modelName'].(in_array($rel['reltype'],array('hasMany','hasManyThrough'))?'Array':''));
		}
		$this->types[]=new PhpWsdlComplex($modelName,$el);
	}
	
	private function addFieldsFromModel($modelName,&$el,$fields){
		$propDef=$modelName::$__PROP_DEF; $modelInfos=$modelName::$__modelInfos;
		if(empty($fields)) $fields=array_keys($propDef);
		foreach($fields as $key=>$field){
			$settings=array('nillable'=>'true');
			if(!empty($modelInfos['columns'][$field]['comment'])) $settings['docs']=$modelInfos['columns'][$field]['comment'];
			$el[]=new PhpWsdlElement($field,$propDef[$field]['type'],$settings);
		}
	}
	
	
	public static function param($name,$type,$settings=null){
		if(substr($type,0,7)==='array[]') return new PhpWsdlParam($name,substr($type,7).'Array',$settings);
		return new PhpWsdlParam($name,$type,$settings);
	}
}