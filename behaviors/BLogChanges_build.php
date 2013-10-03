<?php
/**
 * Behavior LogChanges (build)
 * 
 * @see BLogchanges
 */
class BLogChanges_build{
	public static $afterUpdateCompare=array('logUpdate');
	public static $afterInsert=array('logInsert');
}
