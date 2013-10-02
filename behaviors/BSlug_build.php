<?php
/**
 * Behavior Slug (build)
 */
class BSlug_build{
	public static $beforeSave=array('_setSlugIfName');
	
	public static function onBuild($modelFile,&$contentInfos,$annotations,$enhanceConfig,&$classBeforeContent){
		foreach(array('slug') as $fieldName)
			if(isset($modelFile->_fields[$fieldName])) throw new Exception($modelFile->_className.' already contains a field "'.$fieldName.'"');
		
		$displayField=isset($annotations['DisplayField'][0][0])?$annotations['DisplayField'][0][0]:'name';
		if(!isset($modelFile->_fields[$displayField]) && !isset($annotations['Slug']))
			throw new Exception($modelFile->_className.' must have an field "'.$displayField.'"');
		
		if(isset($modelFile->_fields[$displayField]['Translatable'])) return;
		
		$modelFile->_fields['slug']=array('SqlType'=>isset($annotations['Slug']) ? array($annotations['Slug'][0][0]) : $modelFile->_fields[$displayField]['SqlType'],
				'MinLength'=>array(3));
		if(isset($annotations['UniqueSlug'])) $modelFile->_fields['slug']['Unique']=false;
		if(isset($annotations['IndexSlug'])) $modelFile->_fields['slug']['Index']=false;
		$modelFile->_fields['slug'][isset($annotations['NullableSlug']) ? 'Null' : 'NotNull']=false;
	}
}