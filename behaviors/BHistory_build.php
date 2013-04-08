<?php
class BHistory_build{
	public static $afterInsert=array('_history_created');
	public static $afterUpdate=array('_history_updated');
	
	public static function onBuild($modelFile,&$contentInfos,$annotations,$enhanceConfig,&$classBeforeContent){
		$historyClass=isset($annotations['History']) ? $annotations['History'][0][0] : $modelFile->_className.'History';
		
		foreach(array('owner','created_by') as $fieldName)
			if(isset($modelFile->_fields[$fieldName]) || $fieldName==='created_by'){
				$createdBy='isset($this->'.$fieldName.')?$this->'.$fieldName.':true'; break; }
		
		foreach(array('created_source','created_from') as $fieldName)
			if(isset($modelFile->_fields[$fieldName]) || $fieldName==='created_from'){
				$createdFrom='isset($this->'.$fieldName.')?$this->'.$fieldName.':true'; break; }
		
		foreach(array('updated_by') as $fieldName)
			if(isset($modelFile->_fields[$fieldName]) || $fieldName==='updated_by'){
				$updatedBy='isset($this->'.$fieldName.')?$this->'.$fieldName.':true'; break; }
		
		foreach(array('updated_source','updated_from') as $fieldName)
			if(isset($modelFile->_fields[$fieldName]) || $fieldName==='updated_from'){
				$updatedFrom='isset($this->'.$fieldName.')?$this->'.$fieldName.':true'; break; }
		
		$contentInfos['relations']['History']=array('reltype'=>'hasMany','modelName'=>$historyClass,'dataName'=>'history');
		
		
		$classBeforeContent.=
			"\n".'public static function addHistory($objId,$type,$relId=null,$userId=true,$source=null){ '
						.$historyClass.'::add($objId,$type,$relId,$userId,$source); }'
			."\n".'private function _history_created(){ self::addHistory($this->id,'.$historyClass.'::CREATED,'.$createdBy.','.$createdFrom.'); }'
			."\n".'private function _history_updated(){ self::addHistory($this->id,'.$historyClass.'::UPDATED,'.$updatedBy.','.$updatedFrom.'); }';
		
	}
}