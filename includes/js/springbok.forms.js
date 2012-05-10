(function($){
	var methods={
		beforeSubmit:function(){
			this.find('input.default').removeClass('default').val('');
			return this;
		},
		afterSubmit:function(){
			this.find('input.default').each(function(){this.val(this.title);});
			return this;
		}
	};
	$.fn.defaultInput = function(method){
		if(!method){
			var inputs;
			if(this.is('input')){
				inputs=this.addClass('default');
			}else inputs=this.find('input.default');
			
			inputs.each(function(){
				var $this=$(this);
				$this.val(function(i,v){if(!$this.is(':focus') && (v=='' || v==this.title)) return this.title; $this.removeClass('default');return v;})
				.focusin(function(){if($this.hasClass('default') || $this.val()===this.title) $this.removeClass('default').val('');})
				.focusout(function(e){if(!$this.hasClass('default') && $this.val()=='') $this.addClass('default').val($this.attr('title'));})
				.change(function(e){if($this.hasClass('default')) $this.val()!='' ? $this.removeClass('default') : $this.val($this.attr('title'))});
			});
			return inputs;
		}else if(methods[method]){
			//return methods[method].apply(this,Array.prototype.slice.call(arguments,1));
			return methods[method].apply(this);
		}
	};
	$.fn.reset=function(){
		this.find('input[type=text],input[type=email],input[type=url],input[type=number],input[type=search],input[type=password],textarea,select').val('');
		return this;
	};
	
	var num=0;
	$.fn.ajaxForm=function(url,success,beforeSubmit,error){
		if(!error) error=function(jqXHR, textStatus){alert('Error: '+textStatus);};
		var form=this,submit;
		this.unbind('submit').submit(function(evt){
			evt.preventDefault();
			evt.stopPropagation();
			submit=form.find(':submit');
			form.fadeTo(180,0.4);
			if(window.tinyMCE!==undefined) tinyMCE.triggerSave();
			if((beforeSubmit && beforeSubmit()===false) || (form.data('ht5ifv')!==undefined && !form.ht5ifv('valid'))){
				form.stop().fadeTo(0,1)
				return false;
			}
			var currentNum=num++,
			 ajaxOptions={
				type:'post',cache:false,
				beforeSend:function(){submit.hide();submit.parent().append($('<span/>').attr({id:'imgLoadingSubmit'+currentNum,'class':"img imgLoading"}));},
				data:form.serialize(),
				complete:function(){submit.show().blur();$('#imgLoadingSubmit'+currentNum).remove();form.fadeTo(150,1)},
				error:error
			};
			if(success) ajaxOptions.success=success;
			$.ajax(url,ajaxOptions);
			return false;
		})/*.find(':submit').unbind('click').click(function(){
			//var validator=form.data('validator');
			//if(validator && !validator.checkValidity()) return false;
			//submit=$(this);
			return true;
		});*/
		return this;
	};
	
	$.fn.ajaxChangeForm=function(url,success,beforeSubmit,error){
		if(!error) error=function(jqXHR, textStatus){alert(i18nc['Error:']+' '+textStatus);};
		var form=this;
		this.unbind('change').change(function(){
			form.fadeTo(180,0.4);
			if(beforeSubmit) beforeSubmit();
			if(window.tinyMCE!==undefined) tinyMCE.triggerSave();
			if(form.data('ht5ifv')!==undefined && !form.ht5ifv('valid')){
				form.fadeTo(0,1)
				return false;
			}
			var currentNum=num++,
			 ajaxOptions={
				type:'post',cache:false,
				beforeSend:function(){form.before($('<span/>').attr({id:'imgLoadingForm'+currentNum,'class':"img imgLoading"}).offset({left:form.position().left+form.width()-16}).css({position:'absolute','z-index':5}));},
				data:form.serialize(),
				complete:function(){$('#imgLoadingForm'+currentNum).remove();form.fadeTo(150,1)},
				error:error
			};
			if(success) ajaxOptions.success=success;
			$.ajax(url,ajaxOptions);
			return false;
		});
	}
})(jQuery);


S.HForm=function(modelName,formAttributes,tagContainer,options){
	formAttributes=S.extendsObj({action:'',method:'post'},formAttributes);
	this.$=$('<form/>').attr(formAttributes);
	this.modelName=modelName||false;
	this.name=modelName?modelName.sbLcFirst():false;
	this.tagContainer=tagContainer!==undefined?tagContainer:'div';
};
S.HForm.prototype={
	end:function(submit){
		if(submit || submit==undefined) this.$.append(this.submit(submit))
		return this.$;
	},
	_container:function(res,defaultClass,attributes,labelFor,label,appendLabel){
		if(this.tagContainer && (attributes || attributes===undefined)){
			attributes=S.extendsObj({'class':defaultClass},attributes);
			res=$('<'+this.tagContainer+'/>').html(res);
			if(attributes.before){ res.prepend(attributes.before); delete attributes.before; }
			if(attributes.after){ res.append(attributes.after); delete attributes.after; }
			res.attr(attributes);
			if(label) res[appendLabel?'append':'prepend']($('<label/>').attr('for',labelFor).text(label));
		}
		return res;
	},
	_input:function(name,type,label,inputAttributes,containerAttributes){
		inputAttributes=S.extendsObj({
			id:(this.modelName ? this.modelName : 'Input')+name.sbUcFirst()+(inputAttributes&&inputAttributes.idSuffix?inputAttributes.idSuffix:''),
			name:(this.name ? this.name+'['+name+']' : name)
		},inputAttributes);
		delete inputAttributes.idSuffix;
	
		var res=$('<input type="'+type+'"/>');
		
		if(inputAttributes.before){ res.before(inputAttributes.before); delete inputAttributes.before; }
		if(inputAttributes.after){ res.after(inputAttributes.after); delete inputAttributes.after; }
		
		res.attr(inputAttributes);
		
		return this._container(res,'input '+(type!=='text'?'text ':'')+type,containerAttributes,inputAttributes.id,label);
	},
	inputText:function(name,label,inputAttributes,containerAttributes){
		return this._input(name,'text',label,inputAttributes,containerAttributes);
	},
	appendInputText:function(name,label,inputAttributes,containerAttributes){
		this.$.append(this.inputText(name,label,inputAttributes,containerAttributes));
		return this;
	},
	inputPassword:function(name,label,inputAttributes,containerAttributes){
		return this._input(name,'password',label,inputAttributes,containerAttributes);
	},
	appendInputPassword:function(name,label,inputAttributes,containerAttributes){
		this.$.append(this.inputPassword(name,label,inputAttributes,containerAttributes));
		return this;
	},
	inputNumber:function(name,label,inputAttributes,containerAttributes){
		return this._input(name,'number',label,inputAttributes,containerAttributes);
	},
	appendInputNumber:function(name,label,inputAttributes,containerAttributes){
		this.$.append(this.inputNumber(name,label,inputAttributes,containerAttributes));
		return this;
	},
	inputUrl:function(name,label,inputAttributes,containerAttributes){
		return this._input(name,'url',label,inputAttributes,containerAttributes);
	},
	appendInputUrl:function(name,label,inputAttributes,containerAttributes){
		this.$.append(this.inputUrl(name,label,inputAttributes,containerAttributes));
		return this;
	},
	inputEmail:function(name,label,inputAttributes,containerAttributes){
		return this._input(name,'email',label,inputAttributes,containerAttributes);
	},
	appendInputEmail:function(name,label,inputAttributes,containerAttributes){
		this.$.append(this.inputEmail(name,label,inputAttributes,containerAttributes));
		return this;
	},


	select:function(name,list,options,inputAttributes,containerAttributes){
		options=S.extendsObj({empty:undefined},options);
		inputAttributes=S.extendsObj({
			id:(this.modelName ? this.modelName : 'Select')+name.sbUcFirst()+(inputAttributes&&inputAttributes.idSuffix?inputAttributes.idSuffix:''),
			name:(this.name ? this.name+'['+name+']' : name)
		},inputAttributes);
		delete inputAttributes.idSuffix;
	
		var select=$('<select/>').attr(inputAttributes),t=this;
		if(options.empty != undefined)
			select.append($('<option value=""/>').text(options.empty));
		
		$.each(list,function(k,v){
			if(v) select.append(t.option(k,v,options.selected));
		});
		
		return this._container(select,'input select',containerAttributes,inputAttributes.id,options.label);
	},
	appendSelect:function(name,list,options,inputAttributes,containerAttributes){
		this.$.append(this.select(name,list,options,inputAttributes,containerAttributes));
		return this;
	},

	option:function(value,name,selected){
		return $('<option'+(selected===undefined?'':(selected==value?' selected="selected"':''))+'/>').attr('value',value).text(name);
	},


	selectHour:function(name,options,inputAttributes,containerAttributes){
		return this.select(name,['0',1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23],options,inputAttributes,containerAttributes);
	},
	appendSelectHour:function(name,options,inputAttributes,containerAttributes){
		this.$.append(this.selectHour(name,options,inputAttributes,containerAttributes));
		return this;
	},

	selectHourMorning:function(name,options,inputAttributes,containerAttributes){
		return this.select(name,['0',1,2,3,4,5,6,7,8,9,10,11,12],options,inputAttributes,containerAttributes);
	},
	appendSelectHourMorning:function(name,options,inputAttributes,containerAttributes){
		this.$.append(this.selectHourMorning(name,options,inputAttributes,containerAttributes));
		return this;
	},

	selectHourAfternoon:function(name,options,inputAttributes,containerAttributes){
		return this.select(name,{12:12,13:13,14:14,15:15,16:16,17:17,18:18,19:19,20:20,21:21,22:22,23:23},options,inputAttributes,containerAttributes);
	},
	appendSelectHourAfternoon:function(name,options,inputAttributes,containerAttributes){
		this.$.append(this.selectHourAfternoon(name,options,inputAttributes,containerAttributes));
		return this;
	},
	
	
	textarea:function(name,label,inputAttributes,containerAttributes){
		inputAttributes=S.extendsObj({
			id:(this.modelName ? this.modelName : 'Textarea')+name.sbUcFirst()+(inputAttributes&&inputAttributes.idSuffix?inputAttributes.idSuffix:''),
			name:(this.name ? this.name+'['+name+']' : name)
		},inputAttributes);
		delete inputAttributes.idSuffix;
		
		var res=$('<textarea/>');
		if(inputAttributes.value){
			res.text(inputAttributes.value);
			delete inputAttributes.value;
		}
		res.attr(inputAttributes);
		
		return this._container(res,'input textarea',containerAttributes,inputAttributes.id,label);
	},
	appendTextarea:function(name,options,inputAttributes,containerAttributes){
		this.$.append(this.textarea(name,options,inputAttributes,containerAttributes));
		return this;
	},


	checkbox:function(name,label,attributes,containerAttributes){
		attributes=S.extendsObj({
			type:'checkbox',
			id:(this.modelName ? this.modelName : 'Checkbox')+name.sbUcFirst()+(attributes&&attributes.idSuffix?attributes.idSuffix:''),
			name:(this.name ? this.name+'['+name+']' : name)
		},attributes);
		delete attributes.idSuffix;
		attributes.checked ? attributes.checked='checked' : delete attributes.checked;
		
		var res=$('<input/>').attr(attributes);
		return this._container(res,'input checkbox',containerAttributes,attributes.id,label,true);
	},
	appendCheckbox:function(name,options,inputAttributes,containerAttributes){
		this.$.append(this.checkbox(name,options,inputAttributes,containerAttributes));
		return this;
	},
	
	
	submit:function(title,attributes,containerAttributes){
		if(title===undefined) title=i18nc.Save;
		attributes=S.extendsObj({'class':'submit'},attributes);
		var str=$('<input type="submit"/>').attr('value',title).attr(attributes);
		if(this.tagContainer !== 'div' || containerAttributes!==undefined)
			str=$('<'+this.tagContainer+' class="submit"/>').attr(containerAttributes||{}).html(str); 
		return str;
	},
	appendSubmit:function(title,attributes,containerAttributes){
		this.$.append(this.submit(title,attributes,containerAttributes));
		return this;
	}
};