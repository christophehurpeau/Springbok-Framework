<?php
abstract class HSearch{
	protected $search,$result;
	
	public function __construct($search,$result){
		$this->search=$search;
		$this->result=$result;
	}
	
	public function pager(){
		return HPagination::simple($this->result->pagination);
	}
}