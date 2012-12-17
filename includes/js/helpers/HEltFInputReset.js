S.HEltFInputReset=function(form,title){
	S.HEltFInputReset.superctor.call(this,form);
	this.elt=$('<input type="reset" class="submit reset">')/*.data('sElt',this)*/.attr('value',title===undefined?i18nc.Save:title);
};
S.extendsClass(S.HEltFInputReset,S.HEltFContble,{
	container:function(){ return new S.HEltFCont(this,'submit reset'); },
	toElt:function(){ return this.elt; }
});
