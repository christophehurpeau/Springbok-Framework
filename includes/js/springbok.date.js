$$.dates={
	parseStringDate:function(date){
		date=date.split(' ');
		date[0]=date[0].split('-')
		if(date.length == 2){
			date[1]=date[1].split(':');
			return new Date(date[0][0],date[0][1]-1,date[0][2],date[1][0],date[1][1]);
		}
		return new Date(date[0][0],date[0][1]-1,date[0][2]);
	},
	niceDate:function(date){
		if(!date) date=new Date();
		else if($$.isString(date)) date=this.parseStringDate(date);
		else if(parseInt(date) === date) date=new Date(date);
		return $$.i18n.date.formats.date.nice(date);
	},
	shortDate:function(date){
		if(!date) date=new Date();
		else if($$.isString(date)) date=this.parseStringDate(date);
		else if(parseInt(date) === date) date=new Date(date);
		return $$.i18n.date.formats.date.shortened(date);
	},
	compactDate:function(date){
		if(!date) date=new Date();
		else if($$.isString(date)) date=this.parseStringDate(date);
		else if(parseInt(date) === date) date=new Date(date);
		return $$.i18n.date.formats.date.compact(date);
	},
	simpleDate:function(date){
		if(!date) date=new Date();
		else if($$.isString(date)) date=this.parseStringDate(date);
		else if(parseInt(date) === date) date=new Date(date);
		return $$.i18n.date.formats.date.simple(date);
	},
	simpleTime:function(date){
		if(!date) date=new Date();
		else if($$.isString(date)) date=this.parseStringDate(date);
		else if(parseInt(date) === date) date=new Date(date);
		return $$.i18n.date.formats.times.simple(date);
	},
	niceDateTime:function(date){
		if(!date) date=new Date();
		else if($$.isString(date)) date=this.parseStringDate(date);
		else if(parseInt(date) === date) date=new Date(date);
		return $$.i18n.date.formats.datetime.nice(date);
	},
	shortDate:function(date){
		if(!date) date=new Date();
		else if($$.isString(date)) date=this.parseStringDate(date);
		else if(parseInt(date) === date) date=new Date(date);
		return $$.i18n.date.formats.datetime.shortened(date);
	},
	compactDateTime:function(date){
		if(!date) date=new Date();
		else if($$.isString(date)) date=this.parseStringDate(date);
		else if(parseInt(date) === date) date=new Date(date);
		return $$.i18n.date.formats.datetime.compact(date);
	},
	simpleDateTime:function(date){
		if(!date) date=new Date();
		else if($$.isString(date)) date=this.parseStringDate(date);
		else if(parseInt(date) === date) date=new Date(date);
		return $$.i18n.date.formats.datetime.simple(date);
	}
};
Date.prototype.toSqlDate=function(justDate){
	var day=this.getDate(),month=this.getMonth(),
		str=this.getFullYear()+'-'+(day<10?'0':'')+day+'-'+ (month<9?'0':'')+(month+1);
	if(justDate) return str;
	var hours = this.getHours(), minutes = this.getMinutes(),seconds=this.getSeconds();
	return str+' '+((hours < 10)?"0":"")+hours +((minutes < 10)?":0":":") + minutes +((seconds < 10)?":0":":") + seconds;
};
