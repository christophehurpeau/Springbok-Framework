<?php
class CGallery{
	public static function index(&$id,$albumModelName,$imageModelName){
		Controller::renderJSON('{'
			.'"children":'.Model::json_encode($albumModelName::QAll()->byParent_id($id)->with($imageModelName,array('isCount'=>true,'dataName'=>'images'))->execute())
			.',"images":'.Model::json_encode($imageModelName::QAll()->byAlbum_id($id)->execute())
			.'}'
		);
	}
}
