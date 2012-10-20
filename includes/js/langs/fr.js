S.i18n={
	decimalFormat:{decimalSep:',',thousandsSep:' '},
	percentFormat:'%s %%',
	scientificFormat:'#E0',
	currencyFormat:'#,##0.00 ¤',
	
	isPlural:function(number){ return number>1; },
	
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
		}
	},
	formatDateNice:function(date){
		var now=new Date(),str;
		if(date.getFullYear() == now.getFullYear()){
			if(date.getMonth() == now.getMonth()){
				if(now.getDate() == date.getDate()) str=this.date.today.full;
				else if(now.getDate()-1 == date.getDate()) str=this.date.yesterday.full;
				else str=this.date.weekDayNames.full[date.getDay()] + ' ' + date.getDate();
			}else{
				str=this.date.weekDayNames.full[date.getDay()] + ' ' + date.getDate()+' '+ this.date.monthNames.full[date.getMonth()];
			}
		}else str=this.date.weekDayNames.full[date.getDay()] + ' ' + date.getDate()+' '+ this.date.monthNames.full[date.getMonth()] + ' '+ date.getFullYear();
		return str;
	},
	formatDateShort:function(date){
		var now=new Date(),month=date.getMonth(),str;
		if(date.getFullYear() == now.getFullYear()){
			if(month == now.getMonth()){
				if(now.getDate() == date.getDate()) str=this.date.today.shortened;
				else if(now.getDate()-1 == date.getDate()) str=this.date.yesterday.shortened;
				else str=this.date.weekDayNames.shortened[date.getDay()] + ' ' + date.getDate();
			}else{
				str=this.date.weekDayNames.shortened[date.getDay()] + ' ' + date.getDate()+'/'+ (month<9?'0':'')+(month+1);
			}
		}else str=this.date.weekDayNames.shortened[date.getDay()] + ' ' + date.getDate()+'/'+(month<9?'0':'')+(month+1) + ' '+ date.getFullYear();
		return str;
	},
	formatDateSimple:function(date){
		var now=new Date(),
			str=date.getDate()+' '+ this.date.monthNames.full[date.getMonth()];
		if(date.getFullYear() != now.getFullYear()) str+=' '+date.getFullYear();
		return str;
	},
	formatDateCompact:function(date){
		var now=new Date(),day=date.getDate(),month=date.getMonth(),
			str=(day<10?'0':'')+day+'/'+ (month<9?'0':'')+(month+1);
		if(date.getFullYear() != now.getFullYear()) str+='/'+date.getFullYear();
		return str;
	},
	formatDateComplete:function(date){
		var now=new Date(),day=date.getDate(),month=date.getMonth()
		return (day<10?'0':'')+day+'/'+ (month<9?'0':'')+(month+1)+'/'+date.getFullYear();
	},
	formatTimeSimple:function(date){
		var hours = date.getHours(), minutes = date.getMinutes();
		return ((hours < 10)?"0":"") + hours +((minutes < 10)?"h0":"h") + minutes;
	},
	formatTimeComplete:function(date){
		var hours = date.getHours(), minutes = date.getMinutes();
		return ((hours < 10)?"0":"") + hours +((minutes < 10)?":0":":") + minutes;
	},
	formatDatetimeNice:function(date){
		return this.formatDateNice(date)+' à '+this.formatTimeSimple(date);
	},
	formatDatetimeShort:function(date){
		return this.formatDateShort(date)+' à '+this.formatTimeSimple(date);
	},
	formatDatetimeSimple:function(date){
		return this.formatDateSimple(date)+' à '+this.formatTimeSimple(date);
	},
	formatDatetimeCompact:function(date){
		return this.formatDateCompact(date)+' à '+this.formatTimeSimple(date);
	},
	formatDatetimeComplete:function(date){
		return this.formatDateComplete(date)+' à '+this.formatTimeComplete(date);
	},
	
	formatMonthAndYearSimple:function(date){
		var now=new Date(),str=this.date.monthNames.full[date.getMonth()];
		if(date.getFullYear() != now.getFullYear()) str+=' '+date.getFullYear();
		return str;
	}
};
includeCore('langs/core-fr');