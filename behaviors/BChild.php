<?php
/**
 * Behavior Child
 * 
 * Represents a child of a Parent Model
 * 
 * Sample of a child model
 * <code>
 * /** @TableAlias('p') @Child('Searchable','name,slug,created,updated') {@*}
 * class Post extends Searchable{
 * 	use BChild;
 * 
 * 	...
 * }
 * </code>
 * 
 * 
 * @see BChild_build
 * @see BParent
 * 
 * @property int $id add an id field if NOT present in the child model, foreignKey to the parent's id
 * @property int $p_id add an p_id field if IS present in the child model, foreignKey to the parent's id
 * 
 * @method bool|int insertParent() 
 * @method bool|int insertIgnoreParent() 
 * @method bool|int updateParent() 
 * @method bool|int static getParentId() getParentId(int $id) 
 */
trait BChild{
	public static function QListName(){ return $query=parent::QList()->setFields(array("id"))->withParent("name"); }
}
