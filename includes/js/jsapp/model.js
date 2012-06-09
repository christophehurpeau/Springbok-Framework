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

S.Model.extend=function(name,methods,superclass){
	M[name]=function(methods){ this.methods=methods; };
	S.extendsClass(M[name],superclass||S.Model,methods);
};
