$(document).ready(function(){
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
	
	function displaySpringbokBarPopup(content){
		$('#springbok-bar-popup').fadeOut().find('> pre').text(content).end().show().fadeIn();
	}
	
	var ajaxSpanCount=$('#springbok-bar a[rel=ajax] span');
	$('#springbok-bar-ajax ul').ajaxComplete(function(e,xhr,settings){
		if(!checkedDivFixedPosition) checkDivFixedPosition();
		//console.log(e,xhr,settings);
		ajaxSpanCount.text(new Number(ajaxSpanCount.text())+1);
		$('<li class="clickable"/>').append(settings.type+' ',$('<b/>').text(settings.url),' [&nbsp;'+(settings.async?'async':'sync')+'&nbsp;]',' - ',
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