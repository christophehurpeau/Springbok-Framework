includeCore('libs/jquery-ui-1.9.2.position');
(function(){
	var methods={
		beforeSubmit:function(){
			this.find('input.default').val('');
			return this;
		},
		afterSubmit:function(){
			this.find('input.default').each(function(){ var t=$(this); t.val() ? t.removeClass('default') : t.val(this.title);});
			return this;
		},
		clean:function(){
			this.is('input.default').removeClass('default').val('');
		}
	};
	$.fn.defaultInput = function(method){
		if(!method){
			var inputs,selectorInput='input.default,input[placeholder]',form;
			if(this.is('input')) inputs=this.addClass('default');
			else{
				if(this.is('form')) form=this;
				inputs=this.find(selectorInput);
			}
			
			inputs.each(function(){
				var $this=$(this),placeholder=$this.attr('placeholder');
				if(placeholder) $this.attr('title',placeholder).removeAttr('placeholder').addClass('default');
				$this.val(function(i,v){if(!$this.is(':focus') && (v=='' || v==this.title)) return this.title; $this.removeClass('default');return v;})
					.focusin(function(){if($this.hasClass('default') || $this.val()===this.title) $this.removeClass('default').val('');})
					.focusout(function(e){if(!$this.hasClass('default') && $this.val()=='') $this.addClass('default').val($this.attr('title'));})
					.change(function(e){ if($this.hasClass('default')){ if($this.val()!='') $this.removeClass('default'); else $this.val($this.attr('title')); }
												else if($this.val()==''){ $this.addClass('default').val($this.attr('title')); }});
			});
			(form||inputs.closest('form')).addClass('hasPlaceholders').each(function(){
				$(this).submit(function(){ methods.beforeSubmit.call($(this)) });
			});
			return inputs;
		}else if(methods[method]){
			//return methods[method].apply(this,Array.prototype.slice.call(arguments,1));
			return methods[method].call(this);
		}
	};
	$.fn.reset=function(){
		this.find('input[type=text],input[type=email],input[type=url],input[type=number],input[type=search],input[type=password],input[type=file],input[type=hidden],textarea,select').val('').change();
		return this;
	};
	
	$.fn.ajaxForm=function(url,success,beforeSubmit,error){
		this.each(function(){
			//if(!error) error=function(jqXHR, textStatus){alert('Error: '+textStatus);};
			var form=$(this),submit,imgLoadingSubmit;
			if(S.isFunc(url)){
				error=beforeSubmit;
				beforeSubmit=success;
				success=url;
				url=undefined;
			}
			if(!url) url=form.attr('action');
			form.unbind('submit').submit(function(evt){
				evt.preventDefault();
				evt.stopPropagation();
				submit=form.find('[type="submit"]');
				form.fadeTo(180,0.4);
				if(window.tinyMCE!==undefined) tinyMCE.triggerSave();
				if((beforeSubmit && beforeSubmit()===false) || (form.data('ht5ifv')!==undefined && !form.ht5ifv('valid'))
							|| (S.FormValidator && !S.FormValidator.checkForm(form))){// TODO remove ht5ifv that and force validation
					form.stop().fadeTo(0,1);
					return false;
				}
				var ajaxOptions={
					type:'post',cache:false,
					headers:{'SpringbokAjaxFormSubmit':'1'},
					beforeSend:function(){submit.hide();submit.parent().append(imgLoadingSubmit=S.imgLoading());},
					data:form.serialize(),
					complete:function(){submit.show().blur();form.find('.img.imgLoading').remove();form.fadeTo(150,1)},
					error:error
				};
				if(success) ajaxOptions.success=success;
				$.ajax(url,ajaxOptions)
					.error(function(){S.bodyIcon('cross',form);})
					.success(function(data,textStatus,jqXHR){
						S.bodyIcon('tick',form);
						if(S.isObj(data)){
							if(data.update){
								var u=data.update;
								if(S.isObj(u)){
									UObj.forEach(u,function(key,d){
										UObj.forEach(d,function(k,value){
											form.find('[name="'+ ( key ? key+'['+k+']' : k )+'"]').val(value);
										});
									});
								}
							}else if(data.redirect){
								S.redirect(data.redirect);
							}
						}
						form.trigger('sAjaxFormSubmittedSuccess',[data,jqXHR]);
					});
				return false;
			});
			/*.find(':submit').unbind('click').click(function(){
				//var validator=form.data('validator');
				//if(validator && !validator.checkValidity()) return false;
				//submit=$(this);
				return true;
			});*/
		});
		return this;
	};
	
	$.fn.ajaxChangeForm=function(url,success,beforeSubmit,error){
		if(!error) error=function(jqXHR, textStatus){alert(i18nc['Error:']+' '+textStatus);};
		var form=this,imgLoadingSubmit;
		this.unbind('change').change(function(){
			form.fadeTo(180,0.4);
			if(beforeSubmit) beforeSubmit();
			if(window.tinymce) tinymce.triggerSave();
			if(form.data('ht5ifv')!==undefined && !form.ht5ifv('valid')){
				form.fadeTo(0,1)
				return false;
			}
			var ajaxOptions={
				type:'post',cache:false,
				beforeSend:function(){form.before(imgLoadingSubmit=S.imgLoading().offset({left:form.position().left+form.width()-16}).css({position:'absolute','z-index':5}));},
				data:form.serialize(),
				complete:function(){imgLoadingSubmit.remove();form.fadeTo(150,1)},
				error:error
			};
			if(success) ajaxOptions.success=success;
			$.ajax(url,ajaxOptions);
			return false;
		});
	}
})();


S.HForm=function(modelName,formAttributes,tagContainer,options){
	formAttributes=UObj.extend({action:'',method:'post'},formAttributes);
	this.$=$('<form/>').attr(formAttributes);
	this.modelName=modelName||false;
	this.name=modelName?UString.lcFirst(modelName):false;
	this.tagContainer=tagContainer!==undefined?tagContainer:'div';
};
S.HForm.prototype={
	end:function(submit){
		if(submit || submit==undefined) this.$.append(this.submit(submit))
		return this.$;
	},
	_container:function(res,defaultClass,attributes,labelFor,label,appendLabel){
		if(this.tagContainer && (attributes || attributes===undefined)){
			attributes=UObj.extend({'class':defaultClass},attributes);
			res=$('<'+this.tagContainer+'/>').html(res);
			if(attributes.before){ res.prepend(attributes.before); delete attributes.before; }
			if(attributes.after){ res.append(attributes.after); delete attributes.after; }
			res.attr(attributes);
			if(label) res[appendLabel?'append':'prepend']($('<label/>').attr('for',labelFor).text(label));
		}
		return res;
	},
	_input:function(name,type,label,inputAttributes,containerAttributes){
		inputAttributes=UObj.extend({
			id:(this.modelName ? this.modelName : 'Input')+UString.ucFirst(name)+(inputAttributes&&inputAttributes.idSuffix?inputAttributes.idSuffix:''),
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
		options=UObj.extend({empty:undefined},options);
		inputAttributes=UObj.extend({
			id:(this.modelName ? this.modelName : 'Select')+UString.ucFirst(name)+(inputAttributes&&inputAttributes.idSuffix?inputAttributes.idSuffix:''),
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
		inputAttributes=UObj.extend({
			id:(this.modelName ? this.modelName : 'Textarea')+UString.ucFirst(name)+(inputAttributes&&inputAttributes.idSuffix?inputAttributes.idSuffix:''),
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
		attributes=UObj.extend({
			type:'checkbox',
			id:(this.modelName ? this.modelName : 'Checkbox')+UString.ucFirst(name)+(attributes&&attributes.idSuffix?attributes.idSuffix:''),
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
		attributes=UObj.extend({'class':'submit'},attributes);
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