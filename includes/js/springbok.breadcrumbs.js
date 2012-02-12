(function(){
	var breadcrumbs,linkoptions={itemprop:'url'},separator=' &raquo; ';
	$(document).ready(function(){
		breadcrumbs=$('#breadcrumbs > span:first');
	});
	$$.breadcrumbs=function(links){
		breadcrumbs.html('');
		var b=breadcrumbs,first=true,url;
		$.each(links,function(i,l){
			first ? first=false : b.append(separator);
			b=$('<span itemscope itemtype="http://data-vocabulary.org/Breadcrumb"/>').appendTo(b);
			if(/*$.type(i)==='number'*/$.isNumeric(i)) b=$('<span/>').text(l).appendTo(b);
			else{
				if($.type(l)!=='array') url=l;
				else url=l.url;
				b.append($('<a/>').attr(linkoptions).attr('href',url).html($('<span itemprop="title"/>').text(i)));
			}
		});
	};
})();
