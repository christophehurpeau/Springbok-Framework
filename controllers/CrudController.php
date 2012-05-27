<?php
class CrudController extends Controller{
	/** @ValidParams @Required('id') */
	function edit(int $id){
		CRUD::edit(self::CRUD_MODEL,$id);
	}
}