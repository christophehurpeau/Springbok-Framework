<?php
class BTextContent_build{
	public static function onBuild($modelFile,&$contentInfos,$annotations,$enhancedConfig,&$classBeforeContent){
		$modelFile->_fields['text']=array( 'SqlType'=>array('text'), 'Null'=>false);
	}
}