includeCore('ui/base');
includeCore('libs/jquery-ui-1.9.2.position');
/* https://github.com/jquery/jquery-ui/blob/master/ui/jquery.ui.autocomplete.js */
(function(){
	var defaultDisplayList=function(data,ulAttrs,callback,escape){
		var li,result=$('<ul>').attr(ulAttrs),key='text';
		if( callback && S.isString(callback) ){
			key=callback;
			callback=undefined;
		}
		$.each(data,function(i,v){
			li=$('<li/>');
			if(S.isString(v)) li.html(v);
			else{
				/*#if DEV*/if(!callback && !v[key]) console.warn('[ui/ajaxSearch:displayList]','text is empty',v,key);/*#/if*/
				li[escape===false?'html':'text'](callback ? callback(v,i): v.url ? $('<a/>').attr('href',v.url).text(v[key]) : v[key]).data('item',v);
			}
			result.append(li);
		});
		return result;
	};
	$.fn.sAjaxSearch=function(url,options,destContent,display){
		var xhr,input=this,lastVal='',currentTimeout,
			abort=function(){};
		if(!S.isObj(options)) options={minLength:options==null?3:options};
		options=UObj.extend({ navigate:true, minLength:3, dataType:'json',delay:180 },options);
		display=display||defaultDisplayList;
		/*$(window).on('beforeunload',function(){
			
		});*/
		
		var inputIsNotEditable=function(){return input.is(':disabled')||input.prop('readonly')},
			onSuccess=options.success? function(data,oKey){ options.success.call(destContent,data,oKey||options.display||options.oKey) }
										 : function(data,oKey){ destContent.html(display(data,undefined,oKey||options.display||options.oKey,options.escape)) },
			onChange;
		if(S.isFunc(url)) onChange=url;
		else if(S.isArray(url) || S.isObj(url)){
			var list=url,filter,oKey,listValues;
			
			filter=function(matcher){ return list.filter(function(v){ return matcher.test(v) }); };
			
			if(S.isObj(url)){
				list=url.list;
				if(S.isObj(list)){
					oKey=url.key;
					list=[]; listValues=[];
					UObj.forEach(url.list,function(k,v){ list.push(v); listValues.push(UString.normalize(v[url.key])) });
					filter=function(matcher){ return list.filter(function(v,k){ return matcher.test(listValues[k]) }); };
				}
			}
			
			if(listValues===undefined) listValues=list.map(UString.normalize);
			
			onChange=function(term,onSuccess){
				var matcher = new RegExp( UString.normalize(term) ), data=filter(matcher);
				if(data) onSuccess(data,oKey);
			}
		}else onChange=function(val,onSuccess){
			if(xhr){xhr.abort(); xhr=null;}
			if(currentTimeout) clearTimeout(currentTimeout);
			currentTimeout=setTimeout(function(){
				if(inputIsNotEditable()) return;
				xhr=$.ajax({
					url:url,
					data:{term:val},
					dataType:options.dataType,
					success:function(data){onSuccess(data)},
					error:options.error||options.reset||function(){
						destContent.empty();
					}
				});
			},options.delay);
		};
	
		
		this.attr('autocomplete','off')
			// turning off autocomplete prevents the browser from remembering the
			// value when navigating through history, so we re-enable autocomplete
			// if the page is unloaded before the widget is destroyed. #7790
			/*._bind( this.window, {
				beforeunload: function() {
					this.element.removeAttr( "autocomplete" );
				}
			});*/
			//.bind('dispose',function(){ })
			.keydown(function(e){
				var eKeyCode=e.keyCode;
				if(
					(eKeyCode>=keyCodes.SHIFT && eKeyCode<=keyCodes.CAPS_LOCK)
					|| (eKeyCode>=keyCodes.PAGE_UP && eKeyCode<=keyCodes.HOME)
				) return;
				if(options.keydown && options.keydown(eKeyCode,input)===false){
					e.stopPropagation(); e.preventDefault(); //usefull for autocomplete
					return false;
				}
			}).keyup(function(e){
				var val=input.val();
				input.trigger('sSearch',[val])
			}).bind('sSearch',function(e,val){
				if(inputIsNotEditable()) return;
				if(val===undefined) val=input.val();
				val=val.trim();
				if(options.navigate) S.history.navigate(url+'/'+val);
				if(!val || val.length < options.minLength) options.reset ? options.reset() : destContent.empty();
				else if(val!=lastVal){
					lastVal=val;
					onChange(val,onSuccess);
				}
			});
		return this;
	};
	
	S.ui.Autocomplete=S.extClass(S.Widget,{
		ctor:function(input,url,options,displayResult){
			if($.isFunction(options)){
				displayResult=options;
				options={};
			}
			var active=false,
				divResult=this.el=$('<div class="divAutocomplete widget hidden"/>').appendTo($('#page')),
				showDivResult=function(){
					active=true;
					return divResult.css('width',input.width()).sShow()
						.position({my:"left top",at:"left bottom",of:input,collision:"none"});
				},hideDivResult=function(){
					active=false;
					return divResult.sHide();
				},divResultFindLi=function(selector){
					return divResult.find('li'+selector);
				};
			divResult.on('click','li',options.select ? function(){ options.select.call(this,input); hideDivResult().empty(); }
								 : function(){ input.val($(this).text()).change(); hideDivResult().empty(); });
			divResult.on('mouseenter','li',function(){
				divResult.find('li.current').removeClass('current');
			});
			options=UObj.extend({
				navigate:false,
				keydown:function(eKeyCode,input){
					if(active){
						switch(eKeyCode){
							case keyCodes.ESCAPE:
								hideDivResult();
								return false;
							case keyCodes.DOWN:
								var current=divResultFindLi('.current');
								if(current.length) current.removeClass('current').next().addClass('current');
								else divResultFindLi(':first').addClass('current');
								return false;
							case keyCodes.UP:
								var current=divResultFindLi('.current');
								if(current.length) current.removeClass('current').prev().addClass('current');
								else divResultFindLi(':last').addClass('current');
								return false;
							case keyCodes.ENTER: case keyCodes.NUMPAD_ENTER:
								divResultFindLi('.current').click();
								return false;
							case keyCodes.PAGE_UP: case keyCodes.HOME:
								divResultFindLi('.current').removeClass('current');
								divResultFindLi(':first').addClass('current');
								return false;
							case keyCodes.PAGE_DOWN: case keyCodes.END:
								divResultFindLi('.current').removeClass('current');
								divResultFindLi(':last').addClass('current');
								return false;
						}
					}else if(eKeyCode==keyCodes.UP){
						showDivResult();
						return false;
					}
				},
				success:function(data,oKey){
					divResult.html(defaultDisplayList(data,{'class':'clickable spaced'},displayResult||oKey,options.escape));
					showDivResult();
				},
				error:function(data){
					hideDivResult().empty();
				}
			},options||{});
			var hasFocus=false;
			input
				.data('sAutocomplete',this)
				.bind('dispose',function(){ divResult.remove(); })
				.sAjaxSearch(url,options,divResult)
				.focus(function(){
					hasFocus=true;
					if(!divResult.is(':empty,:visible')) showDivResult();
				}).blur(function(){
					hasFocus=false;
					setTimeout(function(){
						if(!hasFocus) hideDivResult();
					},200);
				});
		}
	});
	
	$.fn.sAutocomplete=function(url,options,displayResult){ return new S.ui.Autocomplete(this,url,options,displayResult); };
	if(includedCore('helpers/HEltFInput')) S.HEltFInput.prototype.autocomplete=function(url,options,displayResult){
		new S.ui.Autocomplete(this.elt,url,options,displayResult);
		return this;
	}
})();
