S.HEltFInputSubmit=function(form,title){S.HEltFInputSubmit.superctor.call(this,form,name);
				this.elt=$('<input type="submit" class="submit"/>').attr('value',title===undefined?i18nc.Save:title); };
S.extendsClass(S.HEltFInputSubmit,S.HEltFContble,{
	container:function(){ var type=this.elt.attr('type'); return new S.HEltFCont(this,'submit'); },
	toElt:function(){ return this.elt; }
});
