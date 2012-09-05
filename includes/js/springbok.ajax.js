includeCore('springbok.history');
includeCore('ui/slideTo');
(function($){
	var lastConfirmResult=true,readyCallbacks=$.Callbacks();
	document.confirm=function(param){return lastConfirmResult=window.confirm(param);};
	var divContainer,divPage,divVariable,divContent,linkFavicon,normalFaviconHref,
		changeLinkFavicon=function(href){
			if(normalFaviconHref) linkFavicon.remove().attr('href',href).appendTo('head')
		};
	S.ready(function(){
		//console.log('AJAX DOCUMENT READY');
		divContainer=$('#container');
		divPage=$('#page');
		linkFavicon=$('head link[rel="icon"],head link[rel="shortcut icon"]');
		normalFaviconHref=linkFavicon.length===0 ? false : linkFavicon.attr('href');
		S.ajax.updateVariable(divPage);
		var mustLoad=!S.history.start();
		S.ajax.init();
		if(mustLoad) S.history.loadUrl();
		
		S.ready=function(callback){ readyCallbacks.add(callback); };
	});
	S.redirect=function(url){ S.ajax.load(url); }
	S.setTitle=function(title){ document.title=title; divVariable.children('h1:first').text(title) }
	S.ajax={
		init:function(){
			$(document).on('click',
						//'a[href]:not([href="javascript:;"]):not([href="#"]):not([href^="mailto:"]):not([target]):not([href^="http://"])'
						'a[href]:not([href^="#"]):not([target]):not([href*=":"])'
				,function(evt){
				if($(evt.target).is('a[onclick^="return"]') && !lastConfirmResult) return false;
				evt.preventDefault();
				evt.stopPropagation();
				var a=$(this),rel='content',menu,url=a.attr('href'),confirmMessage=a.data('confirm');
				if(confirmMessage && !confirm(confirmMessage=='1' ? i18nc['Are you sure ?'] : confirmMessage)) return false;
				if(a.is('header nav.ajax a')){
					menu=a.closest('nav');
					if(a.hasClass('current')) S.ajax.load(url);
					else{
						menu.find('a.current').removeClass('current').data('pagecontent',{html:divPage.html(),title:document.title,'class':divPage.attr('class')});
						var newPageContent=a.data('pagecontent');
						if(newPageContent){
							divPage.html(newPageContent.html).attr('class',newPageContent['class']||"");
							S.setTitle(newPageContent.title);
							S.history.navigate(url);
						}else S.ajax.load(url);
					}
					a.addClass('current');
					return false;
				}
				
				var allMenuLinks=$('nav a[href="'+url+'"]');
				menu=allMenuLinks.closest('nav');
				
				if(menu.size !== 0){
					menu.find('a.current').removeClass('current');
					allMenuLinks.addClass('current');
				}
				
				S.ajax.load(url);
				return false;
			});
			$(document).on('click','ul.clickable li[rel]',function(evt){
				var li=$(this),ul=li.closest('ul');
				ul.find('li').removeClass('current');
				li.addClass('current');
				S.ajax.load(li.attr('rel'));
				return false;
			});
			$(document).on('submit','form[action]:not([action="javascript:;"]):not([action="#"]):not([target]):not([enctype]):not([action^="http://"])',function(){
				var form=$(this),isGet=form.attr('method')==='get',action=form.attr('action'),params=form.serialize();
				if(isGet){ action+=(action.sHas('?')?'&':'?')+params; params=undefined; }
				S.ajax.load(action,params,isGet?0:'post',form.has('input[type="password"]'));
				return false;
			});
		},
		
		updateVariable:function(divPage){
			divVariable=divPage.find('div.variable:first');
			divContent=divVariable.is('.content') && divVariable.has('h1').length===0 ? divVariable : divVariable.find('.content:first');
		},
		load:function(url,data,type,forceNotAddDataToGetORdoNotDoTheEffect,replaceUrl){
			if(url.substr(0,1)==='?') url=location.pathname+url;
			var oldCurrentTitle=document.title,ajaxurl=url,headers={},divLoading=$('<div class="globalAjaxLoading"/>').text(i18nc['Loading...']).prepend('<span/>');
			
			if(data && !forceNotAddDataToGetORdoNotDoTheEffect) url+=(url.indexOf('?')==-1?'?':'&')+data;
			
			headers.SpringbokAjaxPage=divPage.length>0?divPage.data('layoutname')||'0':'0';
			headers.SpringbokAjaxContent=divContent.length>0?divContent.data('layoutname'):'';
			if(S.breadcrumbs) headers.SpringbokBreadcrumbs='1';
			
			document.title=i18nc['Loading...'];
			//$('body').fadeTo(0.4);
			$('body').addClass('cursorWait').append(divLoading);
			changeLinkFavicon(imgUrl+'ajax-roller.gif');
			
			$.ajax(ajaxurl,{
				type:type?type:'GET', data:data, headers:headers,
				async:false,
				complete:function(jqXHR){
					document.title=oldCurrentTitle;
					setTimeout(function(){S.history.navigate(url,replaceUrl)},3);
				/*	$('body').removeClass('cursorWait');
					divLoading.remove();
				},
				success:function(data,textStatus,jqXHR){*/
					var div,to,h=jqXHR.getResponseHeader('SpringbokAjaxTitle'),newTitle=h?$.parseJSON(h):'-';
					setTimeout(function(){S.setTitle(newTitle)},20);
					
					if(h=jqXHR.getResponseHeader('SpringbokAppVersion'))
						if(h!=version) if(confirm("L'application a été mise à jour. Souhaitez vous recharger la page ?")){
							$('body').empty();
							setTimeout(function(){window.location.reload();},21);
							return;
						}
					
					if(h=jqXHR.getResponseHeader('SpringbokRedirect')){
						divLoading.remove();
						S.ajax.load(h,false,false,true,true);
						return;
					}
					
					if(h=jqXHR.getResponseHeader('SpringbokAjaxBreadcrumbs')) S.breadcrumbs($.parseJSON(h));
					
					if(!(to=jqXHR.getResponseHeader('SpringbokAjaxTo'))) to='base';
					
					if(to === 'content') div=divContent;
					else if(to === 'page') div=divPage.attr('class',jqXHR.getResponseHeader('SpringbokAjaxPageClass'));
					else if(to === 'base') div=divContainer;
					
					div.find('span.mceEditor').each(function(){
						var ed=tinymce.get(this.id.substr(0,this.id.length-7));
						ed.focus(); ed.remove();
						/* if(tinymce.isGecko) */
					});
					
					$(window).scrollTop(0);
					
					var OnReadyCallbacks=readyCallbacks;
					S.ajax.loadContent(div,jqXHR.responseText,function(){OnReadyCallbacks.fire();$(document).trigger('springbokAjaxPageLoaded',div);},to,data || forceNotAddDataToGetORdoNotDoTheEffect);
					readyCallbacks=$.Callbacks();
					
					changeLinkFavicon(normalFaviconHref);
					
					if(to === 'base') divPage=$('#page'); 
					if(to === 'base' || to === 'page') S.ajax.updateVariable(divPage);
					
					$('body').removeClass('cursorWait');
					divLoading.remove();
				}/*,
				error:function(jqXHR,textStatus,errorThrown){
					$(window).scrollTop(0);
					divContainer.html($('<p/>').attr('class','message error')
						.text(i18nc.Error+(textStatus?' ['+textStatus+']':'')+(errorThrown?' : '+errorThrown:''))).append(jqXHR.responseText);//.fadeTo(150,1);
					divPage=divVariable=divContent=false;
				}*/
			});
		},
		loadContent:function(div,content,OnReadyCallbacks,to,forceNotAddDataToGetORdoNotDoTheEffect){
			defineDefault('AJAX_CONTENT_EFFECT',true);
			if(AJAX_CONTENT_EFFECT && to === 'content' && !forceNotAddDataToGetORdoNotDoTheEffect){
				divContent=div.sSlideTo(content,OnReadyCallbacks);
			}else{
				div.html(content);//.fadeTo(0,1);
				OnReadyCallbacks();
			}
		}
	};
})(jQuery);
