<?php
class CCache_JsonFile extends CCache_File{
	public static function data_read(&$data){
		return json_decode($data,true);
	}
	public static function data_write(&$data){
		return json_encode($data); // TODO PHP 5.4 : use JSON_UNESCAPED_UNICODE if better perfs
	}
}