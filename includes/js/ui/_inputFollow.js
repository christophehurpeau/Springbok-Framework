includeCore('springbok.base');

S.ui.InputFollow=function(input,div){
	var t=this;
	this.hasFocus=false;
	this.input=input;
	this.div=div;
};

S.ui.InputFollow.prototype={
	isNotEditable:function(){return this.input.is(':disabled')||this.input.prop('readonly');}
};
