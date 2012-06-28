<?php
class CCache_CompressedFile extends CCache_File{
	public static function data_read($data){
		$data=CCache_File::data_read($data);
		return $data?gzinflate($data):false;
	}
	public static function data_write($data){
		return gzdeflate(CCache_File::data_write($data));
	}
}
