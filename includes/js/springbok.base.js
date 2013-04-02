/*! Springbok */
/*'use strict';*/
/* PROD */
window.onerror=function handleError(message,url,line){
	if(url && !(url.indexOf('chrome://')===0 || url.indexOf('http://127.0.0.1')===0))
		$.get(basedir+'site/jsError',{href:window.location.href,jsurl:url,message:message,line:line});
	//alert("An error has occurred!\n"+e);
	//if(console) console.log(e);
	//console.log(arguments);
	/* DEV */
	/*if(console){
		console.log(arguments,S&&S.StackTrace());
	}*/
	/* /DEV */
	//return true;
	return false;
};
/* /PROD */

/* DEV */


/* /DEV */

if(!Object.keys){//http://kangax.github.com/es5-compat-table/
	$.ajax({ url:webUrl+'js/es5-compat.js', global:false, async:false, cache:true, dataType:'script' });
}



var global=window,arraySliceFunction=Array.prototype.slice,$document=$(document);

includeCoreUtils('UObj');
includeCoreUtils('UArray');
includeCoreUtils('UString/');

window.S={
	ready:function(callback){ $document.ready(callback); },
	redirect:function(url){ url && (window.location=url); },
	setTitle:function(title){document.title=title;},
	
	imgLoading:function(){ return $('<span class="img imgLoading"/>') },
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
		var script = document.createElement("script");
		script.type = "text/javascript";
		script.src = url;
		if(lang) script.lang=lang;
		(to||document.body).appendChild(script);
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
		$document.bind('click',function(e){
			if(e.which==2){
				e.preventDefault();
				return false;
			}
		});
	},
	
	isString:function(varName){ return typeof(varName)==='string'; },
	isArray:Array.isArray || $.isArray,
	isObject:function(varName){ return typeof(varName)==='object' },
	isObj:function(varName){ return typeof(varName)==='object' },
	isFunc:function(varName){ return typeof(varName)==='function' },
	
	
	/* Inheritance & Classes */
	
	extProto:function(targetclass,methods){
		if(methods)
			for(var i in methods)
				targetclass.prototype[i]=methods[i];
		return targetclass;
	},
	
	extChild:function(child,parent,protoProps){
		// Set the prototype chain to inherit from `parent`, without calling `parent`'s constructor function.
		// + Set a convenience property in case the parent's prototype is needed later.
		child.prototype=Object.create(child.super_ = parent.prototype);
		child.superCtor = parent;
		
		// Add prototype properties (instance properties) to the subclass,
		// if supplied.
		S.extProto(child,child._inheritsproto_=protoProps);
		
		return child;
	},
	
	/* http://backbonejs.org/backbone.js */
	inherits:function(parent,protoProps,classProps){
		// The constructor function for the new subclass is either defined by you
		// (the "constructor" property in your `extend` definition), or defaulted
		// by us to simply call the parent's constructor.
		var child = protoProps && protoProps.hasOwnProperty('ctor') ?
				protoProps.ctor
				: function(){ parent.apply(this,arguments); };
		/*
		// Set the prototype chain to inherit from `parent`, without calling `parent`'s constructor function.
		// + Set a convenience property in case the parent's prototype is needed later.
		child.prototype=Object.create(child.super_ = parent.prototype);
		child.superCtor = parent;
		
		// Add prototype properties (instance properties) to the subclass,
		// if supplied.
		S.extProto(child,child._inheritsproto_=protoProps);
		*/
		S.extChild(child,parent,protoProps);
		
		// Add static properties to the constructor function, if supplied.
		UObj.extend(child,classProps);
		
		child.prototype.self = child;
		//child.prototype.super_ = child.super_;
		//child.prototype.superCtor = parent;
		
		return child;
	},
	
	extThis:function(protoProps,classProps){ return S.extClass(this,protoProps,classProps); },
	extClass:function(parent,protoProps,classProps){
		var child = S.inherits(parent,protoProps,classProps);
		child.extend = S.extThis;
		return child;
	},
	extClasses:function(parents,protoProps,classProps){
		var parent=parents[0];
		for(var i=1,l=parents.length;i<l;i++) UObj.union(protoProps,parents[i].prototype);
		return S.extClass(parent,protoProps,classProps);
	},
	
	
	
	addSetMethods:function(targetclass,methods){
		for(var i in methods.split(',')){
			var methodName=methods[i];
			targetclass.prototype[methodName]=function(val){ this['_'+methodName]=val; return this; };
		}
	},
	
	map:function(arrayOrObject,callback){
		return S.isArray(arrayOrObject) ? arrayOrObject.map(callback) : UObj.map(arrayOrObject,callback);
	},
	join:function(arrayOrObject,separator){
		return S.isArray(arrayOrObject) ? arrayOrObject.join(separator) : UObj.join(arrayOrObject,separator);
	},
	
	/* STRING */
	
	sNormalize:function(s){
	},
	
	/* HTML */
	escape:function(html){
		return String(html)
			.replace(/&(?!\w+;)/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;');
	},
	escapeUrl:function(html){
		return html.replace('&','&amp;');
	},
	
	/* OTHERS */
	
	tableClick:function(){
		S.eltClick('table.pointer tr');
	}
};
includeCore('base/eltClick');
/* DEV */includeCore('libs/stacktrace');/* /DEV */

S.regexpEscape=RegExp.sEscape=function(value){
	return value.replace( /([\^\$\(\)\[\]\{\}\*\.\+\?\|\\])/gi, "\\$1" );
};

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
	sToggle:function(){ return this[this.css('display')==='none'?'sShow':'sHide'](); }// cannot use hasClass('hidden') : ssHidden !
});

/*function extendBasic(subclass,superclass,basicsuperclass,varName,extendsPrototype){
	extend(subclass,superclass,extendsPrototype);
	for(var i in basicsuperclass.prototype)
		subclass.prototype[i]=function(){return basicsuperclass.prototype[i].apply(this[varName],arguments);}
}*/




/* DEV */
S.error=function(m){
	console.error(m);
	alert(m);
};
(function(){
	var f=function(){
		$('[id]').each(function(){
			var ids = $('[id="'+this.id+'"]');
			if(ids.length>1 && ids[0]==this)
				S.error('Multiple IDs #'+this.id);
		});
		
		if(!window.inputListHandlerIncluded && $('input[list]').length)
			S.error("You must include \'ui/inputListHandler\' in your js file to be able to handle input[list]");
		if(!window.inputDataBoxHandlerIncluded && $('input[data-box]').length)
			S.error("You must include \'ui/inputDataBoxHandler\' in your js file to be able to handle input[data-box]");
		
	};
	S.ready(f);
	$document.bind('springbokAjaxPageLoaded',f);
})();
/* /DEV */


