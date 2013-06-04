(function(){
	var linkoptions={itemprop:'url'},separator=' &raquo; ';
	S.breadcrumbs=function(links){
		var b=$('#breadcrumbs'),span,first=b.children(':first-child'),url;
		b.html(first);
		links && links.forEach(function(l){
			b.append(separator);
			span=$('<span itemscope itemtype="http://data-vocabulary.org/Breadcrumb"/>').appendTo(b);
			if(/*$.type(i)==='number'*/S.isString(l)) $('<span/>').text(l).appendTo(span);
			else{
				span.append($('<a/>').attr(linkoptions).attr('href',l.url).html($('<span itemprop="title"/>').text(l._title)));
			}
		});
		span&&span.addClass('last');
	};
})();
