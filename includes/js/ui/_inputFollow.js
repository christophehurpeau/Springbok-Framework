includeCore('springbok.base');
includeCore('ui/base');

S.ui.InputFollow=S.Widget.extend({
	ctor:function(input){
		this.input=input.on('dispose',function(){ this.dispose(); }.bind(this));
	},
	isNotEditable:function(){return this.input[0].disabled || this.input[0].readonly;}
});
