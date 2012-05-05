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
	$.fn.sAjaxSearch=function(url,minLength,destContent,display){
		var xhr,input=this,lastVal='',currentTimeout;
		display=display||defaultDisplayList;
		this.keyup(function(){
			var val=input.val();
			if(val != '' && val.length >= minLength && val!=lastVal){
				S.history.navigate(url+'/'+val);
				lastVal=val;
				if(xhr){xhr.abort(); xhr=null;}
				if(currentTimeout) clearTimeout(currentTimeout);
				currentTimeout=setTimeout(function(){
					xhr=$.ajax({
						url:url,
						data:{term:val},
						dataType: 'json',
						success:function(data){
							destContent.html(display(data));
						},
						error:function(){
							destContent.html('');
						}
					});
				},160);
			}
		});
		return this;
	};
})(jQuery);
