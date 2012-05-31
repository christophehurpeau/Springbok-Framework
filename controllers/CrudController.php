<?php
class CrudController extends Controller{
	/** @ValidParams @Required('id') */
	function edit(int $id){
		CRUD::edit(self::CRUD_MODEL,$id);
	}
	
	/** @ValidParams @Required('id') */
	function delete(int $id){
		CRUD::delete(self::CRUD_MODEL,$id);
		self::redirect('/'.CRoute::getController());
	}
}