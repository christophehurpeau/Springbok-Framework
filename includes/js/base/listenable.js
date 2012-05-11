S.Listenable=function(){this._events={};};
S.Listenable.prototype={
	on:function(event,fn){
		this._events[event] ? this._events[event].push(fn) : this._events[event]=[fn];
	},
	off:function(event,fn){
		
	},
	trigger:function(event,args){
		if(this._events[event]){
			args = arraySliceFunction.call(arguments,1);
			for(var i=0,events=this._events[event],l=events.length; i<l; i++)
				events[i].apply(this,args);
		}
	}
};
