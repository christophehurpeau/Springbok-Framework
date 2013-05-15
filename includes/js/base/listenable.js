S.Listenable=function(){ this._events={}; };
S.Listenable.prototype={
	on:function(event,fn){
		var callbacks=this._events[event];
		if(!callbacks) callbacks=this._events[event]=$.Callbacks();
		callbacks.add(fn);
	},
	off:function(event,fn){
		var callbacks=this._events[event];
		if(callbacks) callbacks.remove(fn);
	},
	fire:function(event,args){
		/*if(this._events[event]){
			args = UArray.slice1(arguments);
			for(var i=0,events=this._events[event],l=events.length; i<l; i++)
				events[i].apply(this,args);
		}*/
		var callbacks=this._events[event];
		if(callbacks) callbacks.fire.apply(this,UArray.slice1(arguments));
	}
};