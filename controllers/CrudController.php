<?php
class CrudController extends Controller{
	/** @ValidParams @Required('id') */
	static function edit(int $id){
		CRUD::edit(self::CRUD_MODEL,$id);
	}
	
	/** @ValidParams @Required('id') */
	static function delete(int $id){
		CRUD::delete(self::CRUD_MODEL,$id);
		self::redirect('/'.CRoute::getController());
	}
}