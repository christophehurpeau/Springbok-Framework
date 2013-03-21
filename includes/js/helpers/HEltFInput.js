S.HEltFInput=S.extClass(S.HEltFContble,{
	ctor:function(form,name,largeSize){
		S.HEltFInput.superCtor.call(this,form,name);
		
		if(form._modelName){
			var model=M[form._modelName],fModel=model.Fields[name],v,e;
			if(fModel){
				switch(fModel[0]){
					case 'i': e=$('<input type="number"/>'); break;
					case 's':
						if(name==='pwd' || name==='password') e=$('<input type="password" value=""/>');
						else if(name==='email' || name==='mail') e=$('<input type="email"/>');
						else if(name==='url' || name==='website') e=$('<input type="url"/>');
						break; 
				}
			}
			e===undefined ? this.elt=e=$('<input type="text"/>') : this.elt=e;
			if(fModel){	
				if(fModel[1].minL || fModel[1].req) e.prop('required',true);
				if(v=fModel[1].min) e.attr('min',v);
				if(v=fModel[1].max) e.attr('max',v);
				if(v=fModel[1].maxL){
					e.attr('maxlength',v);
					var size=70;
					if(v < 10) size=11;
					else if(v <= 30) size=25;
					else if(v < 80) size=30;
					else if(v < 120) size=40;
					else if(v < 160) size=50;
					else if(v < 200) size=60;
					
					e.attr('size',size*largeSize);
				}
			}
		}else this.elt=$('<input type="text"/>');
		if(this.elt.attr('type')!=='password') this._setAttrValue();
		this._setAttrId(); this._setAttrName();
	},
	container:function(){ var type=this.elt.attr('type'); return new S.HEltFCont(this,'input '+(type!='text'?'text ':'')+type); },
	wp100:function(){ this.addClass('wp100'); return this; },
	change:function(){ this.elt.change.apply(this.elt,arguments); return this; },
	applyElt:function(name){ this.elt[name].apply(this.elt,UArray.slice1(arguments)); return this; }
});
