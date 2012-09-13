if($('#container').length===0) $('body').css('paddingTop','33px');

var checkedDivFixedPosition=false;
function checkDivFixedPosition(){
	$('#container').addClass('devEnvironnement');
	var divFixed=$('div.fixed');
	if(divFixed.length!==0){
		checkedDivFixedPosition=true;
		if(divFixed.css('position')==='fixed') $('head').append('<style type="text/css">#container #page div.fixed{top:'+(parseInt(divFixed.css('top'))+28)+'px;}</style>')
	}
}
checkDivFixedPosition();
/*
function displaySpringbokBarPopup(content){
	$('#springbok-bar-popup').fadeOut().find('> pre').text(content).end().show().fadeIn();
}*/

var jsConsoleLink=$('#springbok-bar a[rel="js-console"]'),jsConsoleSpanCount=jsConsoleLink.find('span'),
			ajaxLink=$('#springbok-bar a[rel=ajax]'),ajaxSpanCount=ajaxLink.find('span');
$('#springbok-bar-ajax ul').ajaxComplete(function(e,xhr,settings){
	if(!checkedDivFixedPosition) checkDivFixedPosition();
	//console.log(e,xhr,settings);
	ajaxLink.stop(true,true).fadeOut('fast').fadeIn('fast').fadeOut('fast').fadeIn('fast');
	ajaxSpanCount.text(new Number(ajaxSpanCount.text())+1);
	$('<li/>').append(settings.type+' ',$('<b/>').text(settings.url),' [&nbsp;'+(settings.async?'async':'sync')+'&nbsp;]',' - ',
				//$('<a href="#"/>').text('See Request Headers').click(function(){displaySpringbokBarPopup(xhr.responseText);return false;})
			$('<i/>').text(xhr.status+' '+xhr.statusText)
	).click(function(){
		$('#SpringbokBarAjaxContent').html($('<h5 class="noclear"/>').text('Headers')).append(
			$('<pre/>').text(xhr.getAllResponseHeaders()),
			$('<h5 class="noclear mt6"/>').text('Content'),
			$('<pre/>').text(xhr.responseText));
		var $t=$(this); $t.parent().find('> li').removeClass('current');
		$t.addClass('current');
		return false;
	})
	.fadeOut(0).appendTo(this).fadeIn(); //fade isn't really usefull...
});


var oldConsoleVar=window.console,jsConsoleContent=$('#springbok-bar-js-console ul'),
log=function(type,args){
	jsConsoleLink.stop(true,true).fadeOut('fast').fadeIn('fast').fadeOut('fast').fadeIn('fast');

	jsConsoleSpanCount.text(new Number(jsConsoleSpanCount.text())+1);
	jsConsoleContent.append($('<li/>').text(Array.prototype.join.call($.map(args,function(v){return $.toJSON(v);},', '))));
};
window.console={
	assert:function(){},
	clear:function(){},
	count:function(){},
	debug:function(){},
	dir:function(){},
	dirxml:function(){},
	error:function(){},
	group:function(){},
	groupCollapsed:function(){},
	groupEnd:function(){},
	info:function(){},
	log:function(){log('info',arguments);},
	profile:function(){},
	profileEnd:function(){},
	table:function(){},
	time:function(){},
	timeEnd:function(){},
	timeStamp:function(){},
	trace:function(){},
	warn:function(){log('warn',arguments);}
};
oldConsoleVar && !$.browser.msie && $.each(console,function(k,v){
	var f=window.console[k];
	window.console[k]=function(){
		oldConsoleVar && oldConsoleVar[k] && oldConsoleVar[k].apply(oldConsoleVar,arguments);
		f && f.apply(window.console,arguments);
	};
});


$('.springbok-bar-content').hide();
$('#springbok-bar a').each(function(i,a){
	var t=$(a);
	t.click(function(){
		t.toggleClass('current');
		$('#springbok-bar-'+t.attr('rel')).animate({opacity:'toggle',height:'toggle'});
		return false;
	});
});