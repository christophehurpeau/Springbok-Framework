<?php
trait TreeController{
	/** */
	function index(){
		$modelName=self::MODEL;
		set('tree',$modelName::TreeView());
		render();
	}
}