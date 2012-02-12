(function($){
	/* https://github.com/jquerytools/jquerytools/blob/master/src/toolbox/toolbox.expose.js */
	function viewport(){
		if($.browser.msie){
			// if there are no scrollbars then use window.height
			var d = $(document).height(), w = $(window).height();
			return [
				window.innerWidth || 						// ie7+
				document.documentElement.clientWidth || 	// ie6  
				document.body.clientWidth, 					// ie6 quirks mode
				d - w < 20 ? w : d
			];
		}
		return [$(document).width(),$(document).height()]; 
	}
	
	function call(fn){ if(fn) return fn.call($.mask); }
	$$.mask={
		maskId:'exposeMask',
		options:{loadSpeed:'slow',closeSpeed:'fast',closeOnClick:true,closeOnEsc:true,
				zIndex:9998,opacity:0.8,startOpacity:0,color:'#000',onLoad:null,onClose:null},
		mask:false,
		
		load:function(options){
			options=options?$.extend({},this.options,options):this.options;
			
			var mask=$("#"+options.maskId),size=viewport();
			if(mask.length===0){
				mask=$('<div/>').attr("id",options.maskId);
				$("body").append(mask);
			}
			mask.css({
				position:'absolute',
				top:0,left:0,
				width:size[0],height:size[1],
				display:'none',
				opacity:options.startOpacity,
				zIndex:options.zIndex
			});
			if(options.color) mask.css('background',options.color);
		}
	};
})(jQuery);
