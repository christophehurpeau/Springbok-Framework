<?php
class CGallery{
	public static function index($id,$type,$albumModelName,$imageModelName){
		Controller::renderJSON('{'
			.'"children":'.SModel::json_encode($albumModelName::QAll()->byParent_id($id)
								->with($imageModelName,array('isCount'=>true,'dataName'=>'images',
											'onConditions'=>$type===null?null:array('lf.type'=>$type)))
								->fetch())
			.',"images":'.SModel::json_encode($imageModelName::QAll()
						->where($type===null?array('folder_id'=>$id):array('folder_id'=>$id,'type'=>$type))
						->fetch())
			.'}'
		);
	}
}
