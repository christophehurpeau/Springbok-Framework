includeCore('ui/ajaxSearch');
(function($){
	$.fn.sAutocomplete=function(url,minLength){
		var divResult=$('<div class="divAutocomplete hidden"/>').appendTo($('body'));
		return this.sAjaxSearch(url,minLength,divResult,function(data){
			divResult.html('').sShow();
			return defaultDisplayList(data,{'class':'clickable'});
		});
	}
})(jQuery);