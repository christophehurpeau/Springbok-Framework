<?php
/**
 * A Cached element which is not cached...
 */
class SViewCacheStoreFileAlwaysRerender extends SViewCacheStoreFile{
	
	public function exists(){
		return false;
	}
	
}
