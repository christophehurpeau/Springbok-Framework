<?php
/**
 * Log changes in the Model "ModelLogChanges", use with core plugin "logModelChanges"
 */
trait BLogChanges{
	public function logUpdate($data,$primaryKeys){
		ModelLogChanges::logUpdate($primaryKeys,$data);
		return true;
	}
	
	public function logInsert($data){
		ModelLogChanges::logInsert($data);
		return true;
	}
}