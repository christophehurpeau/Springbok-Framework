S.Controller=function(methods){
	this.methods=methods;
}
S.Controller.prototype={
	dispatch:function(route){
		this.methods[route.action].apply(this);
	},
	layout:function(name){
		return S.app.layouts[name].render();
	}
};


S.DefaultController=new S.Controller({
	
});
