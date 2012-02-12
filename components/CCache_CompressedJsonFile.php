<?php
class CCache_CompressedJsonFile extends CCache_JsonFile{
	public static function data_read(&$data){
		$data=CCache_JsonFile::data_read($data);
		return $data?gzinflate($data):false;
	}
	public static function data_write(&$data){
		return gzdeflate(CCache_JsonFile::data_write($data));
	}
}
