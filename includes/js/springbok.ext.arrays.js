Array.prototype.inArray=function(val){
	return $.inArray(val,this);
};
Array.prototype.each=function(callback){
	return $.each(this,callback);
};
Array.prototype.findBy=function(propName,val){
	var k=this.findKeyBy(propName,val);
	if(k===false) return k;
	return this[k];
};
Array.prototype.findKeyBy=function(propName,val){
	var res=false;
	this.each(function(k,v){
		if(v[propName] == val){
			res=k;
			return false;
		}
	});
	return res;
};

Array.prototype.sortBy=function(propName,asc,sortFunc){
	if(!$.isFunction(sortFunc)) sortFunc=$$.arraysort[sortFunc===undefined?'':sortFunc];
	return this.sort(function(a,b){
		if(asc) return sortFunc(a[propName],b[propName]);
		return sortFunc(b[propName],a[propName]);
	});
};

Array.prototype.equalsTo=function(array){
	if(typeof array !== 'array' || this.length != array.length) return false;
	for (var i = 0; i < array.length; i++) {
        /*if (this[i].compare) { 
            if (!this[i].compare(testArr[i])) return false;
        }*/
        if(this[i] !== array[i]) return false;
    }
    return true;
};
