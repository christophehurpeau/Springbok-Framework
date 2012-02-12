<?php
class_exists('UFile');

$f=new Folder(dirname(APP).'/tmp_dev'); if($f->exists()) $f->delete();

if(!class_exists('EnhanceApp',false)) include CORE.'enhancers/EnhanceApp.php';
App::$enhancing=$enhanceApp=new EnhanceApp(dirname(APP));
$changes=$enhanceApp->process(true);
App::$enhancing=false;
//debugVar('enahncing app took : '.(microtime(true) - $t).' s');

//$logDir=new Folder(APP.'logs'); $logDir->mkdirs();
//$tmpDir=new Folder(APP.'tmp'); $tmpDir->mkdirs();
//$langDir=new Folder(APP.'models/infos'); $langDir->mkdirs();


$modelFolder=new Folder(APP.'models');
$schemaProcessing=new DBSchemaProcessing($modelFolder,new Folder(APP.'triggers'),true,true);
/*
if(isset(Config::$plugins)){
	include CORE.'enhancers/EnhancePlugin.php';
	foreach(Config::$plugins as $key=>&$plugin){
		App::$enhancing=$enhancePlugin=new EnhancePlugin($pluginFolder=(Config::$pluginsPaths[$plugin[0]].$plugin[1]));
		$changes=$enhancePlugin->process();
		App::$enhancing=false;
		
		$plugin[1].='/dev';
		
		$modelFolder=new Folder($pluginFolder.'/dev/models/');
		$schemaProcessing=new DBSchemaProcessing($modelFolder,new Folder($pluginFolder.'/dev/triggers/'),true,true);
	}
}*/