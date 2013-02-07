<?php
trait TreeController{
	/** */
	function index(){
		$modelName=self::MODEL;
		set('tree',$modelName::TreeView());
		render();
	}
	/** */
	function edit(int $id,$text){
		$modelName=self::MODEL;
		$modelName::updateOneFieldByPk($id,'name',$text);
		renderText('1');
	}
}