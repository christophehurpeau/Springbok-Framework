S.Controller=function(methods){
	this.methods=methods;
}
S.Controller.prototype={
	dispatch:function(route){
		if(this.beforeDispatch) if(!this.beforeDispatch()) return;
		route.sParams.unshift(route.nParams)
		var m=this.methods[route.action];
		/* DEV */ console.log('This action doesn\'t exists: '+route.action); /* /DEV */
		if(!m) notFound();
		m.apply(this,route.sParams);
	},
	check:function(){
		return S.CSecure.checkAccess();
	},
	layout:function(name){
		return S.app.layouts[name].render();
	}
};
S.Controller.extend=function(name,methods,superclass){
	S[name]=function(methods){ this.methods=methods; };
	extend(S[name],superclass||S.Controller,methods);
};

S.DefaultController=new S.Controller({
	
});
