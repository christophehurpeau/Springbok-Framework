<?php
class CSearchResult{
	public $pagination,$totalResults,$query;
	
	public function __construct($search,$query){
		$this->query=self::trim($query);
		
		$page=1;
		CSession::set('search',array('query'=>$query,'page'=>$page));
		$search->set($query);
		
		Controller::setForView('search',$search);
		Controller::setForView('result',$this);
		$this->pagination=$search->createPagination()->execute();
		$page=$this->pagination->getPage();
		
		Controller::setForView('hSearch',new AHSearch($search,$this));
		
		$this->afterSearch($search);
	}
	
	public function afterSearch($search){
		if(($this->totalResults=$this->pagination->getTotalResults())===0)
			throw new SearchException;
	}
	
	public function hasResults(){
		return $this->totalResults!==0;
	}
	
	public static function trim($string){
		return trim($string," \t\n\r\0\x0B\"'-");
	}
}