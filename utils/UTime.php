<?php
class UTime{
	public static function parseDuration($duration=NULL){
		if ($duration === NULL) return 60 * 60 * 24 * 30;
		$toAdd = -1; $matches=array();
		if(preg_match('^([0-9]+)d$',$duration,$matches))
			$toAdd=((int)$matches[1]) * (60 * 60) * 24;
		elseif(preg_match('^([0-9]+)h$',$duration,$matches))
			$toAdd=((int)$matches[1]) * (60 * 60);
		elseif(preg_match('^([0-9]+)mi?n$',$duration,$matches))
			$toAdd=((int)$matches[1]) * (60);
		elseif(preg_match('^([0-9]+)s$',$duration,$matches))
			$toAdd=((int)$matches[1]);
		if ($toAdd == -1)
			throw new Exception("Invalid duration pattern : " . $duration);
        return $toAdd;
	}
}
