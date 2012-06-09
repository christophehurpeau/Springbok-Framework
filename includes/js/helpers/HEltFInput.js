S.HEltFInput=function(form,name,largeSize){S.HEltFInput.superctor.call(this,form,name); this._largeSize=1;
				this.elt=$('<input type="text"/>'); this._setAttrValue(); this._setAttrId(); this._setAttrName(); }
S.extendsClass(S.HEltFInput,S.HEltFContble,{
	container:function(){ var type=this.elt.attr('type'); return new S.HEltFCont(this,'input '+(type!='text'?'text ':'')+type); }
	
});
