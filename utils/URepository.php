<?php
abstract class URepository{
}

abstract class AbstractRepositoryBranch{
	public $id,$revision;
}
abstract class AbstractRepositoryRevision{
	public $id,$name,$author,$time,$comment,$files,$revision,$branch,$identifier,$parents;
	
	public function name(){
		return $this->name===null ? $this->identifier : $this->name;
	}
	
	public function identifier(){
		return $this->identifier;
	}
}
