<?php
trait BLogChanges{
	public static $afterUpdateCompare=array('logUpdate');
	public static $afterInsert=array('logInsert');
	
	
	public function logUpdate($data,$primaryKeys){
		ModelLogChanges::logUpdate($primaryKeys,$data);
		return true;
	}
	
	public function logInsert($data){
		ModelLogChanges::logInsert($data);
		return true;
	}
}