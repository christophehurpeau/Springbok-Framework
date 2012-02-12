<?php
//args : APP daemon_name instance_name

define('DS', DIRECTORY_SEPARATOR);
define('CORE',__DIR__.DS);
define('APP', $argv[1]);

$action='daemon'; $argv=array(1=>$argv[2],2=>empty($argv[3])?'default':$argv[3]);
include CORE.'cli.php';
