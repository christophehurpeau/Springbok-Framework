<?php
class CPagination{
	public static function create($query){
		return new self($query);
	}
	
	protected $query,$pageSize=15,$page=1,$results,$totalResults,$totalPages=0;
	
	private function __construct($query){
		$this->query=$query;
		if(isset($_REQUEST['page'])) $this->page=(int)$_REQUEST['page'];
	}
	
	public function &pageSize($pageSize){$this->pageSize=&$pageSize;return $this;}
	public function &getPageSize(){return $this->pageSize;}
	public function &page($page){$this->page=&$page;return $this;}
	public function &getPage(){return $this->page;}
	public function &getTotalResults(){return $this->totalResults;}
	public function &getTotalPages(){return $this->totalPages;}
	public function &getResults(){return $this->results;}
	public function isEmptyResults(){return empty($this->results);}
	public function hasPager(){ return $this->pageSize < $this->totalResults;}
	
	public function &execute(){
		/* HIDE *//* DEV */ try{ /* /DEV *//* /HIDE */
		if(!($hFR=$this->query->hasCalcFoundRows())){
			$count=$this->totalResults=$this->query->createCountQuery()->execute();
			if($count > 0) $this->totalPages=(int)ceil((double)$count / $this->pageSize);
		}
		/* HIDE *//* DEV */ }catch(Exception $ex){
			throw new Exception(print_r($this->query,true));
		} /* /DEV *//* /HIDE */
		if($hFR || $count > 0){
			if($this->page===1) $down=0;
			else{
				$down=$this->pageSize*($this->page-1);
				if(isset($count) && $down > $count){
					$this->results=array();
					return $this;
				}
			}
			$this->results=$this->query->limit($this->pageSize,$down)->execute();
			
			if($hFR){
				$count=$this->totalResults=$this->query->foundRows();
				if($count > 0) $this->totalPages=(int)ceil((double)$count / $this->pageSize);
			} 
			
		}else $this->results=array();
		return $this;
	}
	
	public function refindResults($page){
		$this->page=$page;
		$down=$this->pageSize*($this->page-1);
		$this->results=$this->query->limit($this->pageSize,$down)->reexecute();
	}
}