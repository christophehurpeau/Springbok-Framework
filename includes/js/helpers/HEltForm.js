S.HEltForm=function(method){ var t=this; t._formElt=t.elt=$('<form method="'+method+'"/>'); t._defaultLabel=true; };

S.HEltForm.Post=function(){ return new S.HEltForm('post'); }
S.HEltForm.Get=function(){ return new S.HEltForm('get'); }

S.HEltForm.ForModel=function(modelName,name,value){ return (new S.HEltForm('post')).setModelName(modelName,name,value); }
S.HEltForm.ForModelGET=function(modelName,name,value){ return (new S.HEltForm('get')).setModelName(modelName,name,value); }

S.extendsClass(S.HEltForm,S.HElt,{
	setModelName:function(modelName,name,value){
		if(!name && modelName!=null) name=modelName.sbLcFirst();
		if(modelName!=null) App.require('m/'+modelName);
		this._modelName=modelName; this._name=name; this._value=value;
		return this;
	},
	isContainable:function(){return !!this._tagContainer; },
	noContainer:function(){ this._tagContainer=false; return this; },
	action:function(url,full){ this.attr('action',S.html.url(url,full)); return this; },
	
	end:function(title){
		if(title!==false) this.submit(title).end();
		if(this._fieldsetStarted) this.fieldsetStop();
		return this.elt;
	},
	
	fieldsetStart:function(legend){
		this._fieldsetStarted=1;
		this.elt=$('<fieldset>');
		if(legend) this.elt.append($('<legend>').text(legend));
		return this;
	},
	fieldsetStop:function(){
		this.elt=this._formElt.append(this.elt);
		return this;
	},
	
	text:function(name){
		M[this.modelName].__PROP_DEF[name]['@']['Text'] ? this.textarea(name) : this.input(name);
	},
	
	input:function(name,largeSize){ return new S.HEltFInput(this,name,largeSize||1); },
	textarea:function(name){ return new S.HEltFTextarea(this,name); },
	hidden:function(name,value){ return new S.HEltFInputHidden(this,name,value); },
	submit:function(title){ return new S.HEltFInputSubmit(this,title); },
	checkbox:function(name){ return new S.HEltFInputCheckbox(this,name); },
	select:function(name,list,selected){ return S.HEltFInputSelect(this,name,list,selected); },
	
	_getValue:function(name){ return this._value && this._value[name]; }
});
S.addSetMethods(S.HEltForm,'tagContainer');



$.fn.sSubmit=function(callback,beforeSubmit){
	var form=this,submit,imgLoadingSubmit;
	this.unbind('submit').submit(function(evt){
		evt.preventDefault();
		evt.stopPropagation();
		submit=form.find(':submit');
		form.fadeTo(180,0.4);
		if(window.tinyMCE!==undefined) tinyMCE.triggerSave();
		if((beforeSubmit && beforeSubmit()===false) || (form.data('ht5ifv')!==undefined && !form.ht5ifv('valid')))
			form.stop().fadeTo(0,1);
		else{
			submit.hide();submit.parent().append(imgLoadingSubmit=S.imgLoading());
			callback(form,function(){
				submit.show().blur();imgLoadingSubmit.remove();form.fadeTo(150,1)
			});
		}
		return false;
	});
	return this;
};
