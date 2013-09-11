<?php
if(!interface_exists('JsonSerializable',false)){
	interface JsonSerializable{}
} 

if(!function_exists('class_uses')){
	function class_uses(){ return array(); }
}


if (!defined('ENT_SUBSTITUTE')) {
	define('ENT_SUBSTITUTE', 8);
}