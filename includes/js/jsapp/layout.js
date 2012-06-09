window.L={};
S.Layout=function(name,methods,init){
	var t=this;
	L[t.name=name]=t;
	t.page=S.app.page;
	t.init=init;
	$.each(methods,function(i,v){
		t[v]=function(content){
			t['$'+v].html(content);
			return t;
		};
	});
};
S.Layout.prototype={
	render:function(){
		if(this.page.data('layout')!==this.name){
			this.init(this.page);
		}
		return this;
	}
};
