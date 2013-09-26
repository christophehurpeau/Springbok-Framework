<?php
trait TreeController{
	/** */
	static function index(){
		$modelName=self::MODEL;
		set('tree',$modelName::TreeView());
		render();
	}
	/** */
	static function edit(int $id,$text){
		$modelName=self::MODEL;
		$modelName::updateOneFieldByPk($id,'name',$text);
		renderText('1');
	}
}