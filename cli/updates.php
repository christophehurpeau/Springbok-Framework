<?php


$baseDir=/*#if DEV */dirname(APP).'/'/*#/if*//*#if false*/./*#/if*//*#if PROD*/APP/*#/if*/;
/*#if DEV */
if(file_exists(APP.'cli/updates')){
	if(!is_writable(APP.'cli/updates')) throw new Exception('dbEvolutions is not writable !');
	if(file_exists(APP.'cli/updates/Updates.php') && !is_writable(APP.'cli/updates/Updates.php'))
			throw new Exception('dbEvolutions/Versions.php is not writable !');
}
/*#/if*/

//TODO unifier ce fichier avec DBSchemaProcessing
$lastCliUpdate=trim(UFile::getContents($lastCliUpdateFilename=($baseDir.'lastCliUpdate')));
$lastCliUpdate=strpos($lastCliUpdate,'-')!==false ? strtotime($lastCliUpdate) : (int)$lastCliUpdate;

if($lastCliUpdate===0) file_put_contents($lastCliUpdateFilename,time());
else{
	$cliUpdates=explode("\n",trim(UFile::getContents(APP.'cli/updates/Updates.php')));
	if(!empty($cliUpdates)){
		$cliUpdatesToFilename=$cliUpdates;
		foreach($cliUpdates as &$version) $version=strpos($version,'-')!==false ? strtotime($version) : (int)$version;
		$cliUpdatesToFilename=array_combine($cliUpdates,$cliUpdatesToFilename);
		
		$lastUpdate=(int)array_pop($cliUpdates);
		
		if($lastCliUpdate !== $lastUpdate && $lastCliUpdate < $lastUpdate){
			display('lastCliUpdate ('.$lastCliUpdate.') != lastUpdate ('.$lastUpdate.')');
			
			$cliUpdatesToDo=array($lastUpdate);
			while(($cliUpdate=array_pop($cliUpdates)) && $cliUpdate > $lastCliUpdate)
				array_unshift($cliUpdatesToDo,(int)$version);
			
			foreach($cliUpdatesToDo as $cliUpdate){
				display('Update: '.$cliUpdatesToFilename[$cliUpdate]);
				passthru('cd '.escapeshellarg($baseDir).' && php cli.php '.escapeshellarg('updates/'.$cliUpdatesToFilename[$cliUpdate]));
			}
		}
	}
}
