<?php
/*
	public static function parseJobFiles(Folder $jobDir){
		$jobsDef=array();
		foreach($jobDir->listFiles() as $file){
			if($file->getName()=='_.php') continue;
			
			$phpContent=$file->read();
			preg_match('/(?:\/\*\*([^{]*)\*\/)\s+class ([A-Za-z_]+) extends Job{/',$phpContent,$matches);//debug($matches);
			if(empty($matches)) die('not a valid job : '.replaceAppAndCoreInFile($file->getPath()));
			
			include_once CORE.'enhancers'.DS.'PhpFile.php';
			$annotations=PhpFile::parseAnnotations($matches[0]);
			
			$found=false;
			if(isset($annotations['OnApplicationStart']))
				$found=$jobsDef['start'][]=$file->getName();
			if(isset($annotations['OnApplicationStop']))
				$found=$jobsDef['stop'][]=$file->getName();
			
			if(isset($annotations['On']))
				$found=$jobsDef['on'][]=$file->getName();
			if(isset($annotations['Every']))
				$found=$jobsDef['every'][]=array('job'=>$file->getName(),'every'=>UTime::parseDuration($annotations['Every'][0]),'lastExecuted'=>false);
			
			if($found===false) die('found no annotations for job :' .replaceAppAndCoreInFile($file->getPath()));
		}
		file_put_contents($jobDir->getPath().'_.php',UPhp::exportCode($jobsDef));
	}
}*/