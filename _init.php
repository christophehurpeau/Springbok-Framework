<?php
Springbok::$scriptname=substr(basename($_SERVER['SCRIPT_NAME']),0,-4);
define('BASE_URL',substr($_SERVER['SCRIPT_NAME'], 0,-strlen(Springbok::$scriptname)-5));/* must NOT end by / */
define('IS_HTTPS',/*isset($_SERVER['HTTPS']) ? */!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'/* : false (substr($_SERVER['SCRIPT_NAME'],0,8)=='https://'))*/);
define('HTTP_OR_HTTPS',IS_HTTPS?'https://':'http://');
if(isset($_SERVER['HTTP_HOST'])) define('FULL_BASE_URL',HTTP_OR_HTTPS.$_SERVER['HTTP_HOST']);

/*#if DEV */
if (version_compare(PHP_VERSION, '5.4.0')===-1)
	die('PHP Version 5.4.0 is REQUIRED');
/*#/if */