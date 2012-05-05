/* http://www.jankoatwarpspeed.com/post/2010/01/26/dynamic-tabs-jquery.aspx */

(function($){
	var oldFunctionInit=S.ajax.init,oldFunctionSetTitle=S.setTitle,ajaxFunctionUpdateVariable=S.ajax.updateVariable,
		dynamictabsMenu,dynamictabsContent,dynamictabsId=0;
	S.ajax.init=function(){
		S.dynamictabs.init();
		$(document).bind('click',function(e){
			if(e.which==2){
				var target=$(e.target);
				if(target.is('nav.dynamictabs a')){
					S.dynamictabs.delTab(target);
				}else if(target.is('a:not(header a)')){
					var a=target,menu=false;
					S.dynamictabs.addTab();
					if(a.is('nav a')) menu=a.closest('nav');
					
					if(menu){
						menu.find('a.current').removeClass('current');
						a.addClass('current');
					}
					S.ajax.load(a.attr('href'));
				}else return true;
				e.preventDefault();
				return false;
			}
			return true;
		});
		oldFunctionInit();
	};
	S.setTitle=function(title){ oldFunctionSetTitle(title); $('nav.dynamictabs a.current').text(title); };
	S.ajax.updateVariable=function(divPage){ ajaxFunctionUpdateVariable(divPage); S.dynamictabs.prepare(); };
	S.dynamictabs={
		init:function(){
			this.prepare();
			$('nav.dynamictabs > ul > li > a').die('click').live('click', function(){
				var a=$(this);
				// hide
				dynamictabsContent.find('> div').hide();
				dynamictabsMenu.find('a.current').removeClass('current');
				//show
				ajaxFunctionUpdateVariable($('#'+a.attr('rel')).show());
				a.addClass('current');
				return false;
			});
		},
		prepare:function(){
			dynamictabsMenu=$('nav.dynamictabs');
			dynamictabsContent=$(dynamictabsMenu.attr('rel')?dynamictabsMenu.attr('rel'):'#dynamictabsContent');
			if(dynamictabsMenu.length!==1){ dynamictabsMenu=false;return; }
			
			var li=dynamictabsMenu.find('li');
			if(li.length===0){
				var newContent=$('<div/>').attr('id','dynamictab'+(++dynamictabsId));
				dynamictabsContent.html(newContent.html(dynamictabsContent.html()));
				ajaxFunctionUpdateVariable(newContent);
				dynamictabsMenu.find('ul').html($('<li/>').html($('<a/>').text(document.title).attr({'class':'current',rel:'dynamictab'+dynamictabsId,href:'javascript:;'})));
			}else ajaxFunctionUpdateVariable(dynamictabsContent.find('> div:not(:hidden)'));
		},
		addTab:function(){
			dynamictabsContent.find('> div').hide();
			var current=dynamictabsMenu.find('.current').removeClass('current');
			
			var newContent=$('<div/>').attr('id','dynamictab'+(++dynamictabsId));
			dynamictabsContent.append(newContent.html($('#'+current.attr('rel')).html()));
			dynamictabsMenu.find('ul').append($('<li/>').html($('<a/>').text(document.title).attr({'class':'current',rel:'dynamictab'+dynamictabsId,href:'javascript:;'})));
			
			ajaxFunctionUpdateVariable(newContent);
		},
		delTab:function(a){
			$('#'+a.attr('rel')).remove();
					
			if(a.hasClass('current')){
				// find the first tab
				var firsttab=dynamictabsMenu.find('li:first a');
				firsttab.addClass("current");
				S.ajax.updateVariable($('#'+firsttab.attr('rel')).show());
			}
			a.parent().remove();
		}
	};
})(jQuery);
