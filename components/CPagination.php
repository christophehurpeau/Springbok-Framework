<?php
class CPagination{
	public static function create($query){
		/* DEV */throw new Exception('Use QAll()->paginate() now.');/* /DEV */
		return self::_create($query);
	}
	public static function _create($query){
		return new self($query);
	}
	
	
	protected $query,$pageSize=15,$page=1,$results,$totalResults,$totalPages=0,$return;
	
	private function __construct(&$query){
		$this->query=$query; $this->return=$this;
		if(isset($_REQUEST['page'])) $this->page=(int)$_REQUEST['page'];
	}
	
	public function pageSize($pageSize){$this->pageSize=$pageSize;return $this;}
	public function getPageSize(){return $this->pageSize;}
	public function page($page){$this->page=$page;return $this;}
	public function getPage(){return $this->page;}
	public function getTotalResults(){return $this->totalResults;}
	public function getTotalPages(){return $this->totalPages;}
	public function getResults(){return $this->results;}
	public function isEmptyResults(){return empty($this->results);}
	public function hasPager(){ return $this->pageSize < $this->totalResults;}
	public function setReturn($return){$this->return=$return;}
	
	public function execute(){
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
				$down=$this->pageSize*($this->page-1);debugVar($down);
				if(isset($count) && $down >= $count){
					throw new SPaginationOverrunException;
				}
			}
			$this->results=$this->query->limit($this->pageSize,$down)->execute();
			
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
	
	public function refindResults($page){
		$this->page=$page;
		$down=$this->pageSize*($this->page-1);
		$this->results=$this->query->limit($this->pageSize,$down)->reexecute();
	}
	
	public function getResultsToArray(){
		return SModel::mToArray($this->results);
	}
}