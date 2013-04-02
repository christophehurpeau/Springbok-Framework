S.eltClick=function(selector){
	$(selector).off('click').each(function(i,elt){
		elt=$(elt);
		var trTimeout,href=elt.attr('rel');
		if(!href) return;
		elt.on('click',function(){trTimeout=setTimeout(function(){S.redirect(href)},180);});
		elt.find('a[href]:not([href="#"])').off('click').on('click',function(e){ clearTimeout(trTimeout); e.stopPropagation(); e.preventDefault();
				var a=$(this),confirmMessage=a.data('confirm'); if(!confirmMessage || confirm(confirmMessage=='1' ? i18nc['Are you sure ?'] : confirmMessage)) S.redirect(a.attr('href')); });
		elt.find('a[href="#"]').off('click').on('click',function(e){ clearTimeout(trTimeout); e.stopPropagation(); e.preventDefault(); });
	});
};