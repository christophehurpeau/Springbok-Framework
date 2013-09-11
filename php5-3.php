<?php
if(!interface_exists('JsonSerializable',false)){
	interface JsonSerializable{}
} 

if(!function_exists('class_uses')){
	function class_uses(){ return array(); }
}
if(!function_exists('trait_exists')){
	function trait_exists(){ return false; }
}
if(!function_exists('get_declared_traits')){
	function get_declared_traits(){ return array(); }
}

if (!defined('ENT_SUBSTITUTE')){
	define('ENT_SUBSTITUTE', 8);
}

if (!function_exists("hex2bin")){
	function hex2bin($hex){ return pack("H*", $hex); }
}

if (!defined('JSON_UNESCAPED_SLASHES')) {
	define('JSON_UNESCAPED_SLASHES', 64);
}

if (!defined('JSON_PRETTY_PRINT')) {
	define('JSON_PRETTY_PRINT', 128);
}

if (!defined('JSON_UNESCAPED_UNICODE')) {
	define('JSON_UNESCAPED_UNICODE', 256);
}