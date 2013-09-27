<?php
/**
 * Query for One Row
 */
class QTableOne extends QTable{
	
	/** @ignore */
	public function allowFilters(){ throw new Exception('allowFilters() is not usable for QTableOne.'); }
	/** @ignore */
	public function allowAdvancedFilters(){ throw new Exception('allowAdvancedFilters() is not usable for QTableOne.'); }
	/** @ignore */
	public function disallowOrder(){ throw new Exception('disallowOrder() is not usable for QTableOne.'); }
	/** @ignore */
	public function exportable($types,$fileName,$title=null){ throw new Exception('exportable() is not usable for QTableOne.'); }
	/** @ignore */
	public function getPagination(){ throw new Exception('getPagination() is not usable for QTableOne.'); }
	
	/** @ignore */
	public function pagination(){ throw new Exception('pagination() is not usable for QTableOne.'); }
	/** @ignore */
	public function paginate(){ throw new Exception('paginate() is not usable for QTableOne.'); }
	
	/**
	 * @return CModelTableOne
	 */
	public function end(){
		$this->process();
		
		return new CModelTableOne($this);
	}
	
	/**
	 * @return CModelTableOne
	 */
	public function notFoundIfFalse(){
		return $this->end()->notFoundIfFalse();
	}
}