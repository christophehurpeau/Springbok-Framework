<?php
class DBSchemaProcessing{
	public static $isProcessing=false;
	private $force,$generate,$shouldApply/* PROD */ =true/* /PROD */,$dbs=array(),$columns=array(),$schemas=array(),$logs,$logger;
	
	public function __construct(Folder $modelDir,Folder $triggersDir,$force=false,$generate=true){
		if(!$modelDir->exists()) return false;
		
		self::$isProcessing=true;
		
		$this->force=&$force; $schemas=array();
		/* DEV */ $this->shouldApply=$force?true:CHttpRequest::_GETor('apply')==='springbokProcessSchema'; /* /DEV */
		$this->generate=$generate||$this->shouldApply;
		
		foreach($modelDir->listFiles() as $file){
			$modelName=substr($file->getName(),0,-4);
			$schemas[$modelName]=DBSchema::create($this,$modelName,0,-4);
		}
		
		foreach($modelDir->listDirs() as $dir){
			$dirname=$dir->getName();
			if($dirname == 'infos') continue;
			foreach($dir->listFiles() as $file){
				$modelName='E'.$dirname.substr($file->getName(),0,-4);
				$schemas[$modelName]=DBSchema::create($this,$modelName,true);
			}
		}
		
		$this->schemas=&$schemas;
		
		foreach($schemas as $schema) if(!isset($this->dbs[$schema->getDb()->_getName()])) $this->dbs[$schema->getDb()->_getName()]=$schema->getDb();
		//foreach($dbs as $db) $db->disableForeignKeyChecks();
		
		foreach($schemas as $schema) $schema->process();
		
		
		//$this->compareIndexes();
		//$this->compareForeignKeys();
		if($generate && ($this->logs===NULL || $this->shouldApply())){
			foreach($schemas as $schema) $schema->compareIndexes();
			foreach($schemas as $schema) $schema->compareForeignKeys();
		}
		
		/* DEV */
		if($this->logs !==NULL && $generate){
			$vars=array('dbs'=>&$this->logs);
			if(!$this->shouldApply()) render(CORE.'db/confirm-view.php',$vars);
			else render(CORE.'db/applied-view.php',$vars);
			exit;
		}
		/* /DEV */
		
		//if(self::$hasModifs)
		//	foreach($schemas as $schema) $schema->createForeignRelationships();
		
		//foreach($dbs as $db) $db->activeForeignKeyChecks();
		
		foreach($schemas as $schema) $schema->addForeignKeysRelations();
		foreach($schemas as $schema) $schema->addForeignKeysHasManyThroughRelations();
		foreach($schemas as $schema) $schema->createRelations();
		
		if($generate && $triggersDir->exists()){
			foreach($triggersDir->listFiles() as $file){
				$dbTrigger=new DBTrigger($schemas,include $file->getPath());
			}
		}
		
		
		/* DEV */
		if(!$generate && !$force) foreach($this->dbs as &$db) $db->resetQueries();
		/* /DEV */
		
		self::$isProcessing=false;
	}

	public static function isProcessing(){
		return self::$isProcessing;
	}
	
	public function log($dbName,$tableName,$log){
		if($this->logger===null) $this->logger=CLogger::get(time().'-schemaprocessing');
		$this->logs[$dbName][$tableName][]=$log;
		$this->logger->log($dbName.' : '.$tableName.' : '.$log);
	}

	public function isGenerate(){
		return $this->generate;
	}
	
	public function shouldApply(){
		return $this->shouldApply;
	}
	
	/*
	public static function hasModifs(){
		self::$hasModifs=true;
		//foreach(self::$schemas as $schema) $schema->removeForeignKeys();
	}
	 * 
	 * 
	
	public static function checkPropDef(Folder $modelDir){
		foreach($modelDir->listFiles() as $file){
			if(!file_exists($filename=$modelDir->getPath().'infos'.DS.substr($file->getName(),0,-4)) || !file_exists($filename.'_') || (include $filename.'_')==NULL){
				$schema=new DBSchema(substr($file->getName(),0,-4));
				$schema->process();
				$schema->createRelations();
			}
		}
	}
	 */
}
