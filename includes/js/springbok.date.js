S.dates={
	parseStringDate:function(date){
		date=date.split(' ');
		date[0]=date[0].split('-')
		if(date.length == 2){
			date[1]=date[1].split(':');
			return new Date(date[0][0],date[0][1]-1,date[0][2],date[1][0],date[1][1]);
		}
		return new Date(date[0][0],date[0][1]-1,date[0][2]);
	},
	getDaysInMonth:function(year, month){
		return [31,(this.isLeapYear(year)?29:28),31,30,31,30,31,31,30,31,30,31][month];
	},
	isLeapYear:function(year){
		return ((year % 4 === 0 && year % 100 !== 0) || year % 400 === 0);
	},
	niceDate:function(date){
		if(!date) date=new Date();
		else if(S.isStr(date)) date=this.parseStringDate(date);
		else if(parseInt(date) === date) date=new Date(date);
		return S.i18n.formatDateNice(date);
	},
	shortDate:function(date){
		if(!date) date=new Date();
		else if(S.isStr(date)) date=this.parseStringDate(date);
		else if(parseInt(date) === date) date=new Date(date);
		return S.i18n.formatDateShort(date);
	},
	compactDate:function(date){
		if(!date) date=new Date();
		else if(S.isStr(date)) date=this.parseStringDate(date);
		else if(parseInt(date) === date) date=new Date(date);
		return S.i18n.formatDateCompact(date);
	},
	simpleDate:function(date){
		if(!date) date=new Date();
		else if(S.isStr(date)) date=this.parseStringDate(date);
		else if(parseInt(date) === date) date=new Date(date);
		return S.i18n.formatDateSimple(date);
	},
	completeDate:function(date){
		if(!date) date=new Date();
		else if(S.isStr(date)) date=this.parseStringDate(date);
		else if(parseInt(date) === date) date=new Date(date);
		return S.i18n.formatDateComplete(date);
	},
	
	simpleTime:function(date){
		if(!date) date=new Date();
		else if(S.isStr(date)) date=this.parseStringDate(date);
		else if(parseInt(date) === date) date=new Date(date);
		return S.i18n.formatTimeSimple(date);
	},
	niceDateTime:function(date){
		if(!date) date=new Date();
		else if(S.isStr(date)) date=this.parseStringDate(date);
		else if(parseInt(date) === date) date=new Date(date);
		return S.i18n.formatDatetimeNice(date);
	},
	compactDateTime:function(date){
		if(!date) date=new Date();
		else if(S.isStr(date)) date=this.parseStringDate(date);
		else if(parseInt(date) === date) date=new Date(date);
		return S.i18n.formatDatetimeCompact(date);
	},
	simpleDateTime:function(date){
		if(!date) date=new Date();
		else if(S.isStr(date)) date=this.parseStringDate(date);
		else if(parseInt(date) === date) date=new Date(date);
		return S.i18n.formatDatetimeSimple(date);
	},
	
	completeDateTime:function(date){
		if(!date) date=new Date();
		else if(S.isStr(date)) date=this.parseStringDate(date);
		else if(parseInt(date) === date) date=new Date(date);
		return S.i18n.formatDatetimeComplete(date);
	}
};
Date.prototype.sToSqlDate=function(justDate){
	var day=this.getDate(),month=this.getMonth(),
		str=this.getFullYear()+'-'+(day<10?'0':'')+day+'-'+ (month<9?'0':'')+(month+1);
	if(justDate) return str;
	var hours = this.getHours(), minutes = this.getMinutes(),seconds=this.getSeconds();
	return str+' '+((hours < 10)?"0":"")+hours +((minutes < 10)?":0":":") + minutes +((seconds < 10)?":0":":") + seconds;
};
