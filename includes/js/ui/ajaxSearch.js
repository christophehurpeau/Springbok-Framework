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
				li[escape===false?'html':'text'](callback ? callback(v,i): v.url ? $('<a/>').attr('href',v.url).text(v[key]) : v[key]).data('item',v);
			}
			result.append(li);
		});
		return result;
	};
	$.fn.sAjaxSearch=function(url,options,destContent,display){
		var xhr,input=this,lastVal='',currentTimeout,
			abort=function(){};
		if(!S.isObject(options)) options={minLength:options==null?3:options};
		options=S.extendsObj({ navigate:true, minLength:3, dataType:'json',delay:180 },options);
		display=display||defaultDisplayList;
		$(window).on('beforeunload',function(){
			
		});
		
		var inputIsNotEditable=function(){return input.is(':disabled')||input.prop('readonly')},
			onSuccess=options.success? function(data,oKey){ options.success.call(destContent,data,oKey||options.display||options.oKey) }
										 : function(data,oKey){ destContent.html(display(data,undefined,oKey||options.display||options.oKey,options.escape)) },
			onChange;
		if(S.isFunc(url)) onChange=url;
		else if(S.isArray(url)) onChange=function(term,onSuccess){
			var matcher = new RegExp( RegExp.sEscape(term), "i" );
			var data=url.filter(function(v){ return matcher.test(v) });
			if(data) onSuccess(data);
		}
		else if(S.isObject(url)){
			var list=url.list;
			if(S.isObject(list)){
				list=[];
				S.oForEach(url.list,function(k,v){ list.push(v) });
			}
			
			onChange=function(term,onSuccess){
				var matcher = new RegExp( RegExp.sEscape(term), "i" );
				var data=list.filter(function(v){ return matcher.test(v[url.key]) });
				if(data) onSuccess(data,url.key);
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
					success:onSuccess,
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
			.keyup(function(e){
				var eKeyCode=e.keyCode;
				if(
					(eKeyCode>=keyCodes.SHIFT && eKeyCode<=keyCodes.CAPS_LOCK)
					|| (eKeyCode>=keyCodes.PAGE_UP && eKeyCode<=keyCodes.HOME)
				) return;
				var val=input.val();
				if(options.keyup && options.keyup(val,eKeyCode)===false) return;
				
				input.trigger('sSearch',[val])
			}).bind('sSearch',function(e,val){
				if(inputIsNotEditable()) return;
				if(val===undefined) val=input.val();
				val=val.trim();
				if(options.navigate) S.history.navigate(url+'/'+val);
				if(val == '' || val.length < options.minLength) options.reset ? options.reset() : destContent.empty();
				else if(val!=lastVal){
					lastVal=val;
					onChange(val,onSuccess);
				}
			});
		return this;
	};
	
	S.ui.Autocomplete=function(input,url,options,displayResult){
		if($.isFunction(options)){
			displayResult=options;
			options={};
		}
		var divResult=this.el=$('<div class="divAutocomplete widget hidden"/>').appendTo($('#page')),
			showDivResult=function(){
				divResult.css('width',input.width()).sShow()
					.position({my:"left top",at:"left bottom",of:input,collision:"none"});
			};
		divResult.on('click','li',options.select ? function(){ options.select.call(this,input); divResult.empty().sHide(); }
							 : function(){ input.val($(this).text()); divResult.empty().sHide(); });
		options=S.extendsObj({
			navigate:false,
			keyup:function(val,eKeyCode){
				if(eKeyCode===keyCodes.ESCAPE){
					divResult.sHide();
					return false;
				}
			},
			success:function(data,oKey){
				divResult.html(defaultDisplayList(data,{'class':'clickable spaced'},displayResult||oKey,options.escape));
				showDivResult();
			},
			error:function(data){
				divResult.empty().sHide();
			}
		},options||{});
		var hasFocus=false;
		input
			.data('sAutocomplete',this)
			.bind('dispose',function(){ divResult.remove(); })
			.sAjaxSearch(url,options,divResult).focus(function(){
				hasFocus=true;
				if(!divResult.is(':empty,:visible')) showDivResult();
			}).blur(function(){
				hasFocus=false;
				setTimeout(function(){
					if(!hasFocus) divResult.sHide();
				},200);
			});
	};
	S.extendsClass(S.ui.Autocomplete,S.Widget);
	
	$.fn.sAutocomplete=function(url,options,displayResult){ return new S.ui.Autocomplete(this,url,options,displayResult); };
	if(includedCore('helpers/HEltFInput')) S.HEltFInput.prototype.autocomplete=function(url,options,displayResult){
		new S.ui.Autocomplete(this.elt,url,options,displayResult);
		return this;
	}
})();
