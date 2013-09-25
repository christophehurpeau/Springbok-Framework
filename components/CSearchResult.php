<?php
/**
 * Search Result
 * 
 * @see CSearch
 */
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
		
		$this->afterPagination($search);
		$this->afterSearch($search);
	}
	
	public function afterPagination($search){
		Controller::setForView('hSearch',new AHSearch($search,$this));
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