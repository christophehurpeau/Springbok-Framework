<?php
class SpringbokTranslations{
	public static function findTranslations($srcDir){
		$arrayStrings=array('all'=>array());
		self::_recursiveFiles($srcDir,$arrayStrings);
		$arrayStrings['all']=array_unique($arrayStrings['all']);
		return $arrayStrings;
	}
	
	public static function listInfosModels($dirname){
		if($dir=opendir($dirname)){
			$files=array();
			while (false !== ($file = readdir($dir)))
				if($file != '.' && $file != '..' && substr($file,-1)!=='_' && !is_dir($filename=$dirname.$file)) $files[$file]=$filename;
			closedir($dir);
			ksort($files);
			return $files;
		}
		return array();
	}
	
	
	public static function loadDbLang($langDir,$lang){
		return new DBSQLite(false,array( 'file'=>$langDir.$lang.'.db','flags'=>SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE ));
	}
	
	public static function checkDb($db){
		//$db->doUpdate('CREATE TABLE IF NOT EXISTS t(s NOT NULL,c NOT NULL,t NOT NULL, PRIMARY KEY(s,c)');
		
		
		$dbSchema=new DBSchemaSQLite($db,'t');
		$dbSchema->setModelInfos(array(
			'primaryKeys'=>array('s','c'),
			'columns'=>array(
				's'=>array('type'=>'TEXT','notnull'=>true,'unique'=>false,'default'=>false),
				'c'=>array('type'=>'TEXT','notnull'=>true,'unique'=>false,'default'=>'"a"'),
				't'=>array('type'=>'TEXT','notnull'=>true,'unique'=>false,'default'=>false)
			)
		));
		if(!$dbSchema->tableExist()) $dbSchema->createTable();
		/*else $dbSchema->compareTableAndApply();*/
	}
	
	protected static function _recursiveFiles($path,&$arrayStrings,$functionName='_t',$deleteLastParam=false,$pattern=false){
		foreach(new RecursiveDirectoryIterator($path,FilesystemIterator::KEY_AS_PATHNAME | FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::SKIP_DOTS)
					as $pathname=>$fileInfo){
			if(substr($fileInfo->getFilename(),0,1) == '.') continue;
			if($fileInfo->isDir()) self::_recursiveFiles($pathname,$arrayStrings,$functionName,$deleteLastParam);
			if(!in_array(($ext=substr($fileInfo->getFilename(),-3)),array('.js','php')) || substr($fileInfo->getFilename(),0,4)=='i18n') continue;
			$matches=array(); preg_match_all($pattern?$pattern:'/(?:\b'.$functionName.'\((.+)\)'.($ext==='php'?'|\{'.substr($functionName,1).'\s+([^}]+)\s*\}':'').')/Um',file_get_contents($pathname),$matches);
			if(!empty($matches[1])){
				foreach($matches[1] as $key=>$value)
					if(empty($matches[1][$key])) $matches[1][$key]=$matches[2][$key];
				unset($matches[2]);
				
				$matches=array_map(function($v) use(&$deleteLastParam){
					$string=substr($v,1);
					if($deleteLastParam) $string=substr($string,0,strrpos($string,','));
					return stripslashes(substr($string,0,-1));
				},$matches[1]);
				foreach($matches as $keyM=>$match) if(substr($match,0,7)==='plugin.') unset($matches[$keyM]);
				$arrayStrings['all']=array_merge($arrayStrings['all'],$matches);
				$arrayStrings[$pathname]=$matches;
			}
		}
	}

	private static function _prepareStatement($db,$c='a'){
		return $db->getConnect()->prepare('REPLACE INTO t(s,c,t) VALUES (:s,\''.$c.'\',:t)');
	}

	public static function saveAll($db,$data){
		if(empty($data)) return false;
		if(is_string($db)) $db=self::_loadDbLang($db);
		$db->doUpdate('DELETE FROM t WHERE c=\'a\' AND s NOT LIKE "plugin%"');
		$statement=self::_prepareStatement($db,'a');
		foreach($data as $d){
			$statement->bindValue(':s',$d['s']);
			$statement->bindValue(':t',$d['t']);
			$statement->execute();
		}
		return true;
	}
	
	public static function saveAllSP($db,$data){
		if(empty($data)) return false;
		if(is_string($db)) $db=self::_loadDbLang($db);
		$db->doUpdate('DELETE FROM t WHERE c IN(\'s\',\'p\')');
		$statementSingular=self::_prepareStatement($db,'s');
		$statementPlural=self::_prepareStatement($db,'p');
		foreach($data as $d){
			$statementSingular->bindValue(':s',$d['s']);
			$statementSingular->bindValue(':t',$d['singular']);
			$statementSingular->execute();
			$statementPlural->bindValue(':s',$d['s']);
			$statementPlural->bindValue(':t',$d['plural']);
			$statementPlural->execute();
		}
		return true;
	}
	
	public static function saveOne($db,$string,$translation){
		if(is_string($db)) $db=self::_loadDbLang($db);
		$statement=self::_prepareStatement($db,'a');
		$statement->bindValue(':s',$string);
		$statement->bindValue(':t',$translation);
		$statement->execute();
	}
	
	public static function saveOneSP($db,$string,$singular,$plural){
		if(is_string($db)) $db=self::_loadDbLang($db);
		$statementSingular=self::_prepareStatement($db,'s');
		$statementSingular->bindValue(':s',$string);
		$statementSingular->bindValue(':t',$singular);
		$statementSingular->execute();
		$statementPlural=self::_prepareStatement($db,'p');
		$statementPlural->bindValue(':s',$string);
		$statementPlural->bindValue(':t',$plural);
		$statementPlural->execute();
	}
	
	public static function saveOneModelField($db,$model,$string,$translation){
		if(is_string($db)) $db=self::_loadDbLang($db);
		$statement=self::_prepareStatement($db,'f');
		$statement->bindValue(':s',$model.':'.$string);
		$statement->bindValue(':t',$translation);
		$statement->execute();
	}
	
}