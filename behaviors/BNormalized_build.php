<?php
class BNormalized_build{
	public static $beforeSave=array('_setNormalizedIfName');
	
	public static function onBuild($modelFile,&$contentInfos,$annotations,$enhanceConfig,&$classBeforeContent){
		foreach(array('normalized') as $fieldName)
			if(isset($modelFile->_fields[$fieldName])) throw new Exception($modelFile->_className.' already contains a field "'.$fieldName.'"');
		if(!isset($modelFile->_fields['name']))
			throw new Exception($modelFile->_className.' must have an field "name"');
		
		$modelFile->_fields['normalized']=array('SqlType'=>$modelFile->_fields['name']['SqlType'], 'NotNull'=>false, 'Index'=>false, 'NotBindable'=>false);
		if(isset($annotations['UniqueNormalized'])) $modelFile->_fields['normalized']['Unique']=false;
	}
}