<?php
trait BChild{
	public static function QListName(){ return $query=parent::QList()->setFields(array("id"))->withParent("name"); }
}