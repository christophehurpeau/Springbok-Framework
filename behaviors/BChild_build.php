<?php
class BChild_build{
	public static function onBuild($modelFile,&$contentInfos,$annotations,$enhanceConfig,&$classBeforeContent){
		$idField=isset($modelFile->_fields['id']) ? 'p_id' : 'id';
		$fieldToInsert=array( 'SqlType'=>array(isset($annotations['ParentBigintId'])?'bigint(20) unsigned':'int(10) unsigned'), 'NotNull'=>false, 'NotBindable'=>false,
							'ForeignKey'=>array($annotations['Child'][0][0],'id','onDelete'=>'CASCADE'));
		$modelFile->_fields=array($idField=>$fieldToInsert)+$modelFile->_fields;
		$idField==='id' ? $modelFile->_fields[$idField]['Pk']=false : $modelFile->_fields[$idField]['Unique']=false;
		$contentInfos['relations']['Parent']=array('reltype'=>'belongsTo','modelName'=>$annotations['Child'][0][0],'foreignKey'=>$idField,
						'fieldsInModel'=>$annotations['TableAlias'][0][0],'fields'=>isset($annotations['Child'][0][1]) ? $annotations['Child'][0][1] : null);
		$classBeforeContent.='public function insert(){ $this->data["'.$idField.'"]=$this->insertParent(); $res=parent::insert(); return $res ? $this->data["'.$idField.'"] : $res; }';
		$classBeforeContent.='public function insertIgnore(){ $idParent=$this->insertIgnoreParent(); if($idParent){ $this->data["'.$idField.'"]=$idParent; return parent::insertIgnore();} }';
		$typesParent=$enhanceConfig['modelParents'][$annotations['Child'][0][0]];
		$typeForParent=array_search($modelFile->_className,$typesParent);
		if($typeForParent===false) throw new Exception("Type parent not found for ".$modelFile->_className.": ".print_r($typesParent,true));
		
		$classBeforeContent.='public function insertParent(){ $parent=new '.$annotations['Child'][0][0].';'
									.'$data=$this->data;'.($idField==='id' ? '' : 'unset($data["id"]);').' $data[\'_type\']='.$typeForParent.'; $parent->_copyData($data);'
									.' return $parent->'.($annotations['Child'][0][0]==='SearchablesKeyword'?'findIdOrInsert(\'id\')':'insert()').'; }';
		$classBeforeContent.='public function insertIgnoreParent(){ $parent=new '.$annotations['Child'][0][0].';'
									.'$data=$this->data;'.($idField==='id' ? '' : 'unset($data["id"]);').' $data[\'_type\']='.$typeForParent.'; $parent->_copyData($data);'
									.' return $parent->insertIgnore(); }';
		$classBeforeContent.='public function updateParent(){ $parent=new '.$annotations['Child'][0][0].';'
									.'$data=$this->data;'.($idField==='id' ? '' : '$data["id"]=$data["p_id"]; unset($data["p_id"]);').' $data[\'_type\']='.$typeForParent.'; $parent->_copyData($data);'
									.' return call_user_func_array(array($parent,"update"),func_get_args()); }';
		if($idField==='p_id') $classBeforeContent.='public static function getParentId($childId){ return self::QValue()->field("p_id")->byId($childId); }';
	}
}