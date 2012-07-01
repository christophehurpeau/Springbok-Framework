window.M={};
S.Model=function(){
	
};
S.Model.prototype={
	findAll:function(){
		
	},
	findOne:function(){
		
	},
	findAllBy:function(){
		
	}
};

S.Model.add=function(name,fields,methods,superclass){
	M[name]=function(methods){ this.methods=methods; };
	M[name].Fields=fields;
	S.extendsClass(M[name],superclass||S.Model,methods);
};
