S.CallbacksOnce=function(){
	var callbacks=[];
	return {
		add:function(callback){
			callbacks.push(callback);
		},
		fire:function(thisArg,args){
			while(callbacks.length>0)
				callbacks.shift().apply(thisArg,args);
		}
	}
};
