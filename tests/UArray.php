<?php
class UArrayTest extends STest{
	function hasAmong(){
		assert(UArray::hasAmong(array(1,2),array(1))===true);
		assert(UArray::hasAmong(array(1,2),array(3,4))===false);
	}
}
