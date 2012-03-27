(function($){
	$.fn.ulFilter=function(filter){
		if(!filter) this.find('li').removeClass('hidden');
		else{
			this.find('li').each(function(i,li){
				li=$(li);
				li.text().toLowerCase().indexOf(filter.toLowerCase())===-1 ? li.addClass('hidden') : li.removeClass('hidden');
			});
		}
	}
})(jQuery);