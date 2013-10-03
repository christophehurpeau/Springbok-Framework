<?php
/**
 * Behavior Normalized (build)
 * 
 * Possible class annotation @UniqueNormalized : instead of creating a simple index on the field, create a unique index.
 * 
 * Add a "normalized" field
 * 
 * @see BNormalized
 */
class BNormalized_build{
	public static $beforeSave=array('_setNormalizedIfName');
	
	public static function onBuild($modelFile,&$contentInfos,$annotations,$enhanceConfig,&$classBeforeContent){
		foreach(array('normalized') as $fieldName)
			if(isset($modelFile->_fields[$fieldName])) throw new Exception($modelFile->_className.' already contains a field "'.$fieldName.'"');
		
		
		$displayField=isset($annotations['DisplayField'][0][0])?$annotations['DisplayField'][0][0]:'name';
		if(!isset($modelFile->_fields[$displayField]) && !isset($annotations['Normalized']))
			throw new Exception($modelFile->_className.' must have an field "'.$displayField.'"');
		
		if(isset($modelFile->_fields[$displayField]['Translatable'])) return;
		
		$modelFile->_fields['normalized']=array('SqlType'=>isset($annotations['Normalized']) ? array($annotations['Normalized'][0][0]) : $modelFile->_fields[$displayField]['SqlType'],
					'NotNull'=>false, 'NotBindable'=>false);
		if(isset($annotations['UniqueNormalized'])) $modelFile->_fields['normalized']['Unique']=false;
		else $modelFile->_fields['normalized']['Index']=false;
	}
}