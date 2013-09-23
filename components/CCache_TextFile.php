<?php
/** Text file Cache */
class CCache_TextFile extends CCache_File{
	public static function data_read($data){
		return $data;
	}
	public static function data_write($data){
		return $data;
	}
}