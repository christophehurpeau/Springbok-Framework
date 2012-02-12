includeCore('langs/core-fr');
$$.i18n={
	date:{
		format:'dd/mm/yyyy',
		today:{full:'Aujourd\'hui',shortened:'Auj.'},
		yesterday:{full:'Hier',shortened:'Hier'},
		monthNames:{
			full:['Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'],
			shortened:['janv','fev','mar','avr','mai','juin','juil','aout','sept','oct','nov','dec'],
			compact:['J','F','M','A','M','J','J','A','S','O','N','D']
		},
		weekDayNames:{
			full:['Dimanche','Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'],
			shortened:['dim','lun','mar','mer','jeu','ven','sam'],
			compact:['D','L','M','M','J','V','S']
		},
		periodNames:{
			full:['avant Jésus-Christ','après Jésus-Christ'],
			shortened:['av. J.-C.','ap. J.-C.'],
			compact:['av JC.','ap JC.']
		},
		formats:{
			date:{
				nice:function(date){
					var now=new Date(),str;
					if(date.getFullYear() == now.getFullYear()){
						if(date.getMonth() == now.getMonth()){
							if(now.getDate() == date.getDate()) str=$$.i18n.date.today.full;
							else if(now.getDate()-1 == date.getDate()) str=$$.i18n.date.yesterday.full;
							else str=$$.i18n.date.weekDayNames.full[date.getDay()] + ' ' + date.getDate();
						}else{
							str=$$.i18n.date.weekDayNames.full[date.getDay()] + ' ' + date.getDate()+' '+ $$.i18n.date.monthNames.full[date.getMonth()];
						}
					}else str=$$.i18n.date.weekDayNames.full[date.getDay()] + ' ' + date.getDate()+' '+ $$.i18n.date.monthNames.full[date.getMonth()] + ' '+ date.getFullYear();
					return str;
				},
				shortened:function(date){
					var now=new Date(),month=date.getMonth(),str;
					if(date.getFullYear() == now.getFullYear()){
						if(month == now.getMonth()){
							if(now.getDate() == date.getDate()) str=$$.i18n.date.today.shortened;
							else if(now.getDate()-1 == date.getDate()) str=$$.i18n.date.yesterday.shortened;
							else str=$$.i18n.date.weekDayNames.shortened[date.getDay()] + ' ' + date.getDate();
						}else{
							str=$$.i18n.date.weekDayNames.shortened[date.getDay()] + ' ' + date.getDate()+'/'+ (month<9?'0':'')+(month+1);
						}
					}else str=$$.i18n.date.weekDayNames.shortened[date.getDay()] + ' ' + date.getDate()+'/'+(month<9?'0':'')+(month+1) + ' '+ date.getFullYear();
					return str;
				},
				simple:function(date){
					var now=new Date(),
						str=date.getDate()+' '+ $$.i18n.date.monthNames.full[date.getMonth()];
					if(date.getFullYear() != now.getFullYear()) str+=' '+date.getFullYear();
					return str;
				},
				compact:function(date){
					var now=new Date(),day=date.getDate(),month=date.getMonth(),
						str=(day<10?'0':'')+day+'/'+ (month<9?'0':'')+(month+1);
					if(date.getFullYear() != now.getFullYear()) str+='/'+date.getFullYear();
					return str;
				}
			},
			times:{
				simple:function(date){
					var hours = date.getHours(), minutes = date.getMinutes();
					return ((hours < 10)?"0":"") + hours +((minutes < 10)?"h0":"h") + minutes;
				}
			},
			datetime:{
				nice:function(date){
					return $$.i18n.date.formats.date.nice(date)+' à '+$$.i18n.date.formats.times.simple(date);
				},
				shortened:function(date){
					return $$.i18n.date.formats.date.shortened(date)+' à '+$$.i18n.date.formats.times.simple(date);
				},
				simple:function(date){
					return $$.i18n.date.formats.date.simple(date)+' à '+$$.i18n.date.formats.times.simple(date);
				},
				compact:function(date){
					return $$.i18n.date.formats.date.compact(date)+' à '+$$.i18n.date.formats.times.simple(date);
				}
			}
		}
	}
};