S.popup={
	openCentered:function(url,width,height,id){
		var screenX = window.screenX!=undefined?window.screenX:window.screenLeft, screenY = window.screenY!=undefined?window.screenY:window.screenTop,
				 outerWidth =  window.outerWidth||document.body.clientWidth,
				 outerHeight = window.outerHeight||(document.body.clientHeight - 22),
				 left = parseInt(screenX + ((outerWidth - width) / 2), 10),
				 top = parseInt(screenY + ((outerHeight - height) / 2.5), 10),
				 w=window.open(S.html.url(url),'Oauth_Google','width='+width+',height='+height+',left='+left+',top='+top+',titlebar=no,location=no,dependent=yes,chrome=yes')
		if(window.focus) w.focus();
		return w;
	}
};
