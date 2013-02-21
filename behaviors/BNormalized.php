<?php
trait BNormalized{
	public function normalized(){ return UString::normalize($this->name); }
	
	public function _setNormalizedIfName(){
		if(!empty($this->name)){
			$this->normalized=$this->normalized();
		}
		return true;
	}
}