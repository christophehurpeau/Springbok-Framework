includeCore('base/listenable');

S.Widget=S.extClass(S.Listenable,{
	dispose:function(){
		for(var keys=Object.keys(this),i=0,l=keys.length;i<l;i++)
			delete this[keys[i]];
	}
});
