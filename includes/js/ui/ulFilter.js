includeCoreUtils('UString/normalize');

$.fn.sUlFilter=function(filter){
	if(!filter) this.find('li').removeClass('hidden');
	else{
		var normalizedFilter=UString.normalize(filter);
		this.find('li:not(.notfiltrable)').each(function(i,li){
			li=$(li);
			UString.normalize(li.text()).contains(normalizedFilter) ? li.sShow() : li.sHide();
		});
	}
};
$.fn.sUlFiltrable=function(ul){
	ul=$(ul);
	this.delayedKeyup(function(){ ul.sUlFilter($(this).val()) })
};