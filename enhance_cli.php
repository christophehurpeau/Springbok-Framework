<?php
ini_set('display_errors',1);
error_reporting(E_ALL | E_STRICT);
define('DS',DIRECTORY_SEPARATOR);
set_time_limit(0);


define('CORE_SRC',__DIR__.'/');
define('CLIBS',dirname(__DIR__).'/libs/dev/');
include CORE_SRC.'enhancers/EnhanceSpringbok.php';



echo '== Core =='."\n";
$instance=new EnhanceSpringbok();
$changes=$instance->process(dirname(__DIR__).DS);
if(empty($changes)) echo "No changes.";
else echo implode("\n",$changes);

echo "\n\n".'== Libs =='."\n";
$instance=new EnhanceSpringbok();
$changes=$instance->process(dirname(__DIR__).'/libs/',true);
if(empty($changes)) echo "No changes.";
else echo implode("\n",$changes);

echo "\n";
