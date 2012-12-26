<?php
class BTree_build{
	public static function onBuild($modelFile,&$contentInfos,$annotations,$enhanceConfig,&$classBeforeContent){
		foreach(array('left','right','parent_id') as $fieldName)
			if(isset($modelFile->_fields[$fieldName])) throw new Exception($modelFile->_className.' already contains a field "'.$fieldName.'"');
		if(!isset($modelFile->_fields['id']))
			throw new Exception($modelFile->_className.' must have an unique Pk "id"');
		
		$modelFile->_fields['left']=array('SqlType'=>array($modelFile->_fields['id']['SqlType']),'NotNull'=>false, 'NotBindable'=>false, 'Index'=>false);
		$modelFile->_fields['right']=array('SqlType'=>array($modelFile->_fields['id']['SqlType']),'NotNull'=>false, 'NotBindable'=>false, 'Index'=>false);
		$modelFile->_fields['parent_id']=array('SqlType'=>array($modelFile->_fields['id']['SqlType']),'NotNull'=>false, 'NotBindable'=>false,
				'ForeignKey'=>array($modelFile->_className,'id','onDelete'=>'RESTRICT'));
	}
}