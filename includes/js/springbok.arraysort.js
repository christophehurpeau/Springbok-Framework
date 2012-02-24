S.arraysort={
	'':function(a,b){
		if(a < b) return -1;
		if(a > b) return 1;
		return 0;
	},
	dates:function(a,b){
		return a-b;
	},
	stringDates:function(a,b){
		var da=S.dates.parseStringDate(a),
			db=S.dates.parseStringDate(b);
		return da-db;
	},
	stringNoCase:function(a,b){
		var na=a.toLowerCase();
		var nb=b.toLowerCase();
		if(na < nb) return -1;
		if(na > nb) return 1;
		return 0;
	},
	numbers:function(a,b){
		return b-a;
	}
};
