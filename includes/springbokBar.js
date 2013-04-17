if($('#container').length===0 && $('#page').css('position')!=='fixed') $('body').css('paddingTop','33px');

var checkedDivFixedPosition=false,checkedDivPagePosition=false;
function checkDivFixedPosition(){
	$('#container').addClass('devEnvironnement');
	var divFixed=$('div.fixed'),divPage=$('#page'),header=$('header');
	if(divPage.length!==0 && checkedDivPagePosition===false){
		checkedDivPagePosition=true;
		if(divPage.css('position')==='absolute') $('head').append('<style type="text/css">html body #container{margin-top:0 !important;}#container #page{top:'+(parseInt(divPage.css('top'))+28)+'px;}'
				+'@media (min-width:1200px){ #container #page{top:'+divPage.css('top')+'} }</style>')
	}
	if(divFixed.length!==0 && checkedDivFixedPosition===false){
		checkedDivFixedPosition=true;
		if(divFixed.css('position')==='fixed')
			$('head').append('<style type="text/css">#container #page div.fixed{top:'+(parseInt(divFixed.css('top'))+28)+'px;}'
				+'@media (min-width:1200px){ #container #page div.fixed{top:'+divFixed.css('top')+'}  #container #page div.fixed.right{ right:200px }}</style>')
	}
}
checkDivFixedPosition();
/*
function displaySpringbokBarPopup(content){
	$('#springbok-bar-popup').fadeOut().find('> pre').text(content).end().show().fadeIn();
}*/

var jsConsoleLink=$('#springbok-bar a[rel="js-console"]'),jsConsoleSpanCount=jsConsoleLink.find('span'),
			ajaxLink=$('#springbok-bar a[rel=ajax]'),ajaxSpanCount=ajaxLink.find('span');
$(document).ajaxComplete(function(e,xhr,settings){
	if(!checkedDivFixedPosition || !checkedDivPagePosition) checkDivFixedPosition();
	//console.log(e,xhr,settings);
	ajaxLink.stop(true,true).fadeOut(99).fadeIn(99).fadeOut(99).fadeIn(99);
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
	.fadeOut(0).appendTo($('#springbok-bar-ajax ul')).fadeIn(); //fade isn't really usefull...
});


var oldConsoleVar=window.console,jsConsoleContent=$('#springbok-bar-js-console ul'),
log=function(type,args){
	jsConsoleLink.addClass(type).stop(true,true).fadeOut(99).fadeIn(99).fadeOut(99).fadeIn(99);

	jsConsoleSpanCount.text(new Number(jsConsoleSpanCount.text())+1);
	jsConsoleContent.append($('<li/>').addClass(type)
			.text(Array.prototype.join.call($.map(args,function(v){try{ return $.toJSON(v); }catch(err){ return err } },', '))));
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
	log:function(){ log('info',arguments);},
	profile:function(){},
	profileEnd:function(){},
	table:function(){},
	time:function(){},
	timeEnd:function(){},
	timeStamp:function(){},
	trace:function(){},
	warn:function(){ log('warn',arguments); }
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
		$('#springbok-bar-'+t.attr('rel')).animate({opacity:'toggle',height:'toggle'},200);
		return false;
	});
});