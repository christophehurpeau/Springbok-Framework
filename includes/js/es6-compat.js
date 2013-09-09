(function(global){if(!String.prototype.contains) String.prototype.contains=function(str){return this.indexOf(str)!==-1};
if(!String.prototype.startsWith) String.prototype.startsWith=function(str,pos){return this.indexOf(str,pos)===0};
if(!String.prototype.endsWith) String.prototype.endsWith=function(str,pos){ var d=this.length-str.length; return d >= 0 && this.indexOf(str,d)===d; };

if (!String.prototype.trimLeft || !String.prototype.trimRight){
	var ws = "[" + "\x09\x0A\x0B\x0C\x0D\x20\xA0\u1680\u180E\u2000\u2001\u2002\u2003" +
	"\u2004\u2005\u2006\u2007\u2008\u2009\u200A\u202F\u205F\u3000\u2028" +
	"\u2029\uFEFF"+ "]";
	var trimBeginRegexp = new RegExp("^" + ws + ws + "*"), trimEndRegexp = new RegExp(ws + ws + "*$");
	String.prototype.trimLeft = function(){ return String(this).replace(trimBeginRegexp, ""); };
	String.prototype.trimRight = function(){ return String(this).replace(trimEndRegexp, ""); };
}

if(!String.prototype.repeat) String.prototype.repeat=function(count){
	//return new Array(count + 1).join(this)
	if(count < 1) return '';
	/* Growing pattern : http://jsfiddle.net/disfated/GejWV/ */
	var result = '',pattern=this.valueOf();
	while(count > 0){
		if (count & 1) result += pattern;
		count >>= 1, pattern += pattern;
	}
	return result;
};

/* https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/String */
// Assumes all supplied String instance methods already present (one may use shims for these if not available)
(function () {
    'use strict';

    var i,
        // We could also build the array of methods with the following, but the
        //   getOwnPropertyNames() method is non-shimable:
        // Object.getOwnPropertyNames(String).filter(function (methodName) {return typeof String[methodName] === 'function'});
        methods = [
            /*'quote', */'substring', 'toLowerCase', 'toUpperCase', 'charAt',
            'charCodeAt', 'indexOf', 'lastIndexOf', 'startsWith', 'endsWith',
            'trim', 'trimLeft', 'trimRight', 'toLocaleLowerCase',
            'toLocaleUpperCase', 'localeCompare', 'match', 'search',
            'replace', 'split', 'substr', 'concat', 'slice'
        ],
        methodCount = methods.length,
        assignStringGeneric = function (methodName) {
            var method = String.prototype[methodName];

            String[methodName] = function (arg1) {
                return method.apply(arg1, Array.prototype.slice.call(arguments, 1));
            };
        };

    for (i = 0; i < methodCount; i++) {
        assignStringGeneric(methods[i]);
    }
}());
/* https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Array#Array_generic_methods */
if(!Array.forEach)
	(function () {
		'use strict';
	
		var i,
			// We could also build the array of methods with the following, but the
			//   getOwnPropertyNames() method is non-shimable:
			// Object.getOwnPropertyNames(Array).filter(function (methodName) {return typeof Array[methodName] === 'function'});
			methods = [
				'join', 'reverse', 'sort', 'push', 'pop', 'shift', 'unshift',
				'splice', 'concat', 'slice', 'indexOf', 'lastIndexOf',
				'forEach', 'map', 'reduce', 'reduceRight', 'filter',
				'some', 'every'
			],
			methodCount = methods.length,
			assignArrayGeneric = function (methodName) {
				var method = Array.prototype[methodName];
				Array[methodName] = function (arg1) {
					return method.apply(arg1, UArray.slice1(arguments));
				};
			};
	
		for (i = 0; i < methodCount; i++) {
			assignArrayGeneric(methods[i]);
		}
	}());
/* https://github.com/WebReflection/es6-collections */
/* https://raw.github.com/eriwen/es6-map-shim/master/es6-map-shim.js */


/* chrome pre-implementation is lacking of the iterator method. Must be activated in prefs (like node --harmony) so most users won't have any implementation '*/
if(global.Map && (!global.Map.prototype.iterator || !global.Map.prototype.clear)) global.Map=null;


global.Map = global.Map || (function(){
	
	
	var Map=function(iterator){
		this.items = {}; this.size = 0;
		if(iterator) iterator.forEach(function(kv){
			this.items[kv[0]] = kv[1];
		}.bind(this));
	};
	Object.defineProperties(Map.prototype,{
		set:{ value:function(k,v){
			if(!this.items.hasOwnProperty(k))
				this.size++;
			this.items[k]=v;
		} },
		get:{ value:function(k){
			return this.items[k];
		} },
		has:{ value:function(k){
			return this.items.hasOwnProperty(k);
		} },
		'delete':{ value:function(k){
			if(this.items.hasOwnProperty(k)){
				this.size--;
				delete this.items[k];
			}
		} },
		clear:{ value:function(){
			this.size = 0;
			this.items = {};
		} },
		iterator:{ value:function(){
			return UObj.iterator(this.items);
		} },
		forEach:{ value:function(callback){
			UObj.forEach(this.items,callback,this);
		} },
		toString:{ value: function() {
			return '[Object Map]';
		} }
	});
	return Map;
})();
/* firefox implementation is lacking of the forEach method*/
if(!Map.prototype.forEach)
	/**
	 * Given a callback function and optional context, invoke the callback on all
	 * entries in this Map.
	 *
	 * @param callbackFn {Function}
	 */
	Map.prototype.forEach=function(callbackfn){
		/*var iter = this.iterator();
		while(true){
			var current;
			try{ current=iter.next(); }catch(err){ return; }
			callbackfn.call(this,current[0],current[1]);
		}*/
		var it=S.iterator(this);
		while(it.hasNext())
			callbackfn.apply(this,it.next());
	};

/* chrome pre-implementation is lacking of the iterator method. Must be activated in prefs (like node --harmony) so most users won't have any implementation '*/
if(global.Set && (!global.Set.prototype.iterator || !global.Set.prototype.clear)) global.Set=null;

global.Set = global.Set || (function(){
	
	
	var Set=function(iterator){
		this.items = []; this.size = 0;
		if(iterator) iterator.forEach(function(v){
			this.items.push(v);
		}.bind(this));
	};
	Object.defineProperties(Set.prototype,{
		add:{ value:function(v){
			if(!this.has(v)){
				this.size++;
				this.items.push(v);
			}
		} },
		has:{ value:function(v){
			return UArray.has(this.items,v);
		} },
		'delete':{ value:function(v){
			var i=this.items.indexOf(v);
			if(i!==-1){
				this.size--;
				this.items = this.items.slice(i,1);
			}
		} },
		clear:{ value:function(){
			this.size = 0;
			this.items = [];
		} },
		iterator:{ value:function(){
			var it = UArray.iterator(this.items);
			return Object.freeze({
				hasNext:it.hasNext.bind(it),
				next:function(){
					return this.items[it.next()];
				}
			});
		} },
		forEach:{ value:function(callback){
			this.items.forEach.call(this.items,callback,this);
		} },
		toString:{ value: function() {
			return '[Object Set]';
		} }
	});
	return Set;
})();
/* firefox implementation is lacking of the forEach method*/
if(!Set.prototype.forEach)
	/**
	 * Given a callback function and optional context, invoke the callback on all
	 * entries in this Map.
	 *
	 * @param callbackFn {Function}
	 */
	Map.prototype.forEach=function(callbackfn){
		var it=S.iterator(this);
		while(it.hasNext())
			callbackfn.apply(this,it.next());
	};

/* loaded by :
 * https://developer.mozilla.org/en-US/docs/Web/API/CustomEvent
 *  => Firefox < 11, Chrome < 15, IE, Opera < 11.6, Safari
 * 
 * https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Array#Array_generic_methods
 * https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/String/contains
 *  => Almost all except Firefox > 19
 * 
 * -
 * http://kangax.github.com/es5-compat-table/es6/
 */

// document.head (HTML5)
if(!document.head) document.head = document.getElementsByTagName('head')[0];
// Iterators
window.StopIteration=window.StopIteration || {};

/*/new Event (ie10)
try{
	var e=new Event('__es6Event__');
}catch(err){
	window.Event=function(name){
		var event = document.createEvent('Event');
		event.initEvent(name, true, true);
		return event;
	}
}*/

/* DOM-shim : https://github.com/Raynos/DOM-shim/tree/master/src/all/interfaces */

var shim=function(name,shim){
	var constructor = window[name];
	if (!constructor)
		constructor = window[name] = shim.interface;
	delete shim.interface;
	var proto = constructor && constructor.prototype;
	if (shim.prototype) {
		proto = constructor.prototype = shim.prototype;
		delete shim.prototype;
	}
	
	console&&console.log("adding interface ", name);
	
	if (shim.hasOwnProperty("constructor")) {
		window[name] = constructor = shim.constructor;
		proto && (shim.constructor.prototype = proto);
		delete shim.constructor;
	}
};

shim('Event',{
	constructor: function(type, dict){
		var e = document.createEvent("Event");
		dict = dict || {};
		dict.bubbles = dict.bubbles || false;
		dict.catchable = dict.catchable || false;
		e.initEvent(type, dict.bubbles, dict.catchable);
		return e;
	}
});

shim('EventTarget',{
	interface: window.Node || window.Element
});

shim('CustomEvent',{
	interface: window.Event,
	constructor: function(type, dict){
		var e = document.createEvent("CustomEvent");
		dict = dict || {};
		dict.detail = dict.detail || null;
		dict.bubbles = dict.bubbles || false;
		dict.catchable = dict.catchable || false;
		if (e.initCustomEvent) {
			e.initCustomEvent(type, dict.bubbles, dict.catchable, dict.detail);
		} else {
			e.initEvent(type, dict.bubbles, dict.catchable);
			e.detail = dict.detail;
		}
		return e;
	}
});



})(window);