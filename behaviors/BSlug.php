<?php
trait BSlug{
	public function auto_slug(){ return HString::slug($this->name); }
	
	public function _setSlugIfName(){
		if(!empty($this->name) && empty($this->slug)) $this->slug=$this->auto_slug();
		return true;
	}
}