<?php
trait TreeController{
	/** @Acl('AclGroup') */
	function index(){
		$modelName=self::MODEL;
		set('tree',$modelName::TreeView()->actionView('/acl/permissions/'));
		render();
	}
}