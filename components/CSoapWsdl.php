<?php
/**
 * Create WSDL for SOAP
 * 
 */
class CSoapWsdl{
	private $types=array();
	public function getTypes(){ return $this->types; }
	
	/**
	 * Add an array of elements
	 * 
	 * @param type of the elements
	 * @return void
	 */
	public function addArray($type){
		$this->types[]=new PhpWsdlComplex($type.'Array',array(/*new PhpWsdlElement('item',$type)*/),array('isarray'=>true));
	}
	
	/**
	 * Add an array of ints
	 * 
	 * @return void
	 */
	public function addIntArray(){
		$this->types[]=new PhpWsdlComplex('IntArray','int');
	}
	
	/**
	 * Add a model
	 * 
	 * @param string modelName
	 * @param array list of fields from the model
	 * @param array list of relations to include in the model
	 * @param string data name of the model
	 * @return void
	 */
	public function addModel($modelName,$fields,$relations=array(),$dataName=null){
		if($dataName===null) $dataName=$modelName;
		$el=array();
		$this->addFieldsFromModel($modelName,$el,$fields);
		foreach($relations as $relation=>$relFields){
			if(is_int($relation)){ $relation=$relFields; $relFields=array(); }
			$rel=$modelName::$_relations[$relation];
			if($rel['fieldsInModel']) $this->addFieldsFromModel($rel['modelName'],$el,$relFields);
			else $el[]=new PhpWsdlElement($rel['dataName'],$rel['modelName'].(in_array($rel['reltype'],array('hasMany','hasManyThrough'))?'Array':''));
		}
		$this->types[]=new PhpWsdlComplex($dataName,$el);
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
	
	/**
	 * Return a new parameter
	 * 
	 * @param string
	 * @param string
	 * @return PhpWsdlParam
	 */
	public static function param($name,$type,$settings=null){
		if(substr($type,0,7)==='array[]') return new PhpWsdlParam($name,substr($type,7).'Array',$settings);
		return new PhpWsdlParam($name,$type,$settings);
	}
	
	/**
	 * Return a way to add fields in a class
	 * 
	 * @param string
	 * @return SoapWsdlAddClass
	 */
	public function addClass($className){
		return new SoapWsdlAddClass($className,$this->types);
	}
}

class SoapWsdlAddClass{
	private $className,$types,$el=array();
	
	/**
	 * @param string
	 * @param array
	 * 
	 * @ignore
	 */
	public function __construct($className,&$types){
		$this->className=$className;
		$this->types=&$types;
	}
	
	/**
	 * Add a field in the class
	 * 
	 * @param string
	 * @param string
	 * @param bool
	 * @param string
	 * @return SoapWsdlAddClass
	 */
	public function addField($fieldName,$fieldType,$nillable=true,$comment=null){
		$settings=array('nillable'=>$nillable?'true':'false');
		if(!empty($comment)) $settings['docs']=$comment;
		$this->el[]=new PhpWsdlElement($fieldName,$fieldType,$settings);
		return $this;
	}
	/**
	 * Add the class in the list of types
	 * 
	 * @return void
	 */
	public function end(){
		$this->types[]=new PhpWsdlComplex($this->className,$this->el);
	}
}
