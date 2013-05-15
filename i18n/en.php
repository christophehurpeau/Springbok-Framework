<?php return array(
	'decimalFormat'=>array('decimalSep'=>'.','thousandsSep'=>','),
	'percentFormat'=>'%s %%',
	'scientificFormat' => '#E0',
	'currencyFormat' => '¤#,##0.00',
	
	'isPlural'=>function(&$number){ return $number!==1; },
	
	'dates'=>array(
		'monthNames'=>array(
			'full'=>array(1=>'January','February','March','April','May','June','July','August','September','October','November','December'),
			'short'=>array(1=>'jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov','dec'),
			'compact'=>array(1=>'J','F','M','A','M','J','J','A','S','O','N','D')
		),
		'weekDayNames'=>array(
			'full'=>array(0=>'Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'),
			'short'=>array(0=>'sun','mon','tue','wed','thu','fri','sat'),
			'compact'=>array(0=>'S','M','T','W','T','F','S')
		),
		'periodNames'=>array(
			'full'=>array('Before Christ','Anno Domini'),
			'short'=>array('BC','AD'),
			'compact'=>array('BC','AD'),
		),
	),
);