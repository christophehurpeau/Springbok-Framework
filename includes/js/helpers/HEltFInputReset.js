S.HEltFInputReset=S.extClass(S.HEltFContble,{
	ctor:function(form,title){
		S.HEltFInputReset.superCtor.call(this,form);
		this.elt=$('<input type="reset" class="submit reset">')/*.data('sElt',this)*/.attr('value',title===undefined?i18nc.Save:title);
	},
	container:function(){ return new S.HEltFCont(this,'submit reset'); },
	toElt:function(){ return this.elt; }
});
