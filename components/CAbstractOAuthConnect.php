<?php
class CAbstractOAuthConnect{
	protected $accessToken,$me=null;
	public function __construct($accessToken,$retrieveMe=false){
		$this->accessToken=&$accessToken;
		if($retrieveMe===true) $this->retrieveMe();
	}
	
	public function &me($name){
		return $this->me[$name];
	}
	
	public function sayHello(){
		if($this->me===null) $this->retrieveMe();
		return 'Hello '.$this->me['name'];
	}
	
	public function isValidMe(){
		return !empty($this->me) && !isset($this->me['error']) && !empty($this->me['id']);
	}
	
}