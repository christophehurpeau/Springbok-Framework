<?php
/**
 * Behavior child (build)
 * 
 * Add p_id or id field and methods like insertParent(), insertIgnoreParent(), updateParent()
 * 
 * @see BChild
 */
class BChild_build{
	public static function onBuild($modelFile,&$contentInfos,$annotations,$enhanceConfig,&$classBeforeContent){
		$idField=isset($modelFile->_fields['id']) ? 'p_id' : 'id';
		$fieldToInsert=array( 'SqlType'=>array(isset($annotations['ParentBigintId'])?'bigint(20) unsigned':'int(10) unsigned'), 'NotNull'=>false, 'NotBindable'=>false,
							'ForeignKey'=>array($annotations['Child'][0][0],'id','onDelete'=>'CASCADE'));
		$modelFile->_fields=array($idField=>$fieldToInsert)+$modelFile->_fields;
		$idField==='id' ? $modelFile->_fields[$idField]['Pk']=false : $modelFile->_fields[$idField]['Unique']=false;
		$contentInfos['relations']['Parent']=array('reltype'=>'belongsTo','modelName'=>$annotations['Child'][0][0],'foreignKey'=>$idField,
						'fieldsInModel'=>$annotations['TableAlias'][0][0],'fields'=>isset($annotations['Child'][0][1]) ? $annotations['Child'][0][1] : null);
		$classBeforeContent.="\n".'public function insert(){ $this->data["'.$idField.'"]=$this->insertParent(); $res=parent::insert(); return $res ? $this->data["'.$idField.'"] : $res; }';
		$classBeforeContent.="\n".'public function insertIgnore(){ $idParent=$this->insertIgnoreParent(); if($idParent){ $this->data["'.$idField.'"]=$idParent; return parent::insertIgnore();} }';
		
		$parentModel=$annotations['Child'][0][0];
		if($parentModel!==UString::camelize($parentModel,false))
			throw new Exception($parentModel.' does not look like a model !');
		if(!isset($enhanceConfig['modelParents'][$parentModel]))
			throw new Exception('You need to add childs of '.$parentModel.' in enhance.php');
		$typesParent=$enhanceConfig['modelParents'][$annotations['Child'][0][0]];
		$typeForParent=array_search($modelFile->_className,$typesParent);
		if($typeForParent===false) throw new Exception("Type parent not found for ".$modelFile->_className.": ".print_r($typesParent,true));
		
		
		$updateThisData=' if($res){ $parentData=$parent->_getData();'
										.($idField==='id' ? 'unset($parentData[\'_type\']);' : 'unset($parentData["id"],$parentData[\'_type\']);')
										.' $this->mset($parentData); }';
		
		$classBeforeContent.="\n".'public function insertParent(){ $parent=new '.$annotations['Child'][0][0].';'
									.'$data=$this->data;'.($idField==='id' ? '' : 'unset($data["id"]);').' $data[\'_type\']='.$typeForParent.'; $parent->_copyData($data);'
									.' $res=$parent->'.($annotations['Child'][0][0]==='SearchablesKeyword'?'findIdOrInsert(\'id\')':'insert()').';'
									.$updateThisData
									.'return $res; }';
		$classBeforeContent.="\n".'public function insertIgnoreParent(){ $parent=new '.$annotations['Child'][0][0].';'
									.'$data=$this->data;'.($idField==='id' ? '' : 'unset($data["id"]);').' $data[\'_type\']='.$typeForParent.'; $parent->_copyData($data);'
									.' $res=$parent->insertIgnore();'
									.$updateThisData
									.' return $res; }';
		$classBeforeContent.="\n".'public function updateParent(){ $parent=new '.$annotations['Child'][0][0].';'
									.'$data=$this->data;'.($idField==='id' ? '' : '$data["id"]=$data["p_id"]; unset($data["p_id"]);').' $data[\'_type\']='.$typeForParent.'; $parent->_copyData($data);'
									.'$res=call_user_func_array(array($parent,"update"),func_get_args());'
									.$updateThisData
									.'return $res; }';
		if($idField==='p_id') $classBeforeContent.="\n".'public static function getParentId($childId){ return self::QValue()->field("p_id")->byId($childId)->fetch(); }';
	}
}