S.HEltFCont=function(contained,defaultClass){
	this._form=contained._form;
	this.elt=$('<'+(contained._form._tagContainer||'div')+'/>').addClass(defaultClass).append(contained.toElt());
};
S.extendsClass(S.HEltFCont,S.HElt,{
	//tagContainer:function(tag){ this.elt=$('<'+tag+'/>').attr(this.attr()) return this; }
	before:function($content){ this.elt.prepend(content); return this; },
	after:function($content){ this.elt.append(content); return this; },
	//error:function($message){ $this._error=message; return this; },
	//noError:function(){ $this->error=false; return this; },
	
	end:function(){ return this._form.append(this.elt); }
});
