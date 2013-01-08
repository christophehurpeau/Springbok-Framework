$.fn.sUlFilter=function(filter){
	if(!filter) this.find('li').removeClass('hidden');
	else{
		this.find('li:not(.notfiltrable)').each(function(i,li){
			li=$(li);
			li.text().toLowerCase().sbRemoveSpecialChars().indexOf(filter.toLowerCase().sbRemoveSpecialChars())===-1 ? li.sHide() : li.sShow();
		});
	}
};
$.fn.sUlFiltrable=function(ul){
	ul=$(ul);
	this.delayedKeyup(function(){ ul.sUlFilter($(this).val()) })
};