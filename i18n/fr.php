<?php
return array(
	'decimalFormat'=>array('decimal_sep'=>',','thousands_sep'=>' '),
	'percentFormat'=>'%s %%',
	'scientificFormat' => '#E0',
	'currencyFormat' => '#,##0.00 ¤',
	
	'dates'=>array(
		'monthNames'=>array(
			'full'=>array(1=>'Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'),
			'short'=>array(1=>'janv','fev','mar','avr','mai','juin','juil','aout','sept','oct','nov','dec'),
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
		'formats'=>array(
			'date'=>array(
				'nice'=>function(&$locale,&$time){
					$res=$locale->weekDayName(date('w',$time),'full').' '.date('j',$time).' '.$locale->monthName(date('n',$time),'full');
					if(($Y=date('Y',$time))!=date('Y')) $res.=' '.$Y;
					return $res;
				},
				'short'=>function(&$locale,&$time){
					$res=$locale->weekDayName(date('w',$time),'short').' '.date('d/m',$time);
					if(($Y=date('Y',$time))!=date('Y')) $res.=' '.$Y;
					return $res;
				},
				'simple'=>function(&$locale,&$time){
					$res=date('j',$time).' '.$locale->monthName(date('n',$time),'full');
					if(($Y=date('Y',$time))!=date('Y')) $res.=' '.$Y;
					return $res;
				},
				'compact'=>function(&$locale,&$time){
					$res=date('d/m',$time);
					if(($Y=date('Y',$time))!=date('Y')) $res.='/'.$Y;
					return $res;
				},
				'complete'=>function(&$locale,&$time){
					return date('d/m/Y',$time);
				}
			),
			'time'=>array(
				'simple'=>function(&$locale,&$time){return date('H\hi',$time);},
				'complete'=>function(&$locale,&$time){return date('H:i:s',$time);},
			),
			'datetime'=>array(
				'nice'=>function(&$locale,&$time){return $locale->formatDate($time,'nice').' à '.$locale->formatTime($time,'simple');},
				'short'=>function(&$locale,&$time){return $locale->formatDate($time,'short').' à '.$locale->formatTime($time,'simple');},
				'simple'=>function(&$locale,&$time){return $locale->formatDate($time,'simple').' à '.$locale->formatTime($time,'simple');},
				'compact'=>function(&$locale,&$time){return $locale->formatDate($time,'compact').' à '.$locale->formatTime($time,'simple');},
				'complete'=>function(&$locale,&$time){return $locale->formatDate($time,'complete').' à '.$locale->formatTime($time,'complete');},
			),
			
			'monthAndYear'=>array(
				'simple'=>function(&$locale,&$time){
					$res=$locale->monthName(date('n',$time),'full');
					if(($Y=date('Y',$time))!=date('Y')) $res.=' '.$Y;
					return $res;
				}
			)
		),
	)
);
