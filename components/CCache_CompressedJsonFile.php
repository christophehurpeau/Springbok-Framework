<?php
/** Compressed JSON file Cache */
class CCache_CompressedJsonFile extends CCache_JsonFile{
	/**
	 * Compress data
	 * 
	 * @param string
	 * @return string
	 */
	public static function data_read($data){
		$data=CCache_JsonFile::data_read($data);
		return $data?gzinflate($data):false;
	}
	/**
	 * Decompress data
	 * 
	 * @param string
	 * @return string
	 */
	public static function data_write($data){
		return gzdeflate(CCache_JsonFile::data_write($data));
	}
}
