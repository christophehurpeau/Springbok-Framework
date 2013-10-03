<?php
/**
 * Paginate queries
 * 
 */
class CPagination{
	/** @deprecated */
	public static function create($query){
		/*#if DEV */throw new Exception('Use QAll()->paginate() now.');/*#/if*/
		return self::_create($query);
	}
	/** @ignore */
	public static function _create($query){
		return new self($query);
	}
	
	
	protected $query,$pageSize=15,$page=1,$results,$totalResults,$totalPages=0,$return;
	
	private function __construct($query){
		$this->query=$query;
		$this->return=$this;
		if(isset($_REQUEST['page'])) $this->page=(int)$_REQUEST['page'];
	}
	
	/**
	 * Set the number of results
	 * 
	 * @param int number of results
	 * @return CPagination
	 */
	public function pageSize($pageSize){
		$this->pageSize = $pageSize;
		return $this;
	}
	
	/**
	 * @return int number of results
	 */
	public function getPageSize(){
		return $this->pageSize;
	}
	
	/**
	 * Set the current page
	 * 
	 * @param int number of the current page
	 * @return CPagination
	 */
	public function page($page){
		$this->page=(int)$page;
		return $this;
	}
	
	/**
	 * Get the current page
	 * 
	 * @return int number of the current page
	 */
	public function getPage(){
		return $this->page;
	}
	
	/**
	 * Get the total number of results
	 * 
	 * @return int number of the current page
	 */
	public function getTotalResults(){
		return $this->totalResults;
	}
	
	/**
	 * Get the total number of pages, computed from the number of results and the page size
	 * 
	 * @return int total number of pages
	 */
	public function getTotalPages(){
		return $this->totalPages;
	}
	
	/**
	 * Get the results
	 * 
	 * @return array
	 */
	public function getResults(){
		return $this->results;
	}
	
	/**
	 * Return if there is no results
	 * 
	 * @return bool
	 */
	public function isEmptyResults(){
		return empty($this->results);
	}
	
	/**
	 * Return if the number of results is  
	 * 
	 * @return bool
	 */
	public function hasPager(){
		return $this->pageSize < $this->totalResults;
	}
	
	/**
	 * Set the return of the execute function
	 * 
	 * @param mixed
	 * @return void
	 */
	public function setReturn($return){
		$this->return=$return;
	}
	
	/**
	 * Create a count Query and execute it
	 * 
	 * @return int
	 */
	protected function _countQuery(){
		return $this->query->createCountQuery()->fetch();
	}
	/**
	 * Return query
	 * 
	 * @return QAll
	 */
	public function _getQuery(){
		return $this->query;
	}
	
	/**
	 * Execute the query and compute counts and results
	 * 
	 * @return CPagination|CModelTable|mixed the return result
	 */
	public function execute(){
		/*#if false*//*#if DEV */ try{ /*#/if*//*#/if*/
		if(!($hFR=$this->query->hasCalcFoundRows())){
			$count=$this->totalResults=$this->_countQuery();
			if($count > 0) $this->totalPages=(int)ceil((double)$count / $this->pageSize);
		}
		/*#if false*//*#if DEV */ }catch(Exception $ex){
			throw new Exception(print_r($this->query,true));
		} /*#/if*//*#/if*/
		if($hFR || $count > 0){
			if($this->page===1) $down=0;
			else{
				$down=$this->pageSize*($this->page-1);
				if(isset($count) && $down >= $count){
					throw new SPaginationOverrunException;
				}
			}
			$this->results=$this->query->limit($this->pageSize,$down)->fetch();
			
			if($hFR){
				$count=$this->totalResults=$this->query->foundRows();
				if($count > 0){
					$this->totalPages=(int)ceil((double)$count / $this->pageSize);
					if(empty($this->results)) throw new SPaginationOverrunException;
				}
			}
			
		}else $this->results=array();
		return $this->return;
	}
	
	/**
	 * Alias of execute
	 * @see execute()
	 */
	public function fetch(){
		return $this->execute();
	}
	
	/** @internal */
	public function _execute_(){
		return $this->fetch();
	}
	
	/**
	 * Rexecute the query to find another page
	 * 
	 * @param int
	 * @return void
	 */
	public function refindResults($page){
		$this->page=$page;
		$down=$this->pageSize*($this->page-1);
		$this->results=$this->query->limit($this->pageSize,$down)->reexecute();
	}
	
	/**
	 * Transform the results to array
	 * 
	 * @return array
	 */
	public function getResultsToArray(){
		return SModel::mToArray($this->results);
	}
	
	/**
	 * Set HTML metas : robots, prev, next
	 * 
	 * @param function callback : function($page){}
	 * @return void
	 */
	public function metas($linkFn){
		$p=$this->page;
		if($this->totalPages>1){
			if($p>1){
				if(!HHead::isMetaNameSet('robots'))
					HHead::metaName('robots','noindex, follow');
				HMeta::prev(call_user_func($linkFn,$p===2?null:$p-1));
			}
			if($p<$this->totalPages) HMeta::next(call_user_func($linkFn,$p+1));
		}
		return $p;
	}
}