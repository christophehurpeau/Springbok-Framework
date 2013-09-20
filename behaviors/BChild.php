<?php
/**
 * Behavior Child
 * 
 * Represents a child of a Parent Model
 * 
 * @see BChild_build
 * @see BParent
 */
trait BChild{
	public static function QListName(){ return $query=parent::QList()->setFields(array("id"))->withParent("name"); }
}