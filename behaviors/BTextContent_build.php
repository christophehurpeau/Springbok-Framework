<?php
class BTextContent_build{
	public static $beforeSave=['_setTextToNullIfEmtpy'];
	public static $afterUpdate=['regenerateText'];
	
	public static function onBuild($modelFile,&$contentInfos,$annotations,$enhancedConfig,&$classBeforeContent){
		$modelFile->_fields['text']=array( 'SqlType'=>array('text'), 'Null'=>false);
	}
}