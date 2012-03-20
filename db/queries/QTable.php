<?php
class QTable extends QFindAll{
	protected $pagination,$allowFilters=false,$autoRelations=true;
	
	public function &allowFilters(){$this->allowFilters=true; return $this;}
	public function &noAutoRelations(){$this->autoRelations=false; return $this;}
	
	public function &isFiltersAllowed(){ return $this->allowFilters; }
	public function &getPagination(){ return $this->pagination; }
	
	public function &pagination(){
		$this->pagination=CPagination::create($this)->pageSize(25);
		$table=new CModelTable($this);
		$this->pagination->setReturn($table);
		return $this->pagination;
	}
	
	public function paginate(){
		return $this->pagination()->execute();
	}
}