<?php
/**
 * Search Helper
 */
abstract class HSearch{
	/**
	 * @param CSearch
	 */
	protected $search;
	/**
	 * @param CSearchResult
	 */
	protected $result;
	
	/**
	 * @param CSearch
	 * @param CSearchResult
	 */
	public function __construct($search,$result){
		$this->search=$search;
		$this->result=$result;
	}
	
	/**
	 * @return string
	 * @uses HPagination::simple()
	 */
	public function pager(){
		return HPagination::simple($this->result->pagination);
	}
}