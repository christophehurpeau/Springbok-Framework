includeCore('jquery/outerHTML');
S.HEltFContble=S.extClass(S.HElt,{
	ctor:function(form,name){this._form=form; this._name=name; this._labelEscape=1; },
	placeholder:function(value){ this.attr('placeholder',value); return this; },
	
	label:function(value){ this._label=value; return this; },
	htmlLabel:function(value){ this._label=value; this._labelEscape=0; return this; },
	noLabel:function(){ this._label=false; return this; },
	noName:function(){ this.removeAttr('name'); return this; },
	
	between:function(content){ this._between=content; return this; },
	
	noContainer:function(){ this._form.append(this.toElt()); return this._form; },
	end:function(){ return this.container().end(); },
	toElt:function(){
		if(this._label===false || (!this._label && !this._form._defaultLabel)) return this.elt;
		var label=this._label|| 'TODO'; //$this->label=$this->form->defaultLabel ? ($this->form->modelName !== null ? _tF($this->form->modelName,$this->name) : $this->name): false;
		/*
		if($this->label!==null) $label=$this->label;
		else{
			if(!$this->form->defaultLabel) return '';
			$label=$this->form->modelName != NULL ? _tF($this->form->modelName,$this->name) : $this->name;
		}
		return $prefix.HHtml::tag('label',array('for'=>$this->attributes['id']),$label,$this->labelEscape).$suffix;
		*/
		label=$('<label/>')[this._labelEscape?'text':'html'](label).attr('for',this.elt.attr('id'));
		//return this.elt.before(label,' ',this._between||'');
		return $(label.outerHTML()+(this._between||' ')+this.elt.outerHTML());
	},
	
	_setAttrValue:function(){
		var value=this._form._getValue(this._name);
		if(value != null) this.elt.val(value);
	},
	_setAttrId:function(){
		this.id(this._form._modelName != null ? this._form._modelName+UString.ucFirst(this._name) : this._name);
	},
	_setAttrName:function(){
		this.attr('name',this._attrName());
	},
	_attrName:function(){
		return this._form._modelName != null ? this._form._name+'['+this._name+']' : this._name;
	}
});
