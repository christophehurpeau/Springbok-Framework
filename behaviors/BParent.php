<?php
/**
 * Behavior Parent
 * 
 * in the file config/enhance.php, you must define all the children of the model
 * 
 * <code>
 * <?php return array(
 * 	'modelParents'=>array(
 * 		'Searchable'=>array(5=>'Page',6=>'CmsHardCodedPage'),
 * 		'SearchablesKeyword'=>array(),
 * 	)
 * );
 * </code>
 * 
 * @property int $_type key of the child model from modelParents in config
 */
trait BParent{
}