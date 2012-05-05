(function($){
	$.fn.sAutocomplete=function(url,minLength){
		var divResult=$('<div class="divAutocomplete hidden"/>').appendTo($('body'));
		this.sAjaxSearch(url,minLength,divResult,function(data){
			divResult.html('').sShow();
			return defaultDisplayList(data,{'class':'clickable'});
		});
	}
})(jQuery);