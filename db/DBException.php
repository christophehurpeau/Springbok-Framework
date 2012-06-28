<?php
class DBException extends Exception{
	private $query,$error;
	public function __construct($error,$query=null){
		parent::__construct($error.($query===null?'':PHP_EOL.'Query : '.$query));
		$this->error=$error;$this->query=$query;
	}
	
	public function hasQuery(){return $this->query!==null;}
	public function getQuery(){return $this->query;}
	public function getError(){return $this->error;}
}
