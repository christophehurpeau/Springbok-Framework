<?php
class FAbstractTransformer{
	protected $tableClass;
	
	public function __construct($tableClass){
		$this->tableClass=&$tableClass;
	}
}
