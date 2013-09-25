<?php
/**
 * Search Result with suggestions
 * 
 * @see CSearch
 * @see CSearchResult
 */
class CSearchResultSuggestionnable extends CSearchResult{
	public $suggestion,$suggestions;
	
	public function afterSearch($search){
		if(($this->totalResults=$this->pagination->getTotalResults())===0){
			if(($changes=$search->findSimilarWords())!==null){
				if(count($changes)===1){
					$this->suggestion=str_replace(array_keys($changes),array_values($changes),$this->query);
					
					$search->changeWords($changes);
					$this->pagination=$search->createPagination()->execute();
					if(($this->totalResults=$this->pagination->getTotalResults())===0) throw new SearchException;
				}else{
					$this->suggestions=array();
					foreach($changes as $ch)
						$this->suggestions[]=str_replace(array_keys($ch),array_values($ch),$q);
				}
			}else throw new SearchException;
		}
	}
	
	/** @return bool */
	public function hasSuggestions(){
		return $this->suggestions!==null;
	}
	
	/** @return bool */
	public function hasResults(){
		return $this->suggestions===null && $this->totalResults!==0;
	}
	
}