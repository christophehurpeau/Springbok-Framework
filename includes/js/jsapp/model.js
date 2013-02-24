window.M={};
S.Model=function(){
	
};
S.Model.prototype={
	findAll:function(callback){
		var t=this;
		App.api.get('models/'+t.Name+'/all',function(result){callback(t._mapAll(result))});
	},
	findOne:function(callback){
		var t=this;
		App.api.get('models/'+t.Name+'/one',callback);
	},
	findAllBy:function(){
		
	},
	_mapAll:function(r){
		var f=M[this.Name];
		return S.map(r,function(r){return new f(r); })
	}
};

S.Model.add=function(name,extendsMainObj,extendsDataObj,superclass){
	var f=function(){};
	S.extChild(f,superclass||S.Model,extendsMainObj);
	var model=new f();
	model.Name=name;
	M[name]=function(data){ if(data){ this.data=data; UObj.extend(this,data); } };
	var createFunction=function(methodName){ return S.isFunc(model[methodName]) ? function(){ return model[methodName].apply(model,arguments)} : model[methodName] ; };
	for(var k in f.prototype) if(k[0]!=='_' && k!='constructor') M[name][k]=createFunction(k);
};
