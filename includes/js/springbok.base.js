/*! Springbok */
/*'use strict';*/
/*#if PROD*/
window.onerror=function handleError(message,url,line){
	if(url && !(url.indexOf('chrome://')===0 || url.indexOf('http://127.0.0.1')===0))
		$.get(baseUrl+'site/jsError',{href:window.location.href,jsurl:url,message:message,line:line});
	//alert("An error has occurred!\n"+e);
	//if(console) console.log(e);
	//console.log(arguments);
	/*#if DEV*/
	/*if(console){
		console.log(arguments,S&&S.StackTrace());
	}*/
	/*#/if*/
	//return true;
	return false;
};
/*#/if*/

if(OLD_IE){
	//include Core('es5-compat.src');
}else{
	if(!Object.keys || !String.contains || !(window.Map && window.Map.prototype.forEach)){ // not String.prototype.contains : the generic version
		//$.ajax({ url:webUrl+'js/es5-compat.js', global:false, async:false, cache:true, dataType:'script' });
		Object.keys || $.ajax({ url:webUrl+'js/es5-compat.js', global:false, async:false, cache:true, dataType:'script' });
		/* !String.contains || !(window.Map && window.Map.prototype.forEach) */
		$.ajax({ url:webUrl+'js/es6-compat.js', global:false, async:false, cache:true, dataType:'script' });
	}
}



var global=window,$document=$(document);

includeCoreUtils('index');
includeCoreUtils('UObj');
includeCoreUtils('UArray');
includeCoreUtils('UString/');

UObj.extend(S,{
	t: function(string,args){
		string = i18n[string] || string;
		return args ? UString.vformat(string,args) : string;
	},
	tC: function(string,args){
		string = i18nc[string] || string;
		return args ? UString.vformat(string,args) : string;
	},
	
	ready:function(callback){ $document.ready(callback); },
	redirect:function(url){ url && (window.location=url); },
	setTitle:function(title){document.title=title;},
	
	httpOrHttps:function(){ return window.location.protocol == "https:" ? 'https://' : 'http://'; },
	
	imgLoading:function(){ return $('<span class="img imgLoading"/>'); },
	imgLongLoading:function(){ return this.imgLoading(); /* return $('<span class="img imgLongLoading"/>')*/ },
	bodyIcon:function(iconName,of,my,at){
		if(!my) my='right center';
		if(!at) at='right center';
		var icon=$('<span class="icon tick"/>').css({display:'block',position:'absolute'})
			.position({my:my,at:at,of:of}).delay(3000).fadeOut('slow',function(){icon.remove()}).appendTo('body');
		return icon;
	},
	
	loadCss:function(url){
		var link=document.createElement("link");
		link.rel='stylesheet';
		link.type='text/css';
		link.href=url;
		document.head.appendChild(link);
	},
	
	loadScript:function(url,lang,to){
		if(OLD_IE){
			$.ajax({ url:url, global:false, async:false, cache:true, dataType:'script' });
		}else{
			var script = document.createElement("script");
			script.type = "text/javascript";
			script.src = url;
			if(lang) script.lang=lang;
			(to||document.body).appendChild(script);
		}
	},
	loadAsyncScript:function(url,lang,to){
		if(OLD_IE){
			$.ajax({ url:url, global:false, async:true, cache:true, dataType:'script' });
		}else{
			var script = document.createElement("script");
			script.type = "text/javascript";
			script.src = url;
			if(lang) script.lang=lang;
			script.async=true;
			(to||document.body).appendChild(script);
		}
	},
	loadSyncScript:function(url){
		return $.ajax({
			url:url,
			global:false, async:false, cache:true,
			dataType:'script'
		});
	},
	syncGet:function(url, data, type){
		/*// shift arguments if data argument was omitted
		if ( jQuery.isFunction( data ) ) {
			type = type || callback;
			callback = data;
			data = undefined;
		}*/
		
		var result;
		$.ajax({
			url: url,
			data: data,
			success: function(r){result=r;},
			dataType: type,
			async:false
		});
		return result;
	},
	
	syncJson:function(url,data){
		return this.syncGet(url,data,'json');
	},
	
	
	tools:{
		preg_replace:function(array_pattern, array_pattern_replace, my_string){
			var new_string = String (my_string);
			for (var i=0; i<array_pattern.length; i++){
				var reg_exp= RegExp(array_pattern[i], "gi");
				var val_to_replace = array_pattern_replace[i];
				new_string = new_string.replace (reg_exp, val_to_replace);
			}
			return new_string;
		},
		/** 
		 * @deprecated Use UString.stripTags()
		 */
		stripHTML:function(str){return str.replace(/<&#91;^>&#93;*>/g, '');},


		autolinkRegExp:new RegExp("(\\s?)(((http|https|ftp)://[^\\s</]+)[^\\s<]*[^\\s<\.)])", "gim"),
		autolink:function(str, attributes){
			attributes = attributes || {"target":"_blank"};
			var attrs = "";
			for(name in attributes) attrs += " "+ name +'="'+ attributes[name] +'"';
			return str.toString().replace(S.tools.autolinkRegExp, '$1<img class="favicon" src="http://www.google.com/s2/favicons?domain=$3" height="16" alt=""/><a href="$2"'+ attrs +'>$2</a>');
		}
	},
	
	preventMiddleClick:function(){
		$document.on('click',function(e){
			if(e.which==2){
				e.preventDefault();
				return false;
			}
		});
	},
	
	
	addSetMethods:function(targetclass,methods){
		for(var i in methods.split(',')){
			var methodName=methods[i];
			targetclass.prototype[methodName]=function(val){ this['_'+methodName]=val; return this; };
		}
	},
	
	/* OTHERS */
	
	tableClick:function(){
		S.eltClick('table.pointer tr');
	}
});
includeCore('base/eltClick');
/*#if DEV*/includeCore('libs/stacktrace');/*#/if*/

RegExp.sEscape=S.regexpEscape;

var $document=$document.on('focus','input.submit,button,.button',function(){ $(this).delay(1200).blur() })
	.ajaxError(function(e, xhr, settings, thrownError){
		if(xhr.status===503) alert(i18nc['http.503.maintenance']);
		else if(xhr.status===500) alert(i18nc['http.500']);
		else if(xhr.status===403) alert(i18nc['http.403']);
	});

var jqueryCleanData=$.cleanData;
$.cleanData=function(elems){
	for ( var i=0,elem ; (elem = elems[i]) != null; i++ )
		$.event.trigger('dispose',undefined,elem,true);
	return jqueryCleanData.apply(this,arguments);
}

UObj.extend($.fn,{
	forEach:$.fn.each,
	
	/* https://github.com/bgrins/bindWithDelay/blob/master/bindWithDelay.js */
	delayedBind:function(delay,eventType,eventData,handler,throttle){
		if($.isFunction(eventData)){
			throttle = handler;
			handler = eventData;
			eventData = undefined;
		}
		handler.guid = handler.guid || $.guid++;
		
		return this.each(function(){
			var wait = null;
			
			function cb() {
				var ctx = this;
				var throttler = function() {
					wait = null;
					handler.apply(ctx,arguments);
				};
				if (!throttle) { clearTimeout(wait); wait = null; }
				if (!wait) { wait = setTimeout(throttler,delay); }
			}
			cb.guid = handler.guid;
			$(this).bind(eventType,eventData,cb);
		});
	},
	
	delayedKeyup:function(delay,handler){
		if($.isFunction(delay)){
			handler=delay;
			delay=200;
		}
		return $(this).delayedBind(delay,'keyup',undefined,handler);
	},
	sHide:function(){ this.addClass('hidden'); return this; },
	sShow:function(){ this.removeClass('hidden ssHidden'); return this; },
	sToggle:function(){ return this[this.css('display')==='none'?'sShow':'sHide'](); },// cannot use hasClass('hidden') : ssHidden !
	
	nodeName:function(){
		return this.prop('tagName').toLowerCase();
	}
});

/*function extendBasic(subclass,superclass,basicsuperclass,varName,extendsPrototype){
	extend(subclass,superclass,extendsPrototype);
	for(var i in basicsuperclass.prototype)
		subclass.prototype[i]=function(){return basicsuperclass.prototype[i].apply(this[varName],arguments);}
}*/

//compat springbok $
$.first=$;

/*#if DEV*/
S.error=function(m){
	console.error('S.error',m);
	alert(m);
};
(function(){
	var f=function(){
		$('[id]').each(function(){
			var ids = $('[id="'+this.id+'"]');
			if(ids.length>1 && ids[0]==this){
				var fError=(this.id.startsWith('springbok-')||this.id.startsWith('Springbok')?console.error:S.error);
				fError('Multiple IDs #'+this.id);
			}
		});
		
		if(!window.inputListHandlerIncluded && $('input[list]').length)
			S.error("You must include \'ui/inputListHandler\' in your js file to be able to handle input[list]");
		if(!window.inputDataBoxHandlerIncluded && $('input[data-box]').length)
			S.error("You must include \'ui/inputDataBoxHandler\' in your js file to be able to handle input[data-box]");
		
		if(!S.ajax && $('input[data-confirm]').length){
			S.error('You must include \'springbok.ajax\' in your js file to be able to use data-confirm');
		}
	};
	S.ready(f);
	$document.bind('springbokAjaxPageLoaded',f);
})();
/*#/if*/


