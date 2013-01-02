<?php
class DBSchemaProcessing{
	public static $isProcessing=false;
	private $force,$generate,$shouldApply/* PROD */ =true/* /PROD */,/* DEV */$writeDbEvolution=false,/* /DEV */
			$dbs=array(),$columns=array(),$schemas=array(),$logs,$logger,$time;
	
	public function __construct(Folder $modelDir,Folder $triggersDir,$force=false,$generate=true){
		if(!$modelDir->exists()) return false;
		$this->time=date('Y-m-d H:i:s');
		$issetCurrentFileEnhanced=(class_exists('App',false) && isset(App::$currentFileEnhanced));
		self::$isProcessing=true;
		
		$this->force=$force; $schemas=array();
		/* DEV */
			$apply=CHttpRequest::_GETor('apply');
			$this->shouldApply=$force?true:$apply==='springbokProcessSchema'||$apply==='springbok_Evolu_Schema';
			$this->writeDbEvolution=$force?false:$apply==='springbok_Evolu_Schema';
		/* /DEV */
		$this->generate=$generate||$this->shouldApply;
		
		$baseDir=/* DEV */dirname(APP).'/'/* /DEV *//* HIDE */./* /HIDE *//* PROD */APP/* /PROD */;
		/* DEV */
			if(!is_writable(APP.'dbEvolutions')) throw new Exception('dbEvolutions is not writable !');
			if(file_exists(APP.'dbEvolutions/Versions.php') && !is_writable(APP.'dbEvolutions/Versions.php'))
					throw new Exception('dbEvolutions/Versions.php is not writable !');
		/* /DEV */
		
		if(Config::$generate['default']){
			$currentDbVersion=trim(UFile::getContents($currentDbVersionFilename=($baseDir.'currentDbVersion')));
			$currentDbVersion=strpos($currentDbVersion,'-')!==false ? strtotime($currentDbVersion) : (int)$currentDbVersion;
		
			$dbVersions=explode("\n",trim(UFile::getContents(APP.'dbEvolutions/Versions.php')));
			if(!empty($dbVersions)){
				$dbVersionToFilename=$dbVersions;
				foreach($dbVersions as &$version) $version=strpos($version,'-')!==false ? strtotime($version) : (int)$version;
				$dbVersionToFilename=array_combine($dbVersions,$dbVersionToFilename);
				
				$lastVersion=(int)array_pop($dbVersions);
				
				if($currentDbVersion !== $lastVersion && $currentDbVersion < $lastVersion){
					$this->displayAndLog('currentDbVersion ('.$currentDbVersion.') != lastVersion ('.$lastVersion.')');
					
					$versionsToUpdate=array($lastVersion);
					while(($version=array_pop($dbVersions)) && $version > $currentDbVersion)
						array_unshift($versionsToUpdate,(int)$version);
					
					if($generate){
						$error=false;
						$vars=array('versions'=>$versionsToUpdate);
						if(!$this->shouldApply()){
							render(CORE.'db/evolutions-view.php',$vars);
						}else{
							foreach($versionsToUpdate as $version){
								$sql=file_get_contents(APP.'dbEvolutions/'.$dbVersionToFilename[$version].'.sql');
								
								foreach(explode("\n",$sql) as $line){
									if(empty($line)) continue;
									list($dbName,$query) = explode('=>',$line,2);
									
									$db=DB::init($dbName);
									try{
										$db->doUpdate($query);
									}catch(Exception $ex){
										$error=true;
										$this->displayAndLog('ERROR: '.$ex->getMessage());
									}
								}
								
								file_put_contents($currentDbVersionFilename,$version);
								$this->displayAndLog('Applied : '.$version.($error?' WITH ERROR':''));
								if($error) break;
							}
							
							
							if(isset($_SERVER['REQUEST_URI'])) render(CORE.'db/applied-evolutions-view.php',$vars);
						}
						if($error || isset($_SERVER['REQUEST_URI'])) exit;
					}
				}
			}
		}
		
		foreach($modelDir->listFiles() as $file){
			$modelName=substr($file->getName(),0,-4);
			if($issetCurrentFileEnhanced) App::$currentFileEnhanced=$modelName;
			$schemas[$modelName]=DBSchema::create($this,$modelName,0,-4);
		}
		
		foreach($modelDir->listDirs() as $dir){
			$dirname=$dir->getName();
			if($dirname == 'infos') continue;
			foreach($dir->listFiles() as $file){
				$modelName='E'.$dirname.substr($file->getName(),0,-4);
				if($issetCurrentFileEnhanced) App::$currentFileEnhanced=$modelName;
				$schemas[$modelName]=DBSchema::create($this,$modelName,true);
			}
		}
		
		$this->schemas=&$schemas;
		
		foreach($schemas as $schema) if(!isset($this->dbs[$schema->getDb()->_getName()])) $this->dbs[$schema->getDb()->_getName()]=$schema->getDb();
		//foreach($dbs as $db) $db->disableForeignKeyChecks();
		
		
		
		foreach($schemas as $schema){
			if($issetCurrentFileEnhanced) App::$currentFileEnhanced=$schema->getModelName();
			$schema->process();
		}
		if($issetCurrentFileEnhanced) App::$currentFileEnhanced='';
		
		//$this->compareIndexes();
		//$this->compareForeignKeys();
		if($generate && ($this->logs===null || $this->shouldApply())){
			foreach($schemas as $schema) $schema->compareIndexes();
			foreach($schemas as $schema) $schema->compareForeignKeys();
		}
		
		/* DEV */
		//regenerate after modifs
		/*if($this->generate){
			foreach($schemas as $schema){
				if($issetCurrentFileEnhanced) App::$currentFileEnhanced=$schema->getModelName();
				$schema->generatePropDefs();
			}
			if($issetCurrentFileEnhanced) App::$currentFileEnhanced='';
		}*/
		
		if($this->logs !==null && $generate && isset($_SERVER['REQUEST_URI'])){
			$vars=array('dbs'=>&$this->logs);
			if(!$this->shouldApply()) render(CORE.'db/confirm-view.php',$vars);
			else{
				if($this->writeDbEvolution){
					file_put_contents($baseDir.'src/dbEvolutions/Versions.php',$this->time."\n",FILE_APPEND);
					file_put_contents($baseDir.'currentDbVersion',$this->time);
				}
				render(CORE.'db/applied-view.php',$vars);
			}
			exit;
		}
		
		if(isset($_SERVER['REQUEST_URI']) && substr(APP,-16)!=='/webmanager/dev/' && ($apply==='springbokProcessSchema'||$apply==='springbok_Evolu_Schema')){
			header('Location: '.substr($_SERVER['REQUEST_URI'],0,-strlen('?apply=springbokProcessSchema')));
			exit;
		}
		/* /DEV */
		
		//if(self::$hasModifs)
		//	foreach($schemas as $schema) $schema->createForeignRelationships();
		
		//foreach($dbs as $db) $db->activeForeignKeyChecks();
		
		foreach($schemas as $schema) $schema->addForeignKeysRelations();
		foreach($schemas as $schema) $schema->addForeignKeysHasManyThroughRelations();
		foreach($schemas as $schema) $schema->createRelations();
		
		if($this->generate && $triggersDir->exists()){
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
	public function displayAndLog($log){
		if($this->logger===null) $this->logger=CLogger::get(time().'-schemaprocessing');
		$this->logger->log($log);
		if(function_exists('display')) display($log);
	}
	
	public function query($dbName,$sql){
		/* DEV */
			if($this->writeDbEvolution)
				file_put_contents(dirname(APP).'/src/dbEvolutions/'.$this->time.'.sql',"\n".$dbName."=>".str_replace("\n",' ',$sql),FILE_APPEND);
		/* /DEV */
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
