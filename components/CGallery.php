<?php
class CGallery{
	public static function index(&$id,$albumModelName,$imageModelName){
		Controller::renderJSON('{'
			.'"children":'.SModel::json_encode($albumModelName::QAll()->byParent_id($id)->with($imageModelName,array('isCount'=>true,'dataName'=>'images'))->execute())
			.',"images":'.SModel::json_encode($imageModelName::QAll()->byAlbum_id($id)->execute())
			.'}'
		);
	}
}
