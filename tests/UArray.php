<?php
/** UArray Test Class */
class UArrayTest extends STest{
	function findKeyBy(){
		$array = array(
			'a'=>array('prop'=>'test'),
			'b'=>array('prop'=>'test2')
		);
		$this->equals(UArray::findKeyBy($array,'prop','test'), 'a');
		$this->equals(UArray::findKeyBy($array,'prop','test2'), 'b');
		$this->equals(UArray::findKeyBy($array,'prop','test3'), 'c');
		
	}
	
	function splice(){
		$this->equals(UArray::splice(array(1,2,3,4,5),0,array('a'=>6,'b'=>7)), array('a'=>6,'b'=>7,1,2,3,4,5));
		$this->equals(UArray::splice(array(1,2,3,4,5),3,array('a'=>6,'b'=>7)), array(1,2,3,'a'=>6,'b'=>7,4,5));
		$this->equals(UArray::splice(array(1,2,3,4,5),4,array()), array(1,2,3,4,5));
	}
	
	function hasAmong(){
		$this->equals(UArray::hasAmong(array(1,2),array(1)), true);
		$this->equals(UArray::hasAmong(array(1,2),array(3,4)), false);
	}
	
	function firstValue(){
		$this->equals(UArray::firstValue(array(1)), 1);
		$this->equals(UArray::firstValue(array('2',3)), '2');
	}
	
	function map(){
		$this->equals(UArray::map(array(1,2),function($k,$v){ return $k; }), array(0,1));
		$this->equals(UArray::map(array(1,2),function($k,$v){ return $v+1; }), array(2,3));
	}
}
