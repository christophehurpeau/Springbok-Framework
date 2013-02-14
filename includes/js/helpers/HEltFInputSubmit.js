S.HEltFInputSubmit=S.extClass(S.HEltFContble,{
	ctor:function(form,title){
		S.HEltFInputSubmit.superCtor.call(this,form);
		this.elt=$('<input type="submit" class="submit">')/*.data('sElt',this)*/.attr('value',title===undefined?i18nc.Save:title);
	},
	container:function(){ return new S.HEltFCont(this,'submit'); },
	toElt:function(){ return this.elt; }
});
