<?php
class BTree_build{
	public static function onBuild($modelFile,&$contentInfos,$annotations,$enhanceConfig,&$classBeforeContent){
		foreach(array('left','right','parent_id','level_depth') as $fieldName)
			if(isset($modelFile->_fields[$fieldName])) throw new Exception($modelFile->_className.' already contains a field "'.$fieldName.'"');
		if(!isset($modelFile->_fields['id']))
			throw new Exception($modelFile->_className.' must have an unique Pk "id"');
		
		$modelFile->_fields['parent_id']=array('SqlType'=>$modelFile->_fields['id']['SqlType'],'Null'=>false, 'NotBindable'=>false,
				'ForeignKey'=>array($modelFile->_className,'id','onDelete'=>'RESTRICT'));
		$modelFile->_fields['left']=array('SqlType'=>$modelFile->_fields['id']['SqlType'],'NotNull'=>false, 'NotBindable'=>false, 'Index'=>false);
		$modelFile->_fields['right']=array('SqlType'=>$modelFile->_fields['id']['SqlType'],'NotNull'=>false, 'NotBindable'=>false, 'Index'=>false);
		$modelFile->_fields['level_depth']=array('SqlType'=>array('tinyint(3) unsigned'),'NotNull'=>false, 'NotBindable'=>false, 'Index'=>false);
		
		$contentInfos['relations']['Parent']=array('reltype'=>'belongsTo','modelName'=>$modelFile->_className,'foreignKey'=>'parent_id',
						'fieldsInModel'=>$annotations['TableAlias'][0][0],'fields'=>isset($annotations['Tree'][0][1]) ? $annotations['Tree'][0][1] : null);
	}
}