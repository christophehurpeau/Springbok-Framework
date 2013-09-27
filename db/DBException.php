<?php
class DBException extends Exception{
	private $query,$error;
	public function __construct($error,$query=null){
		parent::__construct($error.($query===null?'':PHP_EOL.'Query : '.$query));
		$this->error=$error;$this->query=$query;
	}
	
	/** @return bool */
	public function hasQuery(){return $this->query!==null;}
	/** @return string */
	public function getQuery(){return $this->query;}
	/** @return string */
	public function getError(){return $this->error;}
}
