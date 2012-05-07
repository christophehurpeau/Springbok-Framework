/* https://github.com/jquery/jquery-ui/blob/master/ui/jquery.ui.autocomplete.js */
(function($){
	var defaultDisplayList=function(data,ulAttrs){
		var li; result=$('<ul/>').attr(ulAttrs);
		$.each(data,function(i,v){
			li=$('<li/>');
			if(typeof(v) ==='string') li.html(v);
			else li.html($('<a/>').attr('href',v.url).text(v.text));
			result.append(li);
		});
	};
	$.fn.sAjaxSearch=function(url,options,destContent,display){
		var xhr,input=this,lastVal='',currentTimeout;
		if(!S.isObject(options)) options={minLength:options==null?3:options};
		else{
			options=S.extendsObj({ navigate:true, minLength:3 },options);
		}
		display=display||defaultDisplayList;
		this.keyup(function(){
			var val=input.val();
			if(val != '' && val.length >= options.minLength && val!=lastVal){
				if(options.navigate) S.history.navigate(url+'/'+val);
				lastVal=val;
				if(xhr){xhr.abort(); xhr=null;}
				if(currentTimeout) clearTimeout(currentTimeout);
				currentTimeout=setTimeout(function(){
					xhr=$.ajax({
						url:url,
						data:{term:val},
						dataType: 'json',
						success:options.success||function(data){
							destContent.html(display(data));
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
	$.fn.sAutocomplete=function(url,options){
		var divResult=$('<div class="divAutocomplete hidden"/>').appendTo($('body'));
		options=S.extendsObj({
			navigate:false,
			success:function(data){
				divResult.html(defaultDisplayList(data,{'class':'clickable'})).sShow();
			},
			error:function(data){
				divResult.html('').sHide();
			}
		},options||{});
		return this.sAjaxSearch(url,options);
	}
})(jQuery);
