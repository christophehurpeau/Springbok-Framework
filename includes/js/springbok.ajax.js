includeCore('springbok.history');
(function($){
	var lastConfirmResult=true;
	document.confirm=function(param){return lastConfirmResult=window.confirm(param);};
	var divContainer,divPage,divVariable,divContent,linkFavicon,normalFaviconHref;
	S.ready(function(){
		//console.log('AJAX DOCUMENT READY');
		divContainer=$('#container');
		divPage=$('#page');
		linkFavicon=$('head link[rel="icon"],head link[rel="shortcut icon"]');
		normalFaviconHref=linkFavicon.length===0 ? false : linkFavicon.attr('href');
		S.ajax.updateVariable(divPage);
		S.history.start();
		S.ajax.init();
	});
	S.redirect=function(url){ S.ajax.load(url); }
	S.setTitle=function(title){ document.title=title; divVariable.find('h1:first').text(title) }
	S.ajax={
		init:function(){
			$(document).on('click','a[href]:not([href="javascript:;"]):not([href="#"]):not([href^="mailto:"]):not([target]):not([href^="http://"])',function(evt){
				if($(evt.target).is('a[onclick^="return"]') && !lastConfirmResult) return false;
				evt.preventDefault();
				evt.stopPropagation();
				var a=$(this),rel='content',menu,url=a.attr('href');
				if(a.is('header menu.ajax a')){
					menu=a.closest('menu');
					if(a.hasClass('current')) S.ajax.load(url);
					else{
						menu.find('a.current').removeClass('current').data('pagecontent',{html:divPage.html(),title:document.title,'class':divPage.attr('class')});
						var newPageContent=a.data('pagecontent');
						if(newPageContent){
							divPage.html(newPageContent.html).attr('class',newPageContent['class']);
							S.setTitle(newPageContent.title);
							S.history.navigate(url);
						}else S.ajax.load(url);
					}
					a.addClass('current');
					return false;
				}
				
				var allMenuLinks=$('menu a[href="'+url+'"]');
				menu=allMenuLinks.closest('menu');
				
				if(menu.size !== 0){
					menu.find('a.current').removeClass('current');
					allMenuLinks.addClass('current');
				}
				
				S.ajax.load(url);
				return false;
			});
			$(document).on('submit','form[action]:not([action="javascript:;"]):not([action="#"]):not([target]):not([action^="http://"])',function(){
				var form=$(this);
				S.ajax.load(form.attr('action'),form.serialize(),form.attr('method')|'post');
				return false;
			});
		},
		
		updateVariable:function(divPage){
			divVariable=divPage.find('div.variable:first');
			divContent=divVariable.is('.content') && divVariable.has('h1').length===0 ? divVariable : divVariable.find('.content:first');
		},
		load:function(url,data,type){
			if(url.substr(0,1)==='?') url=location.pathname+url;
			var ajaxurl=url,headers={},divLoading=$('<div class="globalAjaxLoading"/>').text(i18nc['Loading...']).prepend('<span/>');
			
			if(data) url+=(url.indexOf('?')==-1?'?':'&')+data;
			
			headers.SpringbokAjaxPage=divPage.length>0?divPage.data('layoutname')||'0':'0';
			headers.SpringbokAjaxContent=divContent.length>0?divContent.data('layoutname'):'';
			if(S.breadcrumbs) headers.SpringbokBreadcrumbs='1';
			
			document.title=i18nc['Loading...'];
			//$('body').fadeTo(0.4);
			$('body').addClass('cursorWait').append(divLoading);
			if(normalFaviconHref) linkFavicon.attr('href',webdir+'img/ajax-roller.gif');
			
			S.history.navigate(url);
			
			$.ajax(ajaxurl,{
				type:type?type:'GET', data:data, headers:headers,
				async:false,
				complete:function(){
					$('body').removeClass('cursorWait');
					divLoading.remove();
				},
				success:function(data,textStatus,jqXHR){
					var h,div,to;
					
					if(h=jqXHR.getResponseHeader('SpringbokRedirect')){
						S.ajax.load(h);
						return;
					}
					
					if(h=jqXHR.getResponseHeader('SpringbokAjaxTitle')) S.setTitle($.parseJSON(h));
					if(h=jqXHR.getResponseHeader('SpringbokAjaxBreadcrumbs')) S.breadcrumbs($.parseJSON(h));
					
					if(!(to=jqXHR.getResponseHeader('SpringbokAjaxTo'))) to='base';
					
					if(to === 'content') div=divContent;
					else if(to === 'page') div=divPage;
					else if(to === 'base') div=divContainer;
					
					div.find('span.mceEditor').each(function(){
						//tinyMCE.execCommand('mceRemoveControl',false,this.id.substr(0,this.id.length-7))
						tinyMCE.remove(this.id.substr(0,this.id.length-7));
					});
					div.html(data);//.fadeTo(0,1);
					$(window).scrollTop(0);
					if(normalFaviconHref) linkFavicon.attr('href',normalFaviconHref);
					
					if(to === 'base') divPage=$('#page');
					else if(to==='page') divPage.attr('class',jqXHR.getResponseHeader('SpringbokAjaxPageClass')); // 
					if(to === 'base' || to === 'page') S.ajax.updateVariable(divPage);
				},
				error:function(jqXHR,textStatus,errorThrown){
					$(window).scrollTop(0);
					divContainer.html($('<p/>').attr('class','message error')
						.text(i18nc.Error+(textStatus?' ['+textStatus+']':'')+(errorThrown?' : '+errorThrown:''))).append(jqXHR.responseText);//.fadeTo(150,1);
					divPage=divVariable=divContent=false;
				}
			});
		}
	};
})(jQuery);
