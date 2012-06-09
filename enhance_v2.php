<?php
ini_set('display_errors',1);
error_reporting(E_ALL | E_STRICT);
define('DS',DIRECTORY_SEPARATOR);
set_time_limit(0);
date_default_timezone_set('Europe/Paris');

header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Pragma: no-cache");

define('CORE_SRC',__DIR__.'/');
define('CLIBS',dirname(__DIR__).'/libs/dev/');
include CORE_SRC.'enhancers/EnhanceSpringbok.php';



echo '<h1>Core</h1>';
$instance=new EnhanceSpringbok();
$changes=$instance->process(dirname(__DIR__).DS);
if(empty($changes)) echo "No changes.";
else echo "<pre>".implode("\n",$changes)."</pre>";

echo '<h1>Libs</h1>';
$instance=new EnhanceSpringbok();
$changes=$instance->process(dirname(__DIR__).'/libs/',true);
if(empty($changes)) echo "No changes.";
else echo "<pre>".implode("\n",$changes)."</pre>";

