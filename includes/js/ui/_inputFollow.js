includeCore('springbok.base');
includeCore('ui/base');

S.ui.InputFollow=S.Widget.extend({
	ctor:function(input){
		var t=this;
		this.input=input.bind('dispose',function(){ t.dispose(); });
	},
	isNotEditable:function(){return this.input.is(':disabled')||this.input.prop('readonly');}
});
