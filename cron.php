<?php
//args : APP job_name

define('DS', DIRECTORY_SEPARATOR);
define('CORE',__DIR__.DS);
define('APP', $argv[1]);

$action='job'; $argv=array(1=>$argv[2]);
include CORE.'cli.php';
