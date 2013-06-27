<?php
class SViewCacheStoreFileAlwaysRerender extends SViewCacheStoreFile{
	
	public function exists(){
		return false;
	}
	
}
