$(document).ready(function(){
	var checkedDivFixedPosition=false;
	function checkDivFixedPosition(){
		var divFixed=$('div.fixed');
		if(divFixed.length!==0){
			checkedDivFixedPosition=true;
			if(divFixed.css('position')==='fixed') $('header').append('<style type="text/css">html body #page div.fixed{top:68px;}</style>')
		}
	}
	checkDivFixedPosition();
	
	function displaySpringbokBarPopup(content){
		$('#springbok-bar-popup').fadeOut().find('> pre').text(content).end().show().fadeIn();
	}
	
	var ajaxSpanCount=$('#springbok-bar a[rel=ajax] span');
	$('#springbok-bar-ajax ul').ajaxComplete(function(e,xhr,settings){
		if(!checkedDivFixedPosition) checkDivFixedPosition();
		//console.log(e,xhr,settings);
		ajaxSpanCount.text(new Number(ajaxSpanCount.text())+1);
		$('<li/>').append(settings.type+' ',$('<b/>').text(settings.url),' [ '+(settings.async?'async':'sync')+' ]'//,' - ',
					//$('<a href="#"/>').text('See Request Headers').click(function(){displaySpringbokBarPopup(xhr.responseText);return false;})
					).append('<br/>')
			.append($('<i/>').text(xhr.status+' '+xhr.statusText),' - ',
					$('<a href="#"/>').text('See Response').click(function(){displaySpringbokBarPopup(xhr.responseText);return false;}),
					' - ',
					$('<a href="#"/>').text('See Response Headers').click(function(){displaySpringbokBarPopup(xhr.getAllResponseHeaders());return false;}))
			.fadeOut(0).appendTo(this).fadeIn(); //fade isn't really usefull...
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
});