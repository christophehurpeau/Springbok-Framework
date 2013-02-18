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



var arraySliceFunction=Array.prototype.slice,$document=$(document);

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
	
	extObj:function(target,object){
		if(object)
			for(var i in object)
				target[i]=object[i];
		return target;
	},
	oUnion:function(target,object){
		if(object)
			for(var i in object)
				if(target[i]===undefined) target[i]=object[i];
		return target;
	},
	oClone:function(o){
		return S.extObj({},o);
	},
	
	
	clone:function(object){
		// clone like _.clone : Shallow copy
		//return $.extend({},object);
		return S.extObj({},object);
	},
	deepClone:function(object){
		return $.extend(true,{},object);
	},
	
	
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
		S.extObj(child,classProps);
		
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
		for(var i=1,l=parents.length;i<l;i++) S.oUnion(protoProps,parents[i].prototype);
		return S.extClass(parent,protoProps,classProps);
	},
	
	
	
	addSetMethods:function(targetclass,methods){
		for(var i in methods.split(',')){
			var methodName=methods[i];
			targetclass.prototype[methodName]=function(val){ this['_'+methodName]=val; return this; };
		}
	},
	
	map:function(objectOrArray,callback){
		if(objectOrArray)
			$.each(function(i,v){
				objectOrArray[i]=callback(v,i);
			});
		return objectOrArray;
	},
	oForEach:function(o,callback){
		for(var keys=Object.keys(o),length=keys.length,i=0;i<length;i++){
			var k=keys[i];
			callback(k,o[k]);
		}
	},
	oImplode:function(o,glue,callback){
		if(S.isFunc(glue)){ callback=glue; glue=''; }
		if(!callback) callback=function(k,v){ return v };
		var res='',keys=Object.keys(o),length=keys.length,i=0;
		for(;i<length;i++){
			var k=keys[i];
			if(i!==0) res+=glue;
			res+=callback(k,o[k]);
		}
		return res;
	},
	
	/* ARRAY */
	
	ASlice:Array.prototype.slice,
	aSlice1:function(a){ return S.ASlice.call(a,1); },
	aHasAmong:function(a,searchElements,i){
		for(var j=0, l=searchElements.length; j<l ; j++)
			if(a.indexOf(searchElements[j],i) !== -1) return true;
		return false;
	},
	
	/* STRING */
	
	sTranslit:function(s){
		[
			[/æ|ǽ/,'ae'],
			[/œ/,'oe'], [/Œ/,'OE'],
			[/Ä|À|Á|Â|Ã|Ä|Å|Ǻ|Ā|Ă|Ą|Ǎ/,'A'], [/ä|à|á|â|ã|å|ǻ|ā|ă|ą|ǎ|ª/,'a'],
			[/Ç|Ć|Ĉ|Ċ|Č/,'C'], [/ç|ć|ĉ|ċ|č/,'c'],
			[/Ð|Ď|Đ/,'D'], [/ð|ď|đ/,'d'],
			[/È|É|Ê|Ë|Ē|Ĕ|Ė|Ę|Ě|€/,'E'], [/è|é|ê|ë|ē|ĕ|ė|ę|ě/,'e'],
			[/Ĝ|Ğ|Ġ|Ģ/,'G'], [/ĝ|ğ|ġ|ģ/,'g'],
			[/Ĥ|Ħ/,'H'], [/ĥ|ħ/,'h'],
			[/Ì|Í|Î|Ï|Ĩ|Ī|Ĭ|Ǐ|Į|İ/,'I'], [/ì|í|î|ï|ĩ|ī|ĭ|ǐ|į|ı/,'i'],
			[/Ĵ/,'J'], [/ĵ/,'j'],
			[/Ķ/,'K'], [/ķ/,'k'],
			[/Ĺ|Ļ|Ľ|Ŀ|Ł/,'L'], [/ĺ|ļ|ľ|ŀ|ł/,'l'],
			[/Ñ|Ń|Ņ|Ň/,'N'], [/ñ|ń|ņ|ň|ŉ/,'n'],
			[/Ö|Ò|Ó|Ô|Õ|Ō|Ŏ|Ǒ|Ő|Ơ|Ø|Ǿ/,'O'], [/ö|ò|ó|ô|õ|ō|ŏ|ǒ|ő|ơ|ø|ǿ|º|°/,'o'],
			[/Ŕ|Ŗ|Ř/,'R'], [/ŕ|ŗ|ř/,'r'],
			[/Ś|Ŝ|Ş|Š/,'S'], [/ś|ŝ|ş|š|ſ/,'s'],
			[/Ţ|Ť|Ŧ/,'T'], [/ţ|ť|ŧ/,'t'],
			[/Ü|Ù|Ú|Û|Ũ|Ū|Ŭ|Ů|Ű|Ų|Ư|Ǔ|Ǖ|Ǘ|Ǚ|Ǜ/,'U'], [/ü|ù|ú|û|ũ|ū|ŭ|ů|ű|ų|ư|ǔ|ǖ|ǘ|ǚ|ǜ/,'u'],
			[/Ý|Ÿ|Ŷ/,'Y'], [/ý|ÿ|ŷ/,'y'],
			[/Ŵ/,'W'], [/ŵ/,'w'],
			[/Ź|Ż|Ž/,'Z'], [/ź|ż|ž/,'z'],
			[/Æ|Ǽ/,'AE'],
			[/ß/,'ss'],
			[/Ĳ/,'IJ'], [/ĳ/,'ij'],
			
			[/ƒ/,'f'],
			[/&/,'et'],
			
			[/þ/,'th'],
			[/Þ/,'TH'],
		].forEach(function(v){ s=s.replace(v[0],v[1]); });
		return s;
	},
	sNormalize:function(s){
		return S.sTranslit(s).replace(/[ \-\'\"\_\(\)\[\]\{\}\#\~\&\*\,\.\;\:\!\?\/\\\\|\`\<\>\+]+/,' ')
					.trim().toLowerCase();
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
	},
	eltClick:function(selector){
		$(selector).off('click').each(function(i,elt){
			elt=$(elt);
			var trTimeout,href=elt.attr('rel');
			if(!href) return;
			elt.on('click',function(){trTimeout=setTimeout(function(){S.redirect(href)},180);});
			elt.find('a[href]:not([href="#"])').off('click').on('click',function(e){ clearTimeout(trTimeout); e.stopPropagation(); e.preventDefault();
					var a=$(this),confirmMessage=a.data('confirm'); if(!confirmMessage || confirm(confirmMessage=='1' ? i18nc['Are you sure ?'] : confirmMessage)) S.redirect(a.attr('href')); });
			elt.find('a[href="#"]').off('click').on('click',function(e){ clearTimeout(trTimeout); e.stopPropagation(); e.preventDefault(); });
		});
		
	}
};

/* DEV */includeCore('libs/stacktrace');/* /DEV */

includeCore('springbok.ext.string');
includeCore('springbok.ext.arrays');

RegExp.sEscape=function(value){
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

S.extObj($.fn,{
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


