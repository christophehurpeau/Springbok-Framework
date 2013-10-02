<?php
/**
 * Behavior Parent (build)
 */
class BParent_build{
	public static function onBuild($modelFile,&$contentInfos,$annotations,$enhanceConfig,&$classBeforeContent){
		if(isset($modelFile->_fields['_type'])) throw new Exception($modelFile->_className.' already contains a field "_type"');
		$children=$enhanceConfig['modelParents'][$modelFile->_className];
		$modelFile->_fields['_type']=array('SqlType'=>array('tinyint(1) unsigned'),'NotNull'=>false, 'NotBindable'=>false, 'Index'=>false, 'Enum'=>$children );
		$_typeRelations=array();
		foreach($children as $child) $_typeRelations[$child]=array('foreignKey'=>'p_id');
		$contentInfos['relations']['_type']=array('reltype'=>'belongsToType', 'dataName'=>'child','types'=>$children,'relations'=>$_typeRelations );
	}
}