includeCore('springbok.history');
(function($){
	var lastConfirmResult=true;
	document.confirm=function(param){return lastConfirmResult=window.confirm(param);};
	var divContainer,divPage,divVariable,divContent;
	$$.ready(function(){
		//console.log('AJAX DOCUMENT READY');
		divContainer=$('#container');
		divPage=$('#page');
		$$.ajax.updateVariable(divPage);
		$$.history.start();
		$$.ajax.init();
	});
	$$.redirect=function(url){ $$.ajax.load(url); }
	$$.setTitle=function(title){ document.title=title; divVariable.find('h1:first').text(title) }
	$$.ajax={
		init:function(){
			$(document).on('click','a[href]:not([href="javascript:;"]):not([href="#"]):not([href^="mailto:"]):not([target]):not([href^="http://"])',function(evt){
				if($(evt.target).is('a[onclick^="return"]') && !lastConfirmResult) return false;
				evt.preventDefault();
				evt.stopPropagation();
				var a=$(this),rel='content',menu=false;
				if(a.is('header menu.ajax a')){
					menu=a.closest('menu');
					var url=a.attr('href');
					if(a.hasClass('current')) $$.ajax.load(url);
					else{
						menu.find('a.current').removeClass('current').data('pagecontent',{html:divPage.html(),title:document.title,'class':divPage.attr('class')});
						var newPageContent=a.data('pagecontent');
						if(newPageContent){
							divPage.html(newPageContent.html).attr('class',newPageContent['class']);
							$$.setTitle(newPageContent.title);
							$$.history.navigate(url);
						}else $$.ajax.load(url);
					}
					a.addClass('current');
					return false;
				}else if(a.is('menu a')){
					menu=a.closest('menu');
				}
				
				if(menu){
					menu.find('a.current').removeClass('current');
					a.addClass('current');
				}
				$$.ajax.load(a.attr('href'));
				return false;
			});
			$(document).on('submit','form:not([target])',function(){
				var form=$(this);
				$$.ajax.load(form.attr('action'),form.serialize(),'post');
				return false;
			});
		},
		
		updateVariable:function(divPage){
			divVariable=divPage.find('div.variable:first');
			divContent=divVariable.is('.content') && divVariable.has('h1').length===0 ? divVariable : divVariable.find('.content:first');
		},
		load:function(url,data,type){
			if(url.substr(0,1)==='?') url=location.href+url;
			var ajaxurl=url,divLoading=$('<div class="globalAjaxLoading"/>').text(i18nc['Loading...']).prepend('<span/>'),headers={};
			data=data||{};
			if(type==='post'){
				ajaxurl+=(ajaxurl.indexOf('?')==-1?'?':'&')+'SpringbokAjaxPage='+(divPage.length>0?divPage.data('layoutname'):'')+'&SpringbokAjaxContent='+(divContent.length>0?divContent.data('layoutname'):'');
				if($$.breadcrumbs) ajaxurl+='&breadcrumbs';
				url+=(url.indexOf('?')==-1?'?':'&')+data;
			}else{
				if($$.breadcrumbs){
					data.breadcrumbs='';
				}
				//headers['SpringbokBreadcrumbs']='test';
				data.SpringbokAjaxPage=divPage.length>0?divPage.data('layoutname'):'';
				data.SpringbokAjaxContent=divContent.length>0?divContent.data('layoutname'):'';
			}
			
			document.title=i18nc['Loading...'];
			//$('body').fadeTo(0.4);
			$('body').addClass('cursorWait').append(divLoading);
			
			$$.history.navigate(url);
			
			$.ajax(ajaxurl,{
				type:type?type:'GET', data:data,
				async:false,
				/*beforeSend:function(jqXHR){
					jqXHR.setRequestHeader('SpringbokBreadcrumbs','test');
				},*/
				success:function(data,textStatus,jqXHR){
					var h,div,to;
					
					if(h=jqXHR.getResponseHeader('SpringbokAjaxTitle')) $$.setTitle($.parseJSON(h));
					if(h=jqXHR.getResponseHeader('SpringbokAjaxBreadcrumbs')) $$.breadcrumbs($.parseJSON(h));
					
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
					$('body').removeClass('cursorWait');
					divLoading.remove();
					
					if(to === 'base') divPage=$('#page');
					else if(to==='page') divPage.attr('class',jqXHR.getResponseHeader('SpringbokAjaxPageClass')); // 
					if(to === 'base' || to === 'page') $$.ajax.updateVariable(divPage);
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
