window.C={};
S.Controller=function(methods){
	this.methods=methods;
}
S.Controller.Stop=function(){};
S.Controller.prototype={
	dispatch:function(route){
		if(this.beforeDispatch) this.beforeDispatch();
		route.sParams.unshift(route.nParams);
		var m=this.methods[route.action];
		/*#if DEV*/ if(!m) console.log('This action doesn\'t exists: "'+route.action+'". Known methods: '+Object.keys(this.methods)); /*#/if*/
		if(!m) notFound();
		m.apply(this,route.sParams);
	},
	check:function(){
		if(!S.CSecure.checkAccess()) throw new S.Controller.Stop();
	},
	layout:function(name){
		return (this.methods.layout||L[name]).render();
	},
	redirect:function(to,exit){
		App.load(to);
		if(exit) throw new S.Controller.Stop();
	},
	dispose:function(){
		delete this.methods;
	}
};
S.Controller.extend=function(name,methods,superclass){
	var target=App[name+"Controller"]=function(methods){ this.methods=methods; };
	S.extChild(target,superclass||S.Controller,methods);
	target.add=function(name,methods){ C[name]=new target(methods) };
};
S.Controller.add=function(name,methods){ C[name]=new S.Controller(methods); }
