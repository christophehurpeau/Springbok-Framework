S.HEltFInputSubmit=function(form,title){
	S.HEltFInputSubmit.superctor.call(this,form);
	this.elt=$('<input type="submit" class="submit">')/*.data('sElt',this)*/.attr('value',title===undefined?i18nc.Save:title);
};
S.extendsClass(S.HEltFInputSubmit,S.HEltFContble,{
	container:function(){ return new S.HEltFCont(this,'submit'); },
	toElt:function(){ return this.elt; }
});
