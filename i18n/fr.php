<?php return array(
	'decimalFormat'=>array('decimalSep'=>',','thousandsSep'=>' '),
	'percentFormat'=>'%s %%',
	'scientificFormat' => '#E0',
	'currencyFormat' => '#,##0.00 ¤',
	
	'isPlural'=>function($number){ return $number>1; },
	
	'dates'=>array(
		'monthNames'=>array(
			'full'=>array(1=>'Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'),
			'short'=>array(1=>'janv','fev','mars','avr','mai','juin','juil','aout','sept','oct','nov','dec'),
			'compact'=>array(1=>'J','F','M','A','M','J','J','A','S','O','N','D')
		),
		'weekDayNames'=>array(
			'full'=>array(0=>'Dimanche','Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'),
			'short'=>array(0=>'dim','lun','mar','mer','jeu','ven','sam'),
			'compact'=>array(0=>'D','L','M','M','J','V','S')
		),
		'periodNames'=>array(
			'full'=>array('avant Jésus-Christ','après Jésus-Christ'),
			'short'=>array('av. J.-C.','ap. J.-C.'),
			'compact'=>array('av JC.','ap JC.'),
		),
	),
	'formatDateNice'=>function($locale,$time){
		$res=$locale->weekDayName(date('w',$time),'full').' '.date('j',$time).' '.$locale->monthName(date('n',$time),'full');
		if(($Y=date('Y',$time))!=date('Y')) $res.=' '.$Y;
		return $res;
	},
	'formatDateShort'=>function($locale,$time){
		$res=$locale->weekDayName(date('w',$time),'short').' '.date('d/m',$time);
		if(($Y=date('Y',$time))!=date('Y')) $res.=' '.$Y;
		return $res;
	},
	'formatDateSimple'=>function($locale,$time){
		$res=date('j',$time).' '.$locale->monthName(date('n',$time),'full');
		if(($Y=date('Y',$time))!=date('Y')) $res.=' '.$Y;
		return $res;
	},
	'formatDateCompact'=>function($locale,$time){
		$res=date('d/m',$time);
		if(($Y=date('Y',$time))!=date('Y')) $res.='/'.$Y;
		return $res;
	},
	'formatDateComplete'=>function($locale,$time){
		return date('d/m/Y',$time);
	},
	'formatTimeSimple'=>function($locale,$time){return date('H\hi',$time);},
	'formatTimeComplete'=>function($locale,$time){return date('H:i:s',$time);},
	'formatDatetimeNice'=>function($locale,$time){return $locale->formatDate($time,'nice').' à '.$locale->formatTime($time,'simple');},
	'formatDatetimeShort'=>function($locale,$time){return $locale->formatDateShort($time).' à '.$locale->formatTimeSimple($time);},
	'formatDatetimeSimple'=>function($locale,$time){return $locale->formatDateSimple($time).' à '.$locale->formatTimeSimple($time);},
	'formatDatetimeCompact'=>function($locale,$time){return $locale->formatDateCompact($time).' à '.$locale->formatTimeSimple($time);},
	'formatDatetimeComplete'=>function($locale,$time){return $locale->formatDateComplete($time).' à '.$locale->formatTimeComplete($time);},
		
	'formatMonthAndYearSimple'=>function($locale,$time){
		$res=$locale->monthName(date('n',$time),'full');
		if(($Y=date('Y',$time))!=date('Y')) $res.=' '.$Y;
		return $res;
	}
);
