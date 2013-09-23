<?php
/**
 * Behavior Normalized
 * 
 * Add a field normalized and methods
 * 
 * Possible annotations : @UniqueNormalized
 * 
 * @property string $normalized
 */
trait BNormalized{
	
	/**
	 * The normalized version of the field "name"
	 * You can override this if you want to use an other field or several fields
	 * 
	 * @return string
	 */
	public function normalized(){ return UString::normalize($this->name); }
	
	/**
	 * Set the normalized field
	 * 
	 * @return true
	 */
	public function _setNormalizedIfName(){
		if(!empty($this->name)) $this->normalized=$this->normalized();
		return true;
	}
}