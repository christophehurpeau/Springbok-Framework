/* https://github.com/gokercebeci/contextmenu/blob/master/contextmenu.js */
includeCore('springbok.base');
(function($){
	// Methods
	var methods= {
		init: function(element, options){
			var $this = this,
				// Bind options
				contextmenu =  $.extend(element, options);
			contextmenu.init(contextmenu);
			contextmenu.bind({
				'contextmenu':function(e){
					e.preventDefault();
					$this.start(contextmenu);
					$('#contextmenu').remove();
					var c = $('<div id="contextmenu">').addClass(contextmenu.style)
						.css({
							position : 'absolute',
							display  : 'none',
							'z-index': '10000'
					}).appendTo($('body'));
					var link,contextMenuLink,contextMenuOptions;
					$.each(contextmenu.menu,function(contextMenuText,contextMenuLink){
						contextMenuLink=contextmenu.menu[contextMenuText];
						if(typeof(contextMenuLink)!='string'){
							contextMenuOptions=contextMenuLink;
							contextMenuLink=contextMenuOptions.link?contextMenuOptions.link:'#';
							if($.isNumeric(contextMenuText) && contextMenuOptions.title) contextMenuText=contextMenuOptions.title;
							if(contextMenuOptions.icon) contextMenuText='<span class="icon '+contextMenuOptions.icon+'"></span>'+contextMenuText;
						}
						link=$('<a/>').attr('href',contextMenuLink);
						if(contextMenuOptions && contextMenuOptions.callbacks) $.each(contextMenuOptions.callbacks,function(name,funct){ link.bind(name,funct); });
						link.html(contextMenuText).appendTo(c);
					});
					
					// Set position
					var ww = $document.width(),wh = $document.height();
					var w = c.outerWidth(1),h = c.outerHeight(1);
					c.css({
						display : 'block',
						top	 : e.pageY > (wh - h) ? wh : e.pageY,
						left	: e.pageX > (ww - w) ? ww : e.pageX
					});
				}
			});
			$document
			.click(function(){
				$this.finish(contextmenu); 
			})
			.keydown(function(e) {
				if ( e.keyCode == 27 ){
					$this.finish(contextmenu); 
				}
			})
			.scroll(function(){ $this.finish(contextmenu); })
			.resize(function(){ $this.finish(contextmenu); });
		},
		start: function(contextmenu){
			contextmenu.start(contextmenu);
			return;
		},
		finish: function(contextmenu){
			contextmenu.finish(contextmenu);
			$('#contextmenu').remove();
			return;
		},
		error: function(contextmenu){
			contextmenu.error(contextmenu);
			return;
		}
	};
	$.fn.contextmenu=function(options) {
		options = $.extend({
			init: function(){},
			start: function(){},
			finish: function(){},
			error: function(){},
			style: '',
			menu: []
		}, options);
		this.each(function(){
			methods.init($(this), options);
		});
	};
})(jQuery);