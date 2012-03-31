<?php
class UTime{
	public static function parseDuration($duration=null){
		if ($duration === NULL) return 60 * 60 * 24 * 30;
		$toAdd = -1; $matches=array();
		if(preg_match('^(\d+)d$',$duration,$matches))
			$toAdd=((int)$matches[1]) * (60 * 60) * 24;
		elseif(preg_match('^(\d+)h$',$duration,$matches))
			$toAdd=((int)$matches[1]) * (60 * 60);
		elseif(preg_match('^(\d+)mi?n$',$duration,$matches))
			$toAdd=((int)$matches[1]) * (60);
		elseif(preg_match('^(\d+)s$',$duration,$matches))
			$toAdd=((int)$matches[1]);
		if ($toAdd == -1)
			throw new Exception("Invalid duration pattern : " . $duration);
        return $toAdd;
	}
	
	const REG_EXPR_HOURS_MINUTES=/* HIDE */''/*/HIDE *//* EVAL '((\d+)(h|hours?))(?:\s*(\d+)(m|min)?)?'
								.'|'.'((\d+)(h|hours?|m|min))'
								.'|'.'(\d+:\d+)'
								.'|'.'(\d+(?:[\.,]\d+)?)h?' */;
	public static function parseHoursDuration($duration){
		
	}
}
