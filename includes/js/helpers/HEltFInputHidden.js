S.HEltFInputHidden=S.extClass(S.HElt,{
	ctor:function(form,name,value){
		this._form=form;
		this.elt=$('<input type="hidden">')/*.data('sElt',this)*/.attr({name:name,value:value});
	},
	toElt:function(){ return this.elt; },
	end:function(){ this._form.append(this.toElt()); return this._form; },
	
});
