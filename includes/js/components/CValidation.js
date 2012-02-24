S.CValidation(){
	this.reset();
}
S.CValidation.prototype={
	reset:function(){
		this.hasErrors=false;
		this.errors={};
	},
	errors:function(){
		if(!this.hasErrors) return '';
		var str='<div class="frame errors"><h3>Oops...</h3><ul>';
		$.each(this.errors,function(key,error){
			str+='<li>'+key+' : '+error+'</li>';
		});
		return str+'</ul></div>';
	},
	addError:function(key,error){
		if(!error) return false;
		this.hasErrors=true;
		this.errors[key]=error;
		return true;
	}
};
