<?php
class QTableOne extends QTable{
	public function allowFilters(){ throw new Exception('allowFilters() is not usable for QTableOne.'); }
	public function allowAdvancedFilters(){ throw new Exception('allowAdvancedFilters() is not usable for QTableOne.'); }
	public function disallowOrder(){ throw new Exception('disallowOrder() is not usable for QTableOne.'); }
	public function exportable($types,$fileName,$title=null){ throw new Exception('exportable() is not usable for QTableOne.'); }
	public function getPagination(){ throw new Exception('getPagination() is not usable for QTableOne.'); }
	
	public function pagination(){ throw new Exception('pagination() is not usable for QTableOne.'); }
	public function paginate(){ throw new Exception('paginate() is not usable for QTableOne.'); }
	
	public function end(){
		$this->process();
		
		return new CModelTableOne($this);
	}	
}