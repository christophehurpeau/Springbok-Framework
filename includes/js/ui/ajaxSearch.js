includeCore('ui/base');
/* https://github.com/jquery/jquery-ui/blob/master/ui/jquery.ui.autocomplete.js */
(function($){
	var defaultDisplayList=function(data,ulAttrs,callback){
		var li,result=$('<ul/>').attr(ulAttrs);
		$.each(data,function(i,v){
			li=$('<li/>');
			if(S.isString(v)) li.html(v);
			else{
				li.html(callback?callback(v,i):$('<a/>').attr('href',v.url).text(v.text));
			}
			result.append(li);
		});
		return result;
	};
	$.fn.sAjaxSearch=function(url,options,destContent,display){
		var xhr,input=this,lastVal='',currentTimeout,
			abort=function(){};
		if(!S.isObject(options)) options={minLength:options==null?3:options};
		else{
			options=S.extendsObj({ navigate:true, minLength:3 },options);
		}
		display=display||defaultDisplayList;
		$(window).on('beforeunload',function(){
			
		});
		this.attr('autocomplete','off')
			// turning off autocomplete prevents the browser from remembering the
			// value when navigating through history, so we re-enable autocomplete
			// if the page is unloaded before the widget is destroyed. #7790
			/*._bind( this.window, {
				beforeunload: function() {
					this.element.removeAttr( "autocomplete" );
				}
			});*/
			.keyup(function(e){
				var eKeyCode=e.keyCode;
				if(
					(eKeyCode>=keyCodes.SHIFT && eKeyCode<=keyCodes.CAPS_LOCK)
					|| (eKeyCode>=keyCodes.PAGE_UP && eKeyCode<=keyCodes.HOME)
				) return;
				var val=input.val();
				if(options.keyup && options.keyup(val,eKeyCode)===false) return;
				
				if(val != '' && val.length >= options.minLength && val!=lastVal){
					if(options.navigate) S.history.navigate(url+'/'+val);
					lastVal=val;
					if(xhr){xhr.abort(); xhr=null;}
					if(currentTimeout) clearTimeout(currentTimeout);
					currentTimeout=setTimeout(function(){
						if(input.is(':disabled')||input.prop('readonly')) return;
						xhr=$.ajax({
							url:url,
							data:{term:val},
							dataType: 'json',
							success:function(data){
								options.success?options.success.call(destContent,data):destContent.html(display(data));
							},
							error:options.error||function(){
								destContent.html('');
							}
						});
					},160);
				}
			});
		return this;
	};
	$.fn.sAutocomplete=function(url,options,displayResult){
		var divResult=$('<div class="divAutocomplete hidden"/>').appendTo($('#page'));
		if($.isFunction(options)){
			displayResult=options;
			options={};
		}
		options=S.extendsObj({
			navigate:false,
			keyup:function(val,eKeyCode){
				if(eKeyCode===keyCodes.ESCAPE){
					divResult.sHide();
					return false;
				}
				
			},
			success:function(data){
				divResult.html(defaultDisplayList(data,{'class':'clickable'},displayResult)).sShow();
			},
			error:function(data){
				divResult.html('').sHide();
			}
		},options||{});
		return this.sAjaxSearch(url,options,divResult).focus(function(){
			if(!divResult.is(':empty,:visible')) divResult.sShow();
		}).blur(function(){
			divResult.sHide();
		});
	}
})(jQuery);
