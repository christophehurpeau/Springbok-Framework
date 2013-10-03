<?php
/**
 * Behavior History (build)
 * 
 * Add suffixed History model and static addHistory method
 * 
 * @see BHistory
 */
class BHistory_build{
	public static $afterInsert=array('_history_created');
	public static $afterUpdate=array('_history_updated');
	
	public static function onBuild($modelFile,&$contentInfos,$annotations,$enhanceConfig,&$classBeforeContent){
		$historyClass=isset($annotations['History']) ? $annotations['History'][0][0] : $modelFile->_className.'History';
		$source=$enhanceConfig['config']['searchableHistory.source'];
		
		foreach(array('owner','created_by') as $fieldName)
			if(isset($modelFile->_fields[$fieldName]) || $fieldName==='created_by'){
				$createdBy='isset($this->'.$fieldName.')?$this->'.$fieldName.':true'; break; }
		
		foreach(array('created_source','created_from') as $fieldName)
			if(isset($modelFile->_fields[$fieldName]) || $fieldName==='created_from'){
				$createdFrom='isset($this->'.$fieldName.')?$this->'.$fieldName.':true'; break; }
		
		foreach(array('updated_by') as $fieldName)
			if(isset($modelFile->_fields[$fieldName]) || $fieldName==='updated_by'){
				$updatedBy='isset($this->'.$fieldName.')?$this->'.$fieldName.':true'; break; }
		
		if($source){
			foreach(array('updated_source','updated_from') as $fieldName)
				if(isset($modelFile->_fields[$fieldName]) || $fieldName==='updated_from'){
					$updatedFrom='isset($this->'.$fieldName.')?$this->'.$fieldName.':null'; break; }
		}
		
		$contentInfos['relations']['History']=array('reltype'=>'hasMany','modelName'=>$historyClass,'dataName'=>'history');
		
		$classBeforeContent.=
			"\n".'public static function addHistory($objId,$type,$relId=null,$userId=true,$source=null){ '
						.$historyClass.'::add($objId,$type,$relId,$userId,$source); }'
			."\n".'private function _history_created(){ self::addHistory($this->id,'.$historyClass.'::CREATED,null,'.$createdBy.($source?','.$createdFrom:'').'); return true; }'
			."\n".'private function _history_updated(){ self::addHistory($this->id,'.$historyClass.'::UPDATED,null,'.$updatedBy.($source?','.$updatedFrom:'').'); return true; }';
		
	}


	/* for the history model */
	public static function _history_beforeEnd($modelFile,&$contentInfos,&$content){
		$types=$typesRelations=$relations=array();
		$content=preg_replace_callback(PhpFile::regexpArrayField('history'),function($matches2) use(&$types,&$typesRelations){
			$firstReplace=empty($types);
			$matches2[1]=preg_replace('/\s*([A-Z][A-Za-z\_]+)\=\>\s*/','"$1"=>',$matches2[1]);
			$matches2[1]=preg_replace('/\s*\=\>(array\()?([A-Z][A-Z\_]+)(\s*[,;\)])\s*/','=>$1"$2"$3',$matches2[1]);
			$eval=dev_eval('return '.$matches2[1]);
			if(empty($eval) && !empty($matches2[1]) && !is_array($eval))
				throw new Exception('Failed to eval :'."\n".$matches2[1]);
			foreach($eval as $relationName=>$typesArray){
				if(!is_string($relationName)){ $typesArray=array($relationName=>$typesArray); $relationName=false; }
				foreach($typesArray as $type=>$typeName){
					$types[$type]=$typeName;
					if($relationName!==false) $typesRelations[$type]=$relationName;
				}
				
			}
			return $firstReplace?'___REPLACE_HISTORY___':'';
		},$content);
		
		
		$content=preg_replace_callback(PhpFile::regexpArrayField('historyRelations'),function($matches2) use(&$relations){
			$matches2[1]=preg_replace('/\s*([A-Z][A-Za-z\_]+)\=\>\s*/','"$1"=>',$matches2[1]);
			$eval=dev_eval('return '.$matches2[1]);
			if(empty($eval) && !empty($matches2[1]) && !is_array($eval))
				throw new Exception('Failed to eval :'."\n".$matches2[1]);
			$relations=$eval;
			return '';
		},$content);
		
		$contentInfos['relations']['details']=array('reltype'=>'belongsToType','fieldType'=>'type','dataName'=>'details','types'=>$typesRelations,'relations'=>$relations);
		
		$historyContent="\n".'const ';
		foreach($types as $type=>$const)
			$historyContent.=$const.'='.$type.',';
		$historyContent=substr($historyContent,0,-1).';';
		
		foreach($types as $type=>$const)
			$historyContent.="\n".'public static function '.UString::camelize(strtolower($const)).'(){'
				.'$args=func_get_args();array_shift($args,$args[0]);$args[1]=self::'.$const.';return call_user_func_array(array("self","add"),$args);}';
		
		$historyContent.="\n".'public function detailOperation(){'
				.'return _tF('.UPhp::exportCode($modelFile->_className).',\'History.\'.$this->type);'
			.'}';
		
		
		$content=str_replace('___REPLACE_HISTORY___',$historyContent,$content);
	}
}