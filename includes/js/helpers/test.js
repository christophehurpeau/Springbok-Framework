(function(window,document,Object,Array,Math,undefined){window.basedir='/2012/social/dev/';window.webdir=basedir+'web/';window.staticUrl=webdir;window.imgdir=webdir+'img/';window.jsdir=webdir+'js/';
/*! jQuery v1.7.2 jquery.com | jquery.org/license */
(function(a,b){function cy(a){return f.isWindow(a)?a:a.nodeType===9?a.defaultView||a.parentWindow:!1}function cu(a){if(!cj[a]){var b=c.body,d=f("<"+a+">").appendTo(b),e=d.css("display");d.remove();if(e==="none"||e===""){ck||(ck=c.createElement("iframe"),ck.frameBorder=ck.width=ck.height=0),b.appendChild(ck);if(!cl||!ck.createElement)cl=(ck.contentWindow||ck.contentDocument).document,cl.write((f.support.boxModel?"<!doctype html>":"")+"<html><body>"),cl.close();d=cl.createElement(a),cl.body.appendChild(d),e=f.css(d,"display"),b.removeChild(ck)}cj[a]=e}return cj[a]}function ct(a,b){var c={};f.each(cp.concat.apply([],cp.slice(0,b)),function(){c[this]=a});return c}function cs(){cq=b}function cr(){setTimeout(cs,0);return cq=f.now()}function ci(){try{return new a.ActiveXObject("Microsoft.XMLHTTP")}catch(b){}}function ch(){try{return new a.XMLHttpRequest}catch(b){}}function cb(a,c){a.dataFilter&&(c=a.dataFilter(c,a.dataType));var d=a.dataTypes,e={},g,h,i=d.length,j,k=d[0],l,m,n,o,p;for(g=1;g<i;g++){if(g===1)for(h in a.converters)typeof h=="string"&&(e[h.toLowerCase()]=a.converters[h]);l=k,k=d[g];if(k==="*")k=l;else if(l!=="*"&&l!==k){m=l+" "+k,n=e[m]||e["* "+k];if(!n){p=b;for(o in e){j=o.split(" ");if(j[0]===l||j[0]==="*"){p=e[j[1]+" "+k];if(p){o=e[o],o===!0?n=p:p===!0&&(n=o);break}}}}!n&&!p&&f.error("No conversion from "+m.replace(" "," to ")),n!==!0&&(c=n?n(c):p(o(c)))}}return c}function ca(a,c,d){var e=a.contents,f=a.dataTypes,g=a.responseFields,h,i,j,k;for(i in g)i in d&&(c[g[i]]=d[i]);while(f[0]==="*")f.shift(),h===b&&(h=a.mimeType||c.getResponseHeader("content-type"));if(h)for(i in e)if(e[i]&&e[i].test(h)){f.unshift(i);break}if(f[0]in d)j=f[0];else{for(i in d){if(!f[0]||a.converters[i+" "+f[0]]){j=i;break}k||(k=i)}j=j||k}if(j){j!==f[0]&&f.unshift(j);return d[j]}}function b_(a,b,c,d){if(f.isArray(b))f.each(b,function(b,e){c||bD.test(a)?d(a,e):b_(a+"["+(typeof e=="object"?b:"")+"]",e,c,d)});else if(!c&&f.type(b)==="object")for(var e in b)b_(a+"["+e+"]",b[e],c,d);else d(a,b)}function b$(a,c){var d,e,g=f.ajaxSettings.flatOptions||{};for(d in c)c[d]!==b&&((g[d]?a:e||(e={}))[d]=c[d]);e&&f.extend(!0,a,e)}function bZ(a,c,d,e,f,g){f=f||c.dataTypes[0],g=g||{},g[f]=!0;var h=a[f],i=0,j=h?h.length:0,k=a===bS,l;for(;i<j&&(k||!l);i++)l=h[i](c,d,e),typeof l=="string"&&(!k||g[l]?l=b:(c.dataTypes.unshift(l),l=bZ(a,c,d,e,l,g)));(k||!l)&&!g["*"]&&(l=bZ(a,c,d,e,"*",g));return l}function bY(a){return function(b,c){typeof b!="string"&&(c=b,b="*");if(f.isFunction(c)){var d=b.toLowerCase().split(bO),e=0,g=d.length,h,i,j;for(;e<g;e++)h=d[e],j=/^\+/.test(h),j&&(h=h.substr(1)||"*"),i=a[h]=a[h]||[],i[j?"unshift":"push"](c)}}}function bB(a,b,c){var d=b==="width"?a.offsetWidth:a.offsetHeight,e=b==="width"?1:0,g=4;if(d>0){if(c!=="border")for(;e<g;e+=2)c||(d-=parseFloat(f.css(a,"padding"+bx[e]))||0),c==="margin"?d+=parseFloat(f.css(a,c+bx[e]))||0:d-=parseFloat(f.css(a,"border"+bx[e]+"Width"))||0;return d+"px"}d=by(a,b);if(d<0||d==null)d=a.style[b];if(bt.test(d))return d;d=parseFloat(d)||0;if(c)for(;e<g;e+=2)d+=parseFloat(f.css(a,"padding"+bx[e]))||0,c!=="padding"&&(d+=parseFloat(f.css(a,"border"+bx[e]+"Width"))||0),c==="margin"&&(d+=parseFloat(f.css(a,c+bx[e]))||0);return d+"px"}function bo(a){var b=c.createElement("div");bh.appendChild(b),b.innerHTML=a.outerHTML;return b.firstChild}function bn(a){var b=(a.nodeName||"").toLowerCase();b==="input"?bm(a):b!=="script"&&typeof a.getElementsByTagName!="undefined"&&f.grep(a.getElementsByTagName("input"),bm)}function bm(a){if(a.type==="checkbox"||a.type==="radio")a.defaultChecked=a.checked}function bl(a){return typeof a.getElementsByTagName!="undefined"?a.getElementsByTagName("*"):typeof a.querySelectorAll!="undefined"?a.querySelectorAll("*"):[]}function bk(a,b){var c;b.nodeType===1&&(b.clearAttributes&&b.clearAttributes(),b.mergeAttributes&&b.mergeAttributes(a),c=b.nodeName.toLowerCase(),c==="object"?b.outerHTML=a.outerHTML:c!=="input"||a.type!=="checkbox"&&a.type!=="radio"?c==="option"?b.selected=a.defaultSelected:c==="input"||c==="textarea"?b.defaultValue=a.defaultValue:c==="script"&&b.text!==a.text&&(b.text=a.text):(a.checked&&(b.defaultChecked=b.checked=a.checked),b.value!==a.value&&(b.value=a.value)),b.removeAttribute(f.expando),b.removeAttribute("_submit_attached"),b.removeAttribute("_change_attached"))}function bj(a,b){if(b.nodeType===1&&!!f.hasData(a)){var c,d,e,g=f._data(a),h=f._data(b,g),i=g.events;if(i){delete h.handle,h.events={};for(c in i)for(d=0,e=i[c].length;d<e;d++)f.event.add(b,c,i[c][d])}h.data&&(h.data=f.extend({},h.data))}}function bi(a,b){return f.nodeName(a,"table")?a.getElementsByTagName("tbody")[0]||a.appendChild(a.ownerDocument.createElement("tbody")):a}function U(a){var b=V.split("|"),c=a.createDocumentFragment();if(c.createElement)while(b.length)c.createElement(b.pop());return c}function T(a,b,c){b=b||0;if(f.isFunction(b))return f.grep(a,function(a,d){var e=!!b.call(a,d,a);return e===c});if(b.nodeType)return f.grep(a,function(a,d){return a===b===c});if(typeof b=="string"){var d=f.grep(a,function(a){return a.nodeType===1});if(O.test(b))return f.filter(b,d,!c);b=f.filter(b,d)}return f.grep(a,function(a,d){return f.inArray(a,b)>=0===c})}function S(a){return!a||!a.parentNode||a.parentNode.nodeType===11}function K(){return!0}function J(){return!1}function n(a,b,c){var d=b+"defer",e=b+"queue",g=b+"mark",h=f._data(a,d);h&&(c==="queue"||!f._data(a,e))&&(c==="mark"||!f._data(a,g))&&setTimeout(function(){!f._data(a,e)&&!f._data(a,g)&&(f.removeData(a,d,!0),h.fire())},0)}function m(a){for(var b in a){if(b==="data"&&f.isEmptyObject(a[b]))continue;if(b!=="toJSON")return!1}return!0}function l(a,c,d){if(d===b&&a.nodeType===1){var e="data-"+c.replace(k,"-$1").toLowerCase();d=a.getAttribute(e);if(typeof d=="string"){try{d=d==="true"?!0:d==="false"?!1:d==="null"?null:f.isNumeric(d)?+d:j.test(d)?f.parseJSON(d):d}catch(g){}f.data(a,c,d)}else d=b}return d}function h(a){var b=g[a]={},c,d;a=a.split(/\s+/);for(c=0,d=a.length;c<d;c++)b[a[c]]=!0;return b}var c=a.document,d=a.navigator,e=a.location,f=function(){function J(){if(!e.isReady){try{c.documentElement.doScroll("left")}catch(a){setTimeout(J,1);return}e.ready()}}var e=function(a,b){return new e.fn.init(a,b,h)},f=a.jQuery,g=a.$,h,i=/^(?:[^#<]*(<[\w\W]+>)[^>]*$|#([\w\-]*)$)/,j=/\S/,k=/^\s+/,l=/\s+$/,m=/^<(\w+)\s*\/?>(?:<\/\1>)?$/,n=/^[\],:{}\s]*$/,o=/\\(?:["\\\/bfnrt]|u[0-9a-fA-F]{4})/g,p=/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g,q=/(?:^|:|,)(?:\s*\[)+/g,r=/(webkit)[ \/]([\w.]+)/,s=/(opera)(?:.*version)?[ \/]([\w.]+)/,t=/(msie) ([\w.]+)/,u=/(mozilla)(?:.*? rv:([\w.]+))?/,v=/-([a-z]|[0-9])/ig,w=/^-ms-/,x=function(a,b){return(b+"").toUpperCase()},y=d.userAgent,z,A,B,C=Object.prototype.toString,D=Object.prototype.hasOwnProperty,E=Array.prototype.push,F=Array.prototype.slice,G=String.prototype.trim,H=Array.prototype.indexOf,I={};e.fn=e.prototype={constructor:e,init:function(a,d,f){var g,h,j,k;if(!a)return this;if(a.nodeType){this.context=this[0]=a,this.length=1;return this}if(a==="body"&&!d&&c.body){this.context=c,this[0]=c.body,this.selector=a,this.length=1;return this}if(typeof a=="string"){a.charAt(0)!=="<"||a.charAt(a.length-1)!==">"||a.length<3?g=i.exec(a):g=[null,a,null];if(g&&(g[1]||!d)){if(g[1]){d=d instanceof e?d[0]:d,k=d?d.ownerDocument||d:c,j=m.exec(a),j?e.isPlainObject(d)?(a=[c.createElement(j[1])],e.fn.attr.call(a,d,!0)):a=[k.createElement(j[1])]:(j=e.buildFragment([g[1]],[k]),a=(j.cacheable?e.clone(j.fragment):j.fragment).childNodes);return e.merge(this,a)}h=c.getElementById(g[2]);if(h&&h.parentNode){if(h.id!==g[2])return f.find(a);this.length=1,this[0]=h}this.context=c,this.selector=a;return this}return!d||d.jquery?(d||f).find(a):this.constructor(d).find(a)}if(e.isFunction(a))return f.ready(a);a.selector!==b&&(this.selector=a.selector,this.context=a.context);return e.makeArray(a,this)},selector:"",jquery:"1.7.2",length:0,size:function(){return this.length},toArray:function(){return F.call(this,0)},get:function(a){return a==null?this.toArray():a<0?this[this.length+a]:this[a]},pushStack:function(a,b,c){var d=this.constructor();e.isArray(a)?E.apply(d,a):e.merge(d,a),d.prevObject=this,d.context=this.context,b==="find"?d.selector=this.selector+(this.selector?" ":"")+c:b&&(d.selector=this.selector+"."+b+"("+c+")");return d},each:function(a,b){return e.each(this,a,b)},ready:function(a){e.bindReady(),A.add(a);return this},eq:function(a){a=+a;return a===-1?this.slice(a):this.slice(a,a+1)},first:function(){return this.eq(0)},last:function(){return this.eq(-1)},slice:function(){return this.pushStack(F.apply(this,arguments),"slice",F.call(arguments).join(","))},map:function(a){return this.pushStack(e.map(this,function(b,c){return a.call(b,c,b)}))},end:function(){return this.prevObject||this.constructor(null)},push:E,sort:[].sort,splice:[].splice},e.fn.init.prototype=e.fn,e.extend=e.fn.extend=function(){var a,c,d,f,g,h,i=arguments[0]||{},j=1,k=arguments.length,l=!1;typeof i=="boolean"&&(l=i,i=arguments[1]||{},j=2),typeof i!="object"&&!e.isFunction(i)&&(i={}),k===j&&(i=this,--j);for(;j<k;j++)if((a=arguments[j])!=null)for(c in a){d=i[c],f=a[c];if(i===f)continue;l&&f&&(e.isPlainObject(f)||(g=e.isArray(f)))?(g?(g=!1,h=d&&e.isArray(d)?d:[]):h=d&&e.isPlainObject(d)?d:{},i[c]=e.extend(l,h,f)):f!==b&&(i[c]=f)}return i},e.extend({noConflict:function(b){a.$===e&&(a.$=g),b&&a.jQuery===e&&(a.jQuery=f);return e},isReady:!1,readyWait:1,holdReady:function(a){a?e.readyWait++:e.ready(!0)},ready:function(a){if(a===!0&&!--e.readyWait||a!==!0&&!e.isReady){if(!c.body)return setTimeout(e.ready,1);e.isReady=!0;if(a!==!0&&--e.readyWait>0)return;A.fireWith(c,[e]),e.fn.trigger&&e(c).trigger("ready").off("ready")}},bindReady:function(){if(!A){A=e.Callbacks("once memory");if(c.readyState==="complete")return setTimeout(e.ready,1);if(c.addEventListener)c.addEventListener("DOMContentLoaded",B,!1),a.addEventListener("load",e.ready,!1);else if(c.attachEvent){c.attachEvent("onreadystatechange",B),a.attachEvent("onload",e.ready);var b=!1;try{b=a.frameElement==null}catch(d){}c.documentElement.doScroll&&b&&J()}}},isFunction:function(a){return e.type(a)==="function"},isArray:Array.isArray||function(a){return e.type(a)==="array"},isWindow:function(a){return a!=null&&a==a.window},isNumeric:function(a){return!isNaN(parseFloat(a))&&isFinite(a)},type:function(a){return a==null?String(a):I[C.call(a)]||"object"},isPlainObject:function(a){if(!a||e.type(a)!=="object"||a.nodeType||e.isWindow(a))return!1;try{if(a.constructor&&!D.call(a,"constructor")&&!D.call(a.constructor.prototype,"isPrototypeOf"))return!1}catch(c){return!1}var d;for(d in a);return d===b||D.call(a,d)},isEmptyObject:function(a){for(var b in a)return!1;return!0},error:function(a){throw new Error(a)},parseJSON:function(b){if(typeof b!="string"||!b)return null;b=e.trim(b);if(a.JSON&&a.JSON.parse)return a.JSON.parse(b);if(n.test(b.replace(o,"@").replace(p,"]").replace(q,"")))return(new Function("return "+b))();e.error("Invalid JSON: "+b)},parseXML:function(c){if(typeof c!="string"||!c)return null;var d,f;try{a.DOMParser?(f=new DOMParser,d=f.parseFromString(c,"text/xml")):(d=new ActiveXObject("Microsoft.XMLDOM"),d.async="false",d.loadXML(c))}catch(g){d=b}(!d||!d.documentElement||d.getElementsByTagName("parsererror").length)&&e.error("Invalid XML: "+c);return d},noop:function(){},globalEval:function(b){b&&j.test(b)&&(a.execScript||function(b){a.eval.call(a,b)})(b)},camelCase:function(a){return a.replace(w,"ms-").replace(v,x)},nodeName:function(a,b){return a.nodeName&&a.nodeName.toUpperCase()===b.toUpperCase()},each:function(a,c,d){var f,g=0,h=a.length,i=h===b||e.isFunction(a);if(d){if(i){for(f in a)if(c.apply(a[f],d)===!1)break}else for(;g<h;)if(c.apply(a[g++],d)===!1)break}else if(i){for(f in a)if(c.call(a[f],f,a[f])===!1)break}else for(;g<h;)if(c.call(a[g],g,a[g++])===!1)break;return a},trim:G?function(a){return a==null?"":G.call(a)}:function(a){return a==null?"":(a+"").replace(k,"").replace(l,"")},makeArray:function(a,b){var c=b||[];if(a!=null){var d=e.type(a);a.length==null||d==="string"||d==="function"||d==="regexp"||e.isWindow(a)?E.call(c,a):e.merge(c,a)}return c},inArray:function(a,b,c){var d;if(b){if(H)return H.call(b,a,c);d=b.length,c=c?c<0?Math.max(0,d+c):c:0;for(;c<d;c++)if(c in b&&b[c]===a)return c}return-1},merge:function(a,c){var d=a.length,e=0;if(typeof c.length=="number")for(var f=c.length;e<f;e++)a[d++]=c[e];else while(c[e]!==b)a[d++]=c[e++];a.length=d;return a},grep:function(a,b,c){var d=[],e;c=!!c;for(var f=0,g=a.length;f<g;f++)e=!!b(a[f],f),c!==e&&d.push(a[f]);return d},map:function(a,c,d){var f,g,h=[],i=0,j=a.length,k=a instanceof e||j!==b&&typeof j=="number"&&(j>0&&a[0]&&a[j-1]||j===0||e.isArray(a));if(k)for(;i<j;i++)f=c(a[i],i,d),f!=null&&(h[h.length]=f);else for(g in a)f=c(a[g],g,d),f!=null&&(h[h.length]=f);return h.concat.apply([],h)},guid:1,proxy:function(a,c){if(typeof c=="string"){var d=a[c];c=a,a=d}if(!e.isFunction(a))return b;var f=F.call(arguments,2),g=function(){return a.apply(c,f.concat(F.call(arguments)))};g.guid=a.guid=a.guid||g.guid||e.guid++;return g},access:function(a,c,d,f,g,h,i){var j,k=d==null,l=0,m=a.length;if(d&&typeof d=="object"){for(l in d)e.access(a,c,l,d[l],1,h,f);g=1}else if(f!==b){j=i===b&&e.isFunction(f),k&&(j?(j=c,c=function(a,b,c){return j.call(e(a),c)}):(c.call(a,f),c=null));if(c)for(;l<m;l++)c(a[l],d,j?f.call(a[l],l,c(a[l],d)):f,i);g=1}return g?a:k?c.call(a):m?c(a[0],d):h},now:function(){return(new Date).getTime()},uaMatch:function(a){a=a.toLowerCase();var b=r.exec(a)||s.exec(a)||t.exec(a)||a.indexOf("compatible")<0&&u.exec(a)||[];return{browser:b[1]||"",version:b[2]||"0"}},sub:function(){function a(b,c){return new a.fn.init(b,c)}e.extend(!0,a,this),a.superclass=this,a.fn=a.prototype=this(),a.fn.constructor=a,a.sub=this.sub,a.fn.init=function(d,f){f&&f instanceof e&&!(f instanceof a)&&(f=a(f));return e.fn.init.call(this,d,f,b)},a.fn.init.prototype=a.fn;var b=a(c);return a},browser:{}}),e.each("Boolean Number String Function Array Date RegExp Object".split(" "),function(a,b){I["[object "+b+"]"]=b.toLowerCase()}),z=e.uaMatch(y),z.browser&&(e.browser[z.browser]=!0,e.browser.version=z.version),e.browser.webkit&&(e.browser.safari=!0),j.test(" ")&&(k=/^[\s\xA0]+/,l=/[\s\xA0]+$/),h=e(c),c.addEventListener?B=function(){c.removeEventListener("DOMContentLoaded",B,!1),e.ready()}:c.attachEvent&&(B=function(){c.readyState==="complete"&&(c.detachEvent("onreadystatechange",B),e.ready())});return e}(),g={};f.Callbacks=function(a){a=a?g[a]||h(a):{};var c=[],d=[],e,i,j,k,l,m,n=function(b){var d,e,g,h,i;for(d=0,e=b.length;d<e;d++)g=b[d],h=f.type(g),h==="array"?n(g):h==="function"&&(!a.unique||!p.has(g))&&c.push(g)},o=function(b,f){f=f||[],e=!a.memory||[b,f],i=!0,j=!0,m=k||0,k=0,l=c.length;for(;c&&m<l;m++)if(c[m].apply(b,f)===!1&&a.stopOnFalse){e=!0;break}j=!1,c&&(a.once?e===!0?p.disable():c=[]:d&&d.length&&(e=d.shift(),p.fireWith(e[0],e[1])))},p={add:function(){if(c){var a=c.length;n(arguments),j?l=c.length:e&&e!==!0&&(k=a,o(e[0],e[1]))}return this},remove:function(){if(c){var b=arguments,d=0,e=b.length;for(;d<e;d++)for(var f=0;f<c.length;f++)if(b[d]===c[f]){j&&f<=l&&(l--,f<=m&&m--),c.splice(f--,1);if(a.unique)break}}return this},has:function(a){if(c){var b=0,d=c.length;for(;b<d;b++)if(a===c[b])return!0}return!1},empty:function(){c=[];return this},disable:function(){c=d=e=b;return this},disabled:function(){return!c},lock:function(){d=b,(!e||e===!0)&&p.disable();return this},locked:function(){return!d},fireWith:function(b,c){d&&(j?a.once||d.push([b,c]):(!a.once||!e)&&o(b,c));return this},fire:function(){p.fireWith(this,arguments);return this},fired:function(){return!!i}};return p};var i=[].slice;f.extend({Deferred:function(a){var b=f.Callbacks("once memory"),c=f.Callbacks("once memory"),d=f.Callbacks("memory"),e="pending",g={resolve:b,reject:c,notify:d},h={done:b.add,fail:c.add,progress:d.add,state:function(){return e},isResolved:b.fired,isRejected:c.fired,then:function(a,b,c){i.done(a).fail(b).progress(c);return this},always:function(){i.done.apply(i,arguments).fail.apply(i,arguments);return this},pipe:function(a,b,c){return f.Deferred(function(d){f.each({done:[a,"resolve"],fail:[b,"reject"],progress:[c,"notify"]},function(a,b){var c=b[0],e=b[1],g;f.isFunction(c)?i[a](function(){g=c.apply(this,arguments),g&&f.isFunction(g.promise)?g.promise().then(d.resolve,d.reject,d.notify):d[e+"With"](this===i?d:this,[g])}):i[a](d[e])})}).promise()},promise:function(a){if(a==null)a=h;else for(var b in h)a[b]=h[b];return a}},i=h.promise({}),j;for(j in g)i[j]=g[j].fire,i[j+"With"]=g[j].fireWith;i.done(function(){e="resolved"},c.disable,d.lock).fail(function(){e="rejected"},b.disable,d.lock),a&&a.call(i,i);return i},when:function(a){function m(a){return function(b){e[a]=arguments.length>1?i.call(arguments,0):b,j.notifyWith(k,e)}}function l(a){return function(c){b[a]=arguments.length>1?i.call(arguments,0):c,--g||j.resolveWith(j,b)}}var b=i.call(arguments,0),c=0,d=b.length,e=Array(d),g=d,h=d,j=d<=1&&a&&f.isFunction(a.promise)?a:f.Deferred(),k=j.promise();if(d>1){for(;c<d;c++)b[c]&&b[c].promise&&f.isFunction(b[c].promise)?b[c].promise().then(l(c),j.reject,m(c)):--g;g||j.resolveWith(j,b)}else j!==a&&j.resolveWith(j,d?[a]:[]);return k}}),f.support=function(){var b,d,e,g,h,i,j,k,l,m,n,o,p=c.createElement("div"),q=c.documentElement;p.setAttribute("className","t"),p.innerHTML="   <link/><table></table><a href='/a' style='top:1px;float:left;opacity:.55;'>a</a><input type='checkbox'/>",d=p.getElementsByTagName("*"),e=p.getElementsByTagName("a")[0];if(!d||!d.length||!e)return{};g=c.createElement("select"),h=g.appendChild(c.createElement("option")),i=p.getElementsByTagName("input")[0],b={leadingWhitespace:p.firstChild.nodeType===3,tbody:!p.getElementsByTagName("tbody").length,htmlSerialize:!!p.getElementsByTagName("link").length,style:/top/.test(e.getAttribute("style")),hrefNormalized:e.getAttribute("href")==="/a",opacity:/^0.55/.test(e.style.opacity),cssFloat:!!e.style.cssFloat,checkOn:i.value==="on",optSelected:h.selected,getSetAttribute:p.className!=="t",enctype:!!c.createElement("form").enctype,html5Clone:c.createElement("nav").cloneNode(!0).outerHTML!=="<:nav></:nav>",submitBubbles:!0,changeBubbles:!0,focusinBubbles:!1,deleteExpando:!0,noCloneEvent:!0,inlineBlockNeedsLayout:!1,shrinkWrapBlocks:!1,reliableMarginRight:!0,pixelMargin:!0},f.boxModel=b.boxModel=c.compatMode==="CSS1Compat",i.checked=!0,b.noCloneChecked=i.cloneNode(!0).checked,g.disabled=!0,b.optDisabled=!h.disabled;try{delete p.test}catch(r){b.deleteExpando=!1}!p.addEventListener&&p.attachEvent&&p.fireEvent&&(p.attachEvent("onclick",function(){b.noCloneEvent=!1}),p.cloneNode(!0).fireEvent("onclick")),i=c.createElement("input"),i.value="t",i.setAttribute("type","radio"),b.radioValue=i.value==="t",i.setAttribute("checked","checked"),i.setAttribute("name","t"),p.appendChild(i),j=c.createDocumentFragment(),j.appendChild(p.lastChild),b.checkClone=j.cloneNode(!0).cloneNode(!0).lastChild.checked,b.appendChecked=i.checked,j.removeChild(i),j.appendChild(p);if(p.attachEvent)for(n in{submit:1,change:1,focusin:1})m="on"+n,o=m in p,o||(p.setAttribute(m,"return;"),o=typeof p[m]=="function"),b[n+"Bubbles"]=o;j.removeChild(p),j=g=h=p=i=null,f(function(){var d,e,g,h,i,j,l,m,n,q,r,s,t,u=c.getElementsByTagName("body")[0];!u||(m=1,t="padding:0;margin:0;border:",r="position:absolute;top:0;left:0;width:1px;height:1px;",s=t+"0;visibility:hidden;",n="style='"+r+t+"5px solid #000;",q="<div "+n+"display:block;'><div style='"+t+"0;display:block;overflow:hidden;'></div></div>"+"<table "+n+"' cellpadding='0' cellspacing='0'>"+"<tr><td></td></tr></table>",d=c.createElement("div"),d.style.cssText=s+"width:0;height:0;position:static;top:0;margin-top:"+m+"px",u.insertBefore(d,u.firstChild),p=c.createElement("div"),d.appendChild(p),p.innerHTML="<table><tr><td style='"+t+"0;display:none'></td><td>t</td></tr></table>",k=p.getElementsByTagName("td"),o=k[0].offsetHeight===0,k[0].style.display="",k[1].style.display="none",b.reliableHiddenOffsets=o&&k[0].offsetHeight===0,a.getComputedStyle&&(p.innerHTML="",l=c.createElement("div"),l.style.width="0",l.style.marginRight="0",p.style.width="2px",p.appendChild(l),b.reliableMarginRight=(parseInt((a.getComputedStyle(l,null)||{marginRight:0}).marginRight,10)||0)===0),typeof p.style.zoom!="undefined"&&(p.innerHTML="",p.style.width=p.style.padding="1px",p.style.border=0,p.style.overflow="hidden",p.style.display="inline",p.style.zoom=1,b.inlineBlockNeedsLayout=p.offsetWidth===3,p.style.display="block",p.style.overflow="visible",p.innerHTML="<div style='width:5px;'></div>",b.shrinkWrapBlocks=p.offsetWidth!==3),p.style.cssText=r+s,p.innerHTML=q,e=p.firstChild,g=e.firstChild,i=e.nextSibling.firstChild.firstChild,j={doesNotAddBorder:g.offsetTop!==5,doesAddBorderForTableAndCells:i.offsetTop===5},g.style.position="fixed",g.style.top="20px",j.fixedPosition=g.offsetTop===20||g.offsetTop===15,g.style.position=g.style.top="",e.style.overflow="hidden",e.style.position="relative",j.subtractsBorderForOverflowNotVisible=g.offsetTop===-5,j.doesNotIncludeMarginInBodyOffset=u.offsetTop!==m,a.getComputedStyle&&(p.style.marginTop="1%",b.pixelMargin=(a.getComputedStyle(p,null)||{marginTop:0}).marginTop!=="1%"),typeof d.style.zoom!="undefined"&&(d.style.zoom=1),u.removeChild(d),l=p=d=null,f.extend(b,j))});return b}();var j=/^(?:\{.*\}|\[.*\])$/,k=/([A-Z])/g;f.extend({cache:{},uuid:0,expando:"jQuery"+(f.fn.jquery+Math.random()).replace(/\D/g,""),noData:{embed:!0,object:"clsid:D27CDB6E-AE6D-11cf-96B8-444553540000",applet:!0},hasData:function(a){a=a.nodeType?f.cache[a[f.expando]]:a[f.expando];return!!a&&!m(a)},data:function(a,c,d,e){if(!!f.acceptData(a)){var g,h,i,j=f.expando,k=typeof c=="string",l=a.nodeType,m=l?f.cache:a,n=l?a[j]:a[j]&&j,o=c==="events";if((!n||!m[n]||!o&&!e&&!m[n].data)&&k&&d===b)return;n||(l?a[j]=n=++f.uuid:n=j),m[n]||(m[n]={},l||(m[n].toJSON=f.noop));if(typeof c=="object"||typeof c=="function")e?m[n]=f.extend(m[n],c):m[n].data=f.extend(m[n].data,c);g=h=m[n],e||(h.data||(h.data={}),h=h.data),d!==b&&(h[f.camelCase(c)]=d);if(o&&!h[c])return g.events;k?(i=h[c],i==null&&(i=h[f.camelCase(c)])):i=h;return i}},removeData:function(a,b,c){if(!!f.acceptData(a)){var d,e,g,h=f.expando,i=a.nodeType,j=i?f.cache:a,k=i?a[h]:h;if(!j[k])return;if(b){d=c?j[k]:j[k].data;if(d){f.isArray(b)||(b in d?b=[b]:(b=f.camelCase(b),b in d?b=[b]:b=b.split(" ")));for(e=0,g=b.length;e<g;e++)delete d[b[e]];if(!(c?m:f.isEmptyObject)(d))return}}if(!c){delete j[k].data;if(!m(j[k]))return}f.support.deleteExpando||!j.setInterval?delete j[k]:j[k]=null,i&&(f.support.deleteExpando?delete a[h]:a.removeAttribute?a.removeAttribute(h):a[h]=null)}},_data:function(a,b,c){return f.data(a,b,c,!0)},acceptData:function(a){if(a.nodeName){var b=f.noData[a.nodeName.toLowerCase()];if(b)return b!==!0&&a.getAttribute("classid")===b}return!0}}),f.fn.extend({data:function(a,c){var d,e,g,h,i,j=this[0],k=0,m=null;if(a===b){if(this.length){m=f.data(j);if(j.nodeType===1&&!f._data(j,"parsedAttrs")){g=j.attributes;for(i=g.length;k<i;k++)h=g[k].name,h.indexOf("data-")===0&&(h=f.camelCase(h.substring(5)),l(j,h,m[h]));f._data(j,"parsedAttrs",!0)}}return m}if(typeof a=="object")return this.each(function(){f.data(this,a)});d=a.split(".",2),d[1]=d[1]?"."+d[1]:"",e=d[1]+"!";return f.access(this,function(c){if(c===b){m=this.triggerHandler("getData"+e,[d[0]]),m===b&&j&&(m=f.data(j,a),m=l(j,a,m));return m===b&&d[1]?this.data(d[0]):m}d[1]=c,this.each(function(){var b=f(this);b.triggerHandler("setData"+e,d),f.data(this,a,c),b.triggerHandler("changeData"+e,d)})},null,c,arguments.length>1,null,!1)},removeData:function(a){return this.each(function(){f.removeData(this,a)})}}),f.extend({_mark:function(a,b){a&&(b=(b||"fx")+"mark",f._data(a,b,(f._data(a,b)||0)+1))},_unmark:function(a,b,c){a!==!0&&(c=b,b=a,a=!1);if(b){c=c||"fx";var d=c+"mark",e=a?0:(f._data(b,d)||1)-1;e?f._data(b,d,e):(f.removeData(b,d,!0),n(b,c,"mark"))}},queue:function(a,b,c){var d;if(a){b=(b||"fx")+"queue",d=f._data(a,b),c&&(!d||f.isArray(c)?d=f._data(a,b,f.makeArray(c)):d.push(c));return d||[]}},dequeue:function(a,b){b=b||"fx";var c=f.queue(a,b),d=c.shift(),e={};d==="inprogress"&&(d=c.shift()),d&&(b==="fx"&&c.unshift("inprogress"),f._data(a,b+".run",e),d.call(a,function(){f.dequeue(a,b)},e)),c.length||(f.removeData(a,b+"queue "+b+".run",!0),n(a,b,"queue"))}}),f.fn.extend({queue:function(a,c){var d=2;typeof a!="string"&&(c=a,a="fx",d--);if(arguments.length<d)return f.queue(this[0],a);return c===b?this:this.each(function(){var b=f.queue(this,a,c);a==="fx"&&b[0]!=="inprogress"&&f.dequeue(this,a)})},dequeue:function(a){return this.each(function(){f.dequeue(this,a)})},delay:function(a,b){a=f.fx?f.fx.speeds[a]||a:a,b=b||"fx";return this.queue(b,function(b,c){var d=setTimeout(b,a);c.stop=function(){clearTimeout(d)}})},clearQueue:function(a){return this.queue(a||"fx",[])},promise:function(a,c){function m(){--h||d.resolveWith(e,[e])}typeof a!="string"&&(c=a,a=b),a=a||"fx";var d=f.Deferred(),e=this,g=e.length,h=1,i=a+"defer",j=a+"queue",k=a+"mark",l;while(g--)if(l=f.data(e[g],i,b,!0)||(f.data(e[g],j,b,!0)||f.data(e[g],k,b,!0))&&f.data(e[g],i,f.Callbacks("once memory"),!0))h++,l.add(m);m();return d.promise(c)}});var o=/[\n\t\r]/g,p=/\s+/,q=/\r/g,r=/^(?:button|input)$/i,s=/^(?:button|input|object|select|textarea)$/i,t=/^a(?:rea)?$/i,u=/^(?:autofocus|autoplay|async|checked|controls|defer|disabled|hidden|loop|multiple|open|readonly|required|scoped|selected)$/i,v=f.support.getSetAttribute,w,x,y;f.fn.extend({attr:function(a,b){return f.access(this,f.attr,a,b,arguments.length>1)},removeAttr:function(a){return this.each(function(){f.removeAttr(this,a)})},prop:function(a,b){return f.access(this,f.prop,a,b,arguments.length>1)},removeProp:function(a){a=f.propFix[a]||a;return this.each(function(){try{this[a]=b,delete this[a]}catch(c){}})},addClass:function(a){var b,c,d,e,g,h,i;if(f.isFunction(a))return this.each(function(b){f(this).addClass(a.call(this,b,this.className))});if(a&&typeof a=="string"){b=a.split(p);for(c=0,d=this.length;c<d;c++){e=this[c];if(e.nodeType===1)if(!e.className&&b.length===1)e.className=a;else{g=" "+e.className+" ";for(h=0,i=b.length;h<i;h++)~g.indexOf(" "+b[h]+" ")||(g+=b[h]+" ");e.className=f.trim(g)}}}return this},removeClass:function(a){var c,d,e,g,h,i,j;if(f.isFunction(a))return this.each(function(b){f(this).removeClass(a.call(this,b,this.className))});if(a&&typeof a=="string"||a===b){c=(a||"").split(p);for(d=0,e=this.length;d<e;d++){g=this[d];if(g.nodeType===1&&g.className)if(a){h=(" "+g.className+" ").replace(o," ");for(i=0,j=c.length;i<j;i++)h=h.replace(" "+c[i]+" "," ");g.className=f.trim(h)}else g.className=""}}return this},toggleClass:function(a,b){var c=typeof a,d=typeof b=="boolean";if(f.isFunction(a))return this.each(function(c){f(this).toggleClass(a.call(this,c,this.className,b),b)});return this.each(function(){if(c==="string"){var e,g=0,h=f(this),i=b,j=a.split(p);while(e=j[g++])i=d?i:!h.hasClass(e),h[i?"addClass":"removeClass"](e)}else if(c==="undefined"||c==="boolean")this.className&&f._data(this,"__className__",this.className),this.className=this.className||a===!1?"":f._data(this,"__className__")||""})},hasClass:function(a){var b=" "+a+" ",c=0,d=this.length;for(;c<d;c++)if(this[c].nodeType===1&&(" "+this[c].className+" ").replace(o," ").indexOf(b)>-1)return!0;return!1},val:function(a){var c,d,e,g=this[0];{if(!!arguments.length){e=f.isFunction(a);return this.each(function(d){var g=f(this),h;if(this.nodeType===1){e?h=a.call(this,d,g.val()):h=a,h==null?h="":typeof h=="number"?h+="":f.isArray(h)&&(h=f.map(h,function(a){return a==null?"":a+""})),c=f.valHooks[this.type]||f.valHooks[this.nodeName.toLowerCase()];if(!c||!("set"in c)||c.set(this,h,"value")===b)this.value=h}})}if(g){c=f.valHooks[g.type]||f.valHooks[g.nodeName.toLowerCase()];if(c&&"get"in c&&(d=c.get(g,"value"))!==b)return d;d=g.value;return typeof d=="string"?d.replace(q,""):d==null?"":d}}}}),f.extend({valHooks:{option:{get:function(a){var b=a.attributes.value;return!b||b.specified?a.value:a.text}},select:{get:function(a){var b,c,d,e,g=a.selectedIndex,h=[],i=a.options,j=a.type==="select-one";if(g<0)return null;c=j?g:0,d=j?g+1:i.length;for(;c<d;c++){e=i[c];if(e.selected&&(f.support.optDisabled?!e.disabled:e.getAttribute("disabled")===null)&&(!e.parentNode.disabled||!f.nodeName(e.parentNode,"optgroup"))){b=f(e).val();if(j)return b;h.push(b)}}if(j&&!h.length&&i.length)return f(i[g]).val();return h},set:function(a,b){var c=f.makeArray(b);f(a).find("option").each(function(){this.selected=f.inArray(f(this).val(),c)>=0}),c.length||(a.selectedIndex=-1);return c}}},attrFn:{val:!0,css:!0,html:!0,text:!0,data:!0,width:!0,height:!0,offset:!0},attr:function(a,c,d,e){var g,h,i,j=a.nodeType;if(!!a&&j!==3&&j!==8&&j!==2){if(e&&c in f.attrFn)return f(a)[c](d);if(typeof a.getAttribute=="undefined")return f.prop(a,c,d);i=j!==1||!f.isXMLDoc(a),i&&(c=c.toLowerCase(),h=f.attrHooks[c]||(u.test(c)?x:w));if(d!==b){if(d===null){f.removeAttr(a,c);return}if(h&&"set"in h&&i&&(g=h.set(a,d,c))!==b)return g;a.setAttribute(c,""+d);return d}if(h&&"get"in h&&i&&(g=h.get(a,c))!==null)return g;g=a.getAttribute(c);return g===null?b:g}},removeAttr:function(a,b){var c,d,e,g,h,i=0;if(b&&a.nodeType===1){d=b.toLowerCase().split(p),g=d.length;for(;i<g;i++)e=d[i],e&&(c=f.propFix[e]||e,h=u.test(e),h||f.attr(a,e,""),a.removeAttribute(v?e:c),h&&c in a&&(a[c]=!1))}},attrHooks:{type:{set:function(a,b){if(r.test(a.nodeName)&&a.parentNode)f.error("type property can't be changed");else if(!f.support.radioValue&&b==="radio"&&f.nodeName(a,"input")){var c=a.value;a.setAttribute("type",b),c&&(a.value=c);return b}}},value:{get:function(a,b){if(w&&f.nodeName(a,"button"))return w.get(a,b);return b in a?a.value:null},set:function(a,b,c){if(w&&f.nodeName(a,"button"))return w.set(a,b,c);a.value=b}}},propFix:{tabindex:"tabIndex",readonly:"readOnly","for":"htmlFor","class":"className",maxlength:"maxLength",cellspacing:"cellSpacing",cellpadding:"cellPadding",rowspan:"rowSpan",colspan:"colSpan",usemap:"useMap",frameborder:"frameBorder",contenteditable:"contentEditable"},prop:function(a,c,d){var e,g,h,i=a.nodeType;if(!!a&&i!==3&&i!==8&&i!==2){h=i!==1||!f.isXMLDoc(a),h&&(c=f.propFix[c]||c,g=f.propHooks[c]);return d!==b?g&&"set"in g&&(e=g.set(a,d,c))!==b?e:a[c]=d:g&&"get"in g&&(e=g.get(a,c))!==null?e:a[c]}},propHooks:{tabIndex:{get:function(a){var c=a.getAttributeNode("tabindex");return c&&c.specified?parseInt(c.value,10):s.test(a.nodeName)||t.test(a.nodeName)&&a.href?0:b}}}}),f.attrHooks.tabindex=f.propHooks.tabIndex,x={get:function(a,c){var d,e=f.prop(a,c);return e===!0||typeof e!="boolean"&&(d=a.getAttributeNode(c))&&d.nodeValue!==!1?c.toLowerCase():b},set:function(a,b,c){var d;b===!1?f.removeAttr(a,c):(d=f.propFix[c]||c,d in a&&(a[d]=!0),a.setAttribute(c,c.toLowerCase()));return c}},v||(y={name:!0,id:!0,coords:!0},w=f.valHooks.button={get:function(a,c){var d;d=a.getAttributeNode(c);return d&&(y[c]?d.nodeValue!=="":d.specified)?d.nodeValue:b},set:function(a,b,d){var e=a.getAttributeNode(d);e||(e=c.createAttribute(d),a.setAttributeNode(e));return e.nodeValue=b+""}},f.attrHooks.tabindex.set=w.set,f.each(["width","height"],function(a,b){f.attrHooks[b]=f.extend(f.attrHooks[b],{set:function(a,c){if(c===""){a.setAttribute(b,"auto");return c}}})}),f.attrHooks.contenteditable={get:w.get,set:function(a,b,c){b===""&&(b="false"),w.set(a,b,c)}}),f.support.hrefNormalized||f.each(["href","src","width","height"],function(a,c){f.attrHooks[c]=f.extend(f.attrHooks[c],{get:function(a){var d=a.getAttribute(c,2);return d===null?b:d}})}),f.support.style||(f.attrHooks.style={get:function(a){return a.style.cssText.toLowerCase()||b},set:function(a,b){return a.style.cssText=""+b}}),f.support.optSelected||(f.propHooks.selected=f.extend(f.propHooks.selected,{get:function(a){var b=a.parentNode;b&&(b.selectedIndex,b.parentNode&&b.parentNode.selectedIndex);return null}})),f.support.enctype||(f.propFix.enctype="encoding"),f.support.checkOn||f.each(["radio","checkbox"],function(){f.valHooks[this]={get:function(a){return a.getAttribute("value")===null?"on":a.value}}}),f.each(["radio","checkbox"],function(){f.valHooks[this]=f.extend(f.valHooks[this],{set:function(a,b){if(f.isArray(b))return a.checked=f.inArray(f(a).val(),b)>=0}})});var z=/^(?:textarea|input|select)$/i,A=/^([^\.]*)?(?:\.(.+))?$/,B=/(?:^|\s)hover(\.\S+)?\b/,C=/^key/,D=/^(?:mouse|contextmenu)|click/,E=/^(?:focusinfocus|focusoutblur)$/,F=/^(\w*)(?:#([\w\-]+))?(?:\.([\w\-]+))?$/,G=function(
a){var b=F.exec(a);b&&(b[1]=(b[1]||"").toLowerCase(),b[3]=b[3]&&new RegExp("(?:^|\\s)"+b[3]+"(?:\\s|$)"));return b},H=function(a,b){var c=a.attributes||{};return(!b[1]||a.nodeName.toLowerCase()===b[1])&&(!b[2]||(c.id||{}).value===b[2])&&(!b[3]||b[3].test((c["class"]||{}).value))},I=function(a){return f.event.special.hover?a:a.replace(B,"mouseenter$1 mouseleave$1")};f.event={add:function(a,c,d,e,g){var h,i,j,k,l,m,n,o,p,q,r,s;if(!(a.nodeType===3||a.nodeType===8||!c||!d||!(h=f._data(a)))){d.handler&&(p=d,d=p.handler,g=p.selector),d.guid||(d.guid=f.guid++),j=h.events,j||(h.events=j={}),i=h.handle,i||(h.handle=i=function(a){return typeof f!="undefined"&&(!a||f.event.triggered!==a.type)?f.event.dispatch.apply(i.elem,arguments):b},i.elem=a),c=f.trim(I(c)).split(" ");for(k=0;k<c.length;k++){l=A.exec(c[k])||[],m=l[1],n=(l[2]||"").split(".").sort(),s=f.event.special[m]||{},m=(g?s.delegateType:s.bindType)||m,s=f.event.special[m]||{},o=f.extend({type:m,origType:l[1],data:e,handler:d,guid:d.guid,selector:g,quick:g&&G(g),namespace:n.join(".")},p),r=j[m];if(!r){r=j[m]=[],r.delegateCount=0;if(!s.setup||s.setup.call(a,e,n,i)===!1)a.addEventListener?a.addEventListener(m,i,!1):a.attachEvent&&a.attachEvent("on"+m,i)}s.add&&(s.add.call(a,o),o.handler.guid||(o.handler.guid=d.guid)),g?r.splice(r.delegateCount++,0,o):r.push(o),f.event.global[m]=!0}a=null}},global:{},remove:function(a,b,c,d,e){var g=f.hasData(a)&&f._data(a),h,i,j,k,l,m,n,o,p,q,r,s;if(!!g&&!!(o=g.events)){b=f.trim(I(b||"")).split(" ");for(h=0;h<b.length;h++){i=A.exec(b[h])||[],j=k=i[1],l=i[2];if(!j){for(j in o)f.event.remove(a,j+b[h],c,d,!0);continue}p=f.event.special[j]||{},j=(d?p.delegateType:p.bindType)||j,r=o[j]||[],m=r.length,l=l?new RegExp("(^|\\.)"+l.split(".").sort().join("\\.(?:.*\\.)?")+"(\\.|$)"):null;for(n=0;n<r.length;n++)s=r[n],(e||k===s.origType)&&(!c||c.guid===s.guid)&&(!l||l.test(s.namespace))&&(!d||d===s.selector||d==="**"&&s.selector)&&(r.splice(n--,1),s.selector&&r.delegateCount--,p.remove&&p.remove.call(a,s));r.length===0&&m!==r.length&&((!p.teardown||p.teardown.call(a,l)===!1)&&f.removeEvent(a,j,g.handle),delete o[j])}f.isEmptyObject(o)&&(q=g.handle,q&&(q.elem=null),f.removeData(a,["events","handle"],!0))}},customEvent:{getData:!0,setData:!0,changeData:!0},trigger:function(c,d,e,g){if(!e||e.nodeType!==3&&e.nodeType!==8){var h=c.type||c,i=[],j,k,l,m,n,o,p,q,r,s;if(E.test(h+f.event.triggered))return;h.indexOf("!")>=0&&(h=h.slice(0,-1),k=!0),h.indexOf(".")>=0&&(i=h.split("."),h=i.shift(),i.sort());if((!e||f.event.customEvent[h])&&!f.event.global[h])return;c=typeof c=="object"?c[f.expando]?c:new f.Event(h,c):new f.Event(h),c.type=h,c.isTrigger=!0,c.exclusive=k,c.namespace=i.join("."),c.namespace_re=c.namespace?new RegExp("(^|\\.)"+i.join("\\.(?:.*\\.)?")+"(\\.|$)"):null,o=h.indexOf(":")<0?"on"+h:"";if(!e){j=f.cache;for(l in j)j[l].events&&j[l].events[h]&&f.event.trigger(c,d,j[l].handle.elem,!0);return}c.result=b,c.target||(c.target=e),d=d!=null?f.makeArray(d):[],d.unshift(c),p=f.event.special[h]||{};if(p.trigger&&p.trigger.apply(e,d)===!1)return;r=[[e,p.bindType||h]];if(!g&&!p.noBubble&&!f.isWindow(e)){s=p.delegateType||h,m=E.test(s+h)?e:e.parentNode,n=null;for(;m;m=m.parentNode)r.push([m,s]),n=m;n&&n===e.ownerDocument&&r.push([n.defaultView||n.parentWindow||a,s])}for(l=0;l<r.length&&!c.isPropagationStopped();l++)m=r[l][0],c.type=r[l][1],q=(f._data(m,"events")||{})[c.type]&&f._data(m,"handle"),q&&q.apply(m,d),q=o&&m[o],q&&f.acceptData(m)&&q.apply(m,d)===!1&&c.preventDefault();c.type=h,!g&&!c.isDefaultPrevented()&&(!p._default||p._default.apply(e.ownerDocument,d)===!1)&&(h!=="click"||!f.nodeName(e,"a"))&&f.acceptData(e)&&o&&e[h]&&(h!=="focus"&&h!=="blur"||c.target.offsetWidth!==0)&&!f.isWindow(e)&&(n=e[o],n&&(e[o]=null),f.event.triggered=h,e[h](),f.event.triggered=b,n&&(e[o]=n));return c.result}},dispatch:function(c){c=f.event.fix(c||a.event);var d=(f._data(this,"events")||{})[c.type]||[],e=d.delegateCount,g=[].slice.call(arguments,0),h=!c.exclusive&&!c.namespace,i=f.event.special[c.type]||{},j=[],k,l,m,n,o,p,q,r,s,t,u;g[0]=c,c.delegateTarget=this;if(!i.preDispatch||i.preDispatch.call(this,c)!==!1){if(e&&(!c.button||c.type!=="click")){n=f(this),n.context=this.ownerDocument||this;for(m=c.target;m!=this;m=m.parentNode||this)if(m.disabled!==!0){p={},r=[],n[0]=m;for(k=0;k<e;k++)s=d[k],t=s.selector,p[t]===b&&(p[t]=s.quick?H(m,s.quick):n.is(t)),p[t]&&r.push(s);r.length&&j.push({elem:m,matches:r})}}d.length>e&&j.push({elem:this,matches:d.slice(e)});for(k=0;k<j.length&&!c.isPropagationStopped();k++){q=j[k],c.currentTarget=q.elem;for(l=0;l<q.matches.length&&!c.isImmediatePropagationStopped();l++){s=q.matches[l];if(h||!c.namespace&&!s.namespace||c.namespace_re&&c.namespace_re.test(s.namespace))c.data=s.data,c.handleObj=s,o=((f.event.special[s.origType]||{}).handle||s.handler).apply(q.elem,g),o!==b&&(c.result=o,o===!1&&(c.preventDefault(),c.stopPropagation()))}}i.postDispatch&&i.postDispatch.call(this,c);return c.result}},props:"attrChange attrName relatedNode srcElement altKey bubbles cancelable ctrlKey currentTarget eventPhase metaKey relatedTarget shiftKey target timeStamp view which".split(" "),fixHooks:{},keyHooks:{props:"char charCode key keyCode".split(" "),filter:function(a,b){a.which==null&&(a.which=b.charCode!=null?b.charCode:b.keyCode);return a}},mouseHooks:{props:"button buttons clientX clientY fromElement offsetX offsetY pageX pageY screenX screenY toElement".split(" "),filter:function(a,d){var e,f,g,h=d.button,i=d.fromElement;a.pageX==null&&d.clientX!=null&&(e=a.target.ownerDocument||c,f=e.documentElement,g=e.body,a.pageX=d.clientX+(f&&f.scrollLeft||g&&g.scrollLeft||0)-(f&&f.clientLeft||g&&g.clientLeft||0),a.pageY=d.clientY+(f&&f.scrollTop||g&&g.scrollTop||0)-(f&&f.clientTop||g&&g.clientTop||0)),!a.relatedTarget&&i&&(a.relatedTarget=i===a.target?d.toElement:i),!a.which&&h!==b&&(a.which=h&1?1:h&2?3:h&4?2:0);return a}},fix:function(a){if(a[f.expando])return a;var d,e,g=a,h=f.event.fixHooks[a.type]||{},i=h.props?this.props.concat(h.props):this.props;a=f.Event(g);for(d=i.length;d;)e=i[--d],a[e]=g[e];a.target||(a.target=g.srcElement||c),a.target.nodeType===3&&(a.target=a.target.parentNode),a.metaKey===b&&(a.metaKey=a.ctrlKey);return h.filter?h.filter(a,g):a},special:{ready:{setup:f.bindReady},load:{noBubble:!0},focus:{delegateType:"focusin"},blur:{delegateType:"focusout"},beforeunload:{setup:function(a,b,c){f.isWindow(this)&&(this.onbeforeunload=c)},teardown:function(a,b){this.onbeforeunload===b&&(this.onbeforeunload=null)}}},simulate:function(a,b,c,d){var e=f.extend(new f.Event,c,{type:a,isSimulated:!0,originalEvent:{}});d?f.event.trigger(e,null,b):f.event.dispatch.call(b,e),e.isDefaultPrevented()&&c.preventDefault()}},f.event.handle=f.event.dispatch,f.removeEvent=c.removeEventListener?function(a,b,c){a.removeEventListener&&a.removeEventListener(b,c,!1)}:function(a,b,c){a.detachEvent&&a.detachEvent("on"+b,c)},f.Event=function(a,b){if(!(this instanceof f.Event))return new f.Event(a,b);a&&a.type?(this.originalEvent=a,this.type=a.type,this.isDefaultPrevented=a.defaultPrevented||a.returnValue===!1||a.getPreventDefault&&a.getPreventDefault()?K:J):this.type=a,b&&f.extend(this,b),this.timeStamp=a&&a.timeStamp||f.now(),this[f.expando]=!0},f.Event.prototype={preventDefault:function(){this.isDefaultPrevented=K;var a=this.originalEvent;!a||(a.preventDefault?a.preventDefault():a.returnValue=!1)},stopPropagation:function(){this.isPropagationStopped=K;var a=this.originalEvent;!a||(a.stopPropagation&&a.stopPropagation(),a.cancelBubble=!0)},stopImmediatePropagation:function(){this.isImmediatePropagationStopped=K,this.stopPropagation()},isDefaultPrevented:J,isPropagationStopped:J,isImmediatePropagationStopped:J},f.each({mouseenter:"mouseover",mouseleave:"mouseout"},function(a,b){f.event.special[a]={delegateType:b,bindType:b,handle:function(a){var c=this,d=a.relatedTarget,e=a.handleObj,g=e.selector,h;if(!d||d!==c&&!f.contains(c,d))a.type=e.origType,h=e.handler.apply(this,arguments),a.type=b;return h}}}),f.support.submitBubbles||(f.event.special.submit={setup:function(){if(f.nodeName(this,"form"))return!1;f.event.add(this,"click._submit keypress._submit",function(a){var c=a.target,d=f.nodeName(c,"input")||f.nodeName(c,"button")?c.form:b;d&&!d._submit_attached&&(f.event.add(d,"submit._submit",function(a){a._submit_bubble=!0}),d._submit_attached=!0)})},postDispatch:function(a){a._submit_bubble&&(delete a._submit_bubble,this.parentNode&&!a.isTrigger&&f.event.simulate("submit",this.parentNode,a,!0))},teardown:function(){if(f.nodeName(this,"form"))return!1;f.event.remove(this,"._submit")}}),f.support.changeBubbles||(f.event.special.change={setup:function(){if(z.test(this.nodeName)){if(this.type==="checkbox"||this.type==="radio")f.event.add(this,"propertychange._change",function(a){a.originalEvent.propertyName==="checked"&&(this._just_changed=!0)}),f.event.add(this,"click._change",function(a){this._just_changed&&!a.isTrigger&&(this._just_changed=!1,f.event.simulate("change",this,a,!0))});return!1}f.event.add(this,"beforeactivate._change",function(a){var b=a.target;z.test(b.nodeName)&&!b._change_attached&&(f.event.add(b,"change._change",function(a){this.parentNode&&!a.isSimulated&&!a.isTrigger&&f.event.simulate("change",this.parentNode,a,!0)}),b._change_attached=!0)})},handle:function(a){var b=a.target;if(this!==b||a.isSimulated||a.isTrigger||b.type!=="radio"&&b.type!=="checkbox")return a.handleObj.handler.apply(this,arguments)},teardown:function(){f.event.remove(this,"._change");return z.test(this.nodeName)}}),f.support.focusinBubbles||f.each({focus:"focusin",blur:"focusout"},function(a,b){var d=0,e=function(a){f.event.simulate(b,a.target,f.event.fix(a),!0)};f.event.special[b]={setup:function(){d++===0&&c.addEventListener(a,e,!0)},teardown:function(){--d===0&&c.removeEventListener(a,e,!0)}}}),f.fn.extend({on:function(a,c,d,e,g){var h,i;if(typeof a=="object"){typeof c!="string"&&(d=d||c,c=b);for(i in a)this.on(i,c,d,a[i],g);return this}d==null&&e==null?(e=c,d=c=b):e==null&&(typeof c=="string"?(e=d,d=b):(e=d,d=c,c=b));if(e===!1)e=J;else if(!e)return this;g===1&&(h=e,e=function(a){f().off(a);return h.apply(this,arguments)},e.guid=h.guid||(h.guid=f.guid++));return this.each(function(){f.event.add(this,a,e,d,c)})},one:function(a,b,c,d){return this.on(a,b,c,d,1)},off:function(a,c,d){if(a&&a.preventDefault&&a.handleObj){var e=a.handleObj;f(a.delegateTarget).off(e.namespace?e.origType+"."+e.namespace:e.origType,e.selector,e.handler);return this}if(typeof a=="object"){for(var g in a)this.off(g,c,a[g]);return this}if(c===!1||typeof c=="function")d=c,c=b;d===!1&&(d=J);return this.each(function(){f.event.remove(this,a,d,c)})},bind:function(a,b,c){return this.on(a,null,b,c)},unbind:function(a,b){return this.off(a,null,b)},live:function(a,b,c){f(this.context).on(a,this.selector,b,c);return this},die:function(a,b){f(this.context).off(a,this.selector||"**",b);return this},delegate:function(a,b,c,d){return this.on(b,a,c,d)},undelegate:function(a,b,c){return arguments.length==1?this.off(a,"**"):this.off(b,a,c)},trigger:function(a,b){return this.each(function(){f.event.trigger(a,b,this)})},triggerHandler:function(a,b){if(this[0])return f.event.trigger(a,b,this[0],!0)},toggle:function(a){var b=arguments,c=a.guid||f.guid++,d=0,e=function(c){var e=(f._data(this,"lastToggle"+a.guid)||0)%d;f._data(this,"lastToggle"+a.guid,e+1),c.preventDefault();return b[e].apply(this,arguments)||!1};e.guid=c;while(d<b.length)b[d++].guid=c;return this.click(e)},hover:function(a,b){return this.mouseenter(a).mouseleave(b||a)}}),f.each("blur focus focusin focusout load resize scroll unload click dblclick mousedown mouseup mousemove mouseover mouseout mouseenter mouseleave change select submit keydown keypress keyup error contextmenu".split(" "),function(a,b){f.fn[b]=function(a,c){c==null&&(c=a,a=null);return arguments.length>0?this.on(b,null,a,c):this.trigger(b)},f.attrFn&&(f.attrFn[b]=!0),C.test(b)&&(f.event.fixHooks[b]=f.event.keyHooks),D.test(b)&&(f.event.fixHooks[b]=f.event.mouseHooks)}),function(){function x(a,b,c,e,f,g){for(var h=0,i=e.length;h<i;h++){var j=e[h];if(j){var k=!1;j=j[a];while(j){if(j[d]===c){k=e[j.sizset];break}if(j.nodeType===1){g||(j[d]=c,j.sizset=h);if(typeof b!="string"){if(j===b){k=!0;break}}else if(m.filter(b,[j]).length>0){k=j;break}}j=j[a]}e[h]=k}}}function w(a,b,c,e,f,g){for(var h=0,i=e.length;h<i;h++){var j=e[h];if(j){var k=!1;j=j[a];while(j){if(j[d]===c){k=e[j.sizset];break}j.nodeType===1&&!g&&(j[d]=c,j.sizset=h);if(j.nodeName.toLowerCase()===b){k=j;break}j=j[a]}e[h]=k}}}var a=/((?:\((?:\([^()]+\)|[^()]+)+\)|\[(?:\[[^\[\]]*\]|['"][^'"]*['"]|[^\[\]'"]+)+\]|\\.|[^ >+~,(\[\\]+)+|[>+~])(\s*,\s*)?((?:.|\r|\n)*)/g,d="sizcache"+(Math.random()+"").replace(".",""),e=0,g=Object.prototype.toString,h=!1,i=!0,j=/\\/g,k=/\r\n/g,l=/\W/;[0,0].sort(function(){i=!1;return 0});var m=function(b,d,e,f){e=e||[],d=d||c;var h=d;if(d.nodeType!==1&&d.nodeType!==9)return[];if(!b||typeof b!="string")return e;var i,j,k,l,n,q,r,t,u=!0,v=m.isXML(d),w=[],x=b;do{a.exec(""),i=a.exec(x);if(i){x=i[3],w.push(i[1]);if(i[2]){l=i[3];break}}}while(i);if(w.length>1&&p.exec(b))if(w.length===2&&o.relative[w[0]])j=y(w[0]+w[1],d,f);else{j=o.relative[w[0]]?[d]:m(w.shift(),d);while(w.length)b=w.shift(),o.relative[b]&&(b+=w.shift()),j=y(b,j,f)}else{!f&&w.length>1&&d.nodeType===9&&!v&&o.match.ID.test(w[0])&&!o.match.ID.test(w[w.length-1])&&(n=m.find(w.shift(),d,v),d=n.expr?m.filter(n.expr,n.set)[0]:n.set[0]);if(d){n=f?{expr:w.pop(),set:s(f)}:m.find(w.pop(),w.length===1&&(w[0]==="~"||w[0]==="+")&&d.parentNode?d.parentNode:d,v),j=n.expr?m.filter(n.expr,n.set):n.set,w.length>0?k=s(j):u=!1;while(w.length)q=w.pop(),r=q,o.relative[q]?r=w.pop():q="",r==null&&(r=d),o.relative[q](k,r,v)}else k=w=[]}k||(k=j),k||m.error(q||b);if(g.call(k)==="[object Array]")if(!u)e.push.apply(e,k);else if(d&&d.nodeType===1)for(t=0;k[t]!=null;t++)k[t]&&(k[t]===!0||k[t].nodeType===1&&m.contains(d,k[t]))&&e.push(j[t]);else for(t=0;k[t]!=null;t++)k[t]&&k[t].nodeType===1&&e.push(j[t]);else s(k,e);l&&(m(l,h,e,f),m.uniqueSort(e));return e};m.uniqueSort=function(a){if(u){h=i,a.sort(u);if(h)for(var b=1;b<a.length;b++)a[b]===a[b-1]&&a.splice(b--,1)}return a},m.matches=function(a,b){return m(a,null,null,b)},m.matchesSelector=function(a,b){return m(b,null,null,[a]).length>0},m.find=function(a,b,c){var d,e,f,g,h,i;if(!a)return[];for(e=0,f=o.order.length;e<f;e++){h=o.order[e];if(g=o.leftMatch[h].exec(a)){i=g[1],g.splice(1,1);if(i.substr(i.length-1)!=="\\"){g[1]=(g[1]||"").replace(j,""),d=o.find[h](g,b,c);if(d!=null){a=a.replace(o.match[h],"");break}}}}d||(d=typeof b.getElementsByTagName!="undefined"?b.getElementsByTagName("*"):[]);return{set:d,expr:a}},m.filter=function(a,c,d,e){var f,g,h,i,j,k,l,n,p,q=a,r=[],s=c,t=c&&c[0]&&m.isXML(c[0]);while(a&&c.length){for(h in o.filter)if((f=o.leftMatch[h].exec(a))!=null&&f[2]){k=o.filter[h],l=f[1],g=!1,f.splice(1,1);if(l.substr(l.length-1)==="\\")continue;s===r&&(r=[]);if(o.preFilter[h]){f=o.preFilter[h](f,s,d,r,e,t);if(!f)g=i=!0;else if(f===!0)continue}if(f)for(n=0;(j=s[n])!=null;n++)j&&(i=k(j,f,n,s),p=e^i,d&&i!=null?p?g=!0:s[n]=!1:p&&(r.push(j),g=!0));if(i!==b){d||(s=r),a=a.replace(o.match[h],"");if(!g)return[];break}}if(a===q)if(g==null)m.error(a);else break;q=a}return s},m.error=function(a){throw new Error("Syntax error, unrecognized expression: "+a)};var n=m.getText=function(a){var b,c,d=a.nodeType,e="";if(d){if(d===1||d===9||d===11){if(typeof a.textContent=="string")return a.textContent;if(typeof a.innerText=="string")return a.innerText.replace(k,"");for(a=a.firstChild;a;a=a.nextSibling)e+=n(a)}else if(d===3||d===4)return a.nodeValue}else for(b=0;c=a[b];b++)c.nodeType!==8&&(e+=n(c));return e},o=m.selectors={order:["ID","NAME","TAG"],match:{ID:/#((?:[\w\u00c0-\uFFFF\-]|\\.)+)/,CLASS:/\.((?:[\w\u00c0-\uFFFF\-]|\\.)+)/,NAME:/\[name=['"]*((?:[\w\u00c0-\uFFFF\-]|\\.)+)['"]*\]/,ATTR:/\[\s*((?:[\w\u00c0-\uFFFF\-]|\\.)+)\s*(?:(\S?=)\s*(?:(['"])(.*?)\3|(#?(?:[\w\u00c0-\uFFFF\-]|\\.)*)|)|)\s*\]/,TAG:/^((?:[\w\u00c0-\uFFFF\*\-]|\\.)+)/,CHILD:/:(only|nth|last|first)-child(?:\(\s*(even|odd|(?:[+\-]?\d+|(?:[+\-]?\d*)?n\s*(?:[+\-]\s*\d+)?))\s*\))?/,POS:/:(nth|eq|gt|lt|first|last|even|odd)(?:\((\d*)\))?(?=[^\-]|$)/,PSEUDO:/:((?:[\w\u00c0-\uFFFF\-]|\\.)+)(?:\((['"]?)((?:\([^\)]+\)|[^\(\)]*)+)\2\))?/},leftMatch:{},attrMap:{"class":"className","for":"htmlFor"},attrHandle:{href:function(a){return a.getAttribute("href")},type:function(a){return a.getAttribute("type")}},relative:{"+":function(a,b){var c=typeof b=="string",d=c&&!l.test(b),e=c&&!d;d&&(b=b.toLowerCase());for(var f=0,g=a.length,h;f<g;f++)if(h=a[f]){while((h=h.previousSibling)&&h.nodeType!==1);a[f]=e||h&&h.nodeName.toLowerCase()===b?h||!1:h===b}e&&m.filter(b,a,!0)},">":function(a,b){var c,d=typeof b=="string",e=0,f=a.length;if(d&&!l.test(b)){b=b.toLowerCase();for(;e<f;e++){c=a[e];if(c){var g=c.parentNode;a[e]=g.nodeName.toLowerCase()===b?g:!1}}}else{for(;e<f;e++)c=a[e],c&&(a[e]=d?c.parentNode:c.parentNode===b);d&&m.filter(b,a,!0)}},"":function(a,b,c){var d,f=e++,g=x;typeof b=="string"&&!l.test(b)&&(b=b.toLowerCase(),d=b,g=w),g("parentNode",b,f,a,d,c)},"~":function(a,b,c){var d,f=e++,g=x;typeof b=="string"&&!l.test(b)&&(b=b.toLowerCase(),d=b,g=w),g("previousSibling",b,f,a,d,c)}},find:{ID:function(a,b,c){if(typeof b.getElementById!="undefined"&&!c){var d=b.getElementById(a[1]);return d&&d.parentNode?[d]:[]}},NAME:function(a,b){if(typeof b.getElementsByName!="undefined"){var c=[],d=b.getElementsByName(a[1]);for(var e=0,f=d.length;e<f;e++)d[e].getAttribute("name")===a[1]&&c.push(d[e]);return c.length===0?null:c}},TAG:function(a,b){if(typeof b.getElementsByTagName!="undefined")return b.getElementsByTagName(a[1])}},preFilter:{CLASS:function(a,b,c,d,e,f){a=" "+a[1].replace(j,"")+" ";if(f)return a;for(var g=0,h;(h=b[g])!=null;g++)h&&(e^(h.className&&(" "+h.className+" ").replace(/[\t\n\r]/g," ").indexOf(a)>=0)?c||d.push(h):c&&(b[g]=!1));return!1},ID:function(a){return a[1].replace(j,"")},TAG:function(a,b){return a[1].replace(j,"").toLowerCase()},CHILD:function(a){if(a[1]==="nth"){a[2]||m.error(a[0]),a[2]=a[2].replace(/^\+|\s*/g,"");var b=/(-?)(\d*)(?:n([+\-]?\d*))?/.exec(a[2]==="even"&&"2n"||a[2]==="odd"&&"2n+1"||!/\D/.test(a[2])&&"0n+"+a[2]||a[2]);a[2]=b[1]+(b[2]||1)-0,a[3]=b[3]-0}else a[2]&&m.error(a[0]);a[0]=e++;return a},ATTR:function(a,b,c,d,e,f){var g=a[1]=a[1].replace(j,"");!f&&o.attrMap[g]&&(a[1]=o.attrMap[g]),a[4]=(a[4]||a[5]||"").replace(j,""),a[2]==="~="&&(a[4]=" "+a[4]+" ");return a},PSEUDO:function(b,c,d,e,f){if(b[1]==="not")if((a.exec(b[3])||"").length>1||/^\w/.test(b[3]))b[3]=m(b[3],null,null,c);else{var g=m.filter(b[3],c,d,!0^f);d||e.push.apply(e,g);return!1}else if(o.match.POS.test(b[0])||o.match.CHILD.test(b[0]))return!0;return b},POS:function(a){a.unshift(!0);return a}},filters:{enabled:function(a){return a.disabled===!1&&a.type!=="hidden"},disabled:function(a){return a.disabled===!0},checked:function(a){return a.checked===!0},selected:function(a){a.parentNode&&a.parentNode.selectedIndex;return a.selected===!0},parent:function(a){return!!a.firstChild},empty:function(a){return!a.firstChild},has:function(a,b,c){return!!m(c[3],a).length},header:function(a){return/h\d/i.test(a.nodeName)},text:function(a){var b=a.getAttribute("type"),c=a.type;return a.nodeName.toLowerCase()==="input"&&"text"===c&&(b===c||b===null)},radio:function(a){return a.nodeName.toLowerCase()==="input"&&"radio"===a.type},checkbox:function(a){return a.nodeName.toLowerCase()==="input"&&"checkbox"===a.type},file:function(a){return a.nodeName.toLowerCase()==="input"&&"file"===a.type},password:function(a){return a.nodeName.toLowerCase()==="input"&&"password"===a.type},submit:function(a){var b=a.nodeName.toLowerCase();return(b==="input"||b==="button")&&"submit"===a.type},image:function(a){return a.nodeName.toLowerCase()==="input"&&"image"===a.type},reset:function(a){var b=a.nodeName.toLowerCase();return(b==="input"||b==="button")&&"reset"===a.type},button:function(a){var b=a.nodeName.toLowerCase();return b==="input"&&"button"===a.type||b==="button"},input:function(a){return/input|select|textarea|button/i.test(a.nodeName)},focus:function(a){return a===a.ownerDocument.activeElement}},setFilters:{first:function(a,b){return b===0},last:function(a,b,c,d){return b===d.length-1},even:function(a,b){return b%2===0},odd:function(a,b){return b%2===1},lt:function(a,b,c){return b<c[3]-0},gt:function(a,b,c){return b>c[3]-0},nth:function(a,b,c){return c[3]-0===b},eq:function(a,b,c){return c[3]-0===b}},filter:{PSEUDO:function(a,b,c,d){var e=b[1],f=o.filters[e];if(f)return f(a,c,b,d);if(e==="contains")return(a.textContent||a.innerText||n([a])||"").indexOf(b[3])>=0;if(e==="not"){var g=b[3];for(var h=0,i=g.length;h<i;h++)if(g[h]===a)return!1;return!0}m.error(e)},CHILD:function(a,b){var c,e,f,g,h,i,j,k=b[1],l=a;switch(k){case"only":case"first":while(l=l.previousSibling)if(l.nodeType===1)return!1;if(k==="first")return!0;l=a;case"last":while(l=l.nextSibling)if(l.nodeType===1)return!1;return!0;case"nth":c=b[2],e=b[3];if(c===1&&e===0)return!0;f=b[0],g=a.parentNode;if(g&&(g[d]!==f||!a.nodeIndex)){i=0;for(l=g.firstChild;l;l=l.nextSibling)l.nodeType===1&&(l.nodeIndex=++i);g[d]=f}j=a.nodeIndex-e;return c===0?j===0:j%c===0&&j/c>=0}},ID:function(a,b){return a.nodeType===1&&a.getAttribute("id")===b},TAG:function(a,b){return b==="*"&&a.nodeType===1||!!a.nodeName&&a.nodeName.toLowerCase()===b},CLASS:function(a,b){return(" "+(a.className||a.getAttribute("class"))+" ").indexOf(b)>-1},ATTR:function(a,b){var c=b[1],d=m.attr?m.attr(a,c):o.attrHandle[c]?o.attrHandle[c](a):a[c]!=null?a[c]:a.getAttribute(c),e=d+"",f=b[2],g=b[4];return d==null?f==="!=":!f&&m.attr?d!=null:f==="="?e===g:f==="*="?e.indexOf(g)>=0:f==="~="?(" "+e+" ").indexOf(g)>=0:g?f==="!="?e!==g:f==="^="?e.indexOf(g)===0:f==="$="?e.substr(e.length-g.length)===g:f==="|="?e===g||e.substr(0,g.length+1)===g+"-":!1:e&&d!==!1},POS:function(a,b,c,d){var e=b[2],f=o.setFilters[e];if(f)return f(a,c,b,d)}}},p=o.match.POS,q=function(a,b){return"\\"+(b-0+1)};for(var r in o.match)o.match[r]=new RegExp(o.match[r].source+/(?![^\[]*\])(?![^\(]*\))/.source),o.leftMatch[r]=new RegExp(/(^(?:.|\r|\n)*?)/.source+o.match[r].source.replace(/\\(\d+)/g,q));o.match.globalPOS=p;var s=function(a,b){a=Array.prototype.slice.call(a,0);if(b){b.push.apply(b,a);return b}return a};try{Array.prototype.slice.call(c.documentElement.childNodes,0)[0].nodeType}catch(t){s=function(a,b){var c=0,d=b||[];if(g.call(a)==="[object Array]")Array.prototype.push.apply(d,a);else if(typeof a.length=="number")for(var e=a.length;c<e;c++)d.push(a[c]);else for(;a[c];c++)d.push(a[c]);return d}}var u,v;c.documentElement.compareDocumentPosition?u=function(a,b){if(a===b){h=!0;return 0}if(!a.compareDocumentPosition||!b.compareDocumentPosition)return a.compareDocumentPosition?-1:1;return a.compareDocumentPosition(b)&4?-1:1}:(u=function(a,b){if(a===b){h=!0;return 0}if(a.sourceIndex&&b.sourceIndex)return a.sourceIndex-b.sourceIndex;var c,d,e=[],f=[],g=a.parentNode,i=b.parentNode,j=g;if(g===i)return v(a,b);if(!g)return-1;if(!i)return 1;while(j)e.unshift(j),j=j.parentNode;j=i;while(j)f.unshift(j),j=j.parentNode;c=e.length,d=f.length;for(var k=0;k<c&&k<d;k++)if(e[k]!==f[k])return v(e[k],f[k]);return k===c?v(a,f[k],-1):v(e[k],b,1)},v=function(a,b,c){if(a===b)return c;var d=a.nextSibling;while(d){if(d===b)return-1;d=d.nextSibling}return 1}),function(){var a=c.createElement("div"),d="script"+(new Date).getTime(),e=c.documentElement;a.innerHTML="<a name='"+d+"'/>",e.insertBefore(a,e.firstChild),c.getElementById(d)&&(o.find.ID=function(a,c,d){if(typeof c.getElementById!="undefined"&&!d){var e=c.getElementById(a[1]);return e?e.id===a[1]||typeof e.getAttributeNode!="undefined"&&e.getAttributeNode("id").nodeValue===a[1]?[e]:b:[]}},o.filter.ID=function(a,b){var c=typeof a.getAttributeNode!="undefined"&&a.getAttributeNode("id");return a.nodeType===1&&c&&c.nodeValue===b}),e.removeChild(a),e=a=null}(),function(){var a=c.createElement("div");a.appendChild(c.createComment("")),a.getElementsByTagName("*").length>0&&(o.find.TAG=function(a,b){var c=b.getElementsByTagName(a[1]);if(a[1]==="*"){var d=[];for(var e=0;c[e];e++)c[e].nodeType===1&&d.push(c[e]);c=d}return c}),a.innerHTML="<a href='#'></a>",a.firstChild&&typeof a.firstChild.getAttribute!="undefined"&&a.firstChild.getAttribute("href")!=="#"&&(o.attrHandle.href=function(a){return a.getAttribute("href",2)}),a=null}(),c.querySelectorAll&&function(){var a=m,b=c.createElement("div"),d="__sizzle__";b.innerHTML="<p class='TEST'></p>";if(!b.querySelectorAll||b.querySelectorAll(".TEST").length!==0){m=function(b,e,f,g){e=e||c;if(!g&&!m.isXML(e)){var h=/^(\w+$)|^\.([\w\-]+$)|^#([\w\-]+$)/.exec(b);if(h&&(e.nodeType===1||e.nodeType===9)){if(h[1])return s(e.getElementsByTagName(b),f);if(h[2]&&o.find.CLASS&&e.getElementsByClassName)return s(e.getElementsByClassName(h[2]),f)}if(e.nodeType===9){if(b==="body"&&e.body)return s([e.body],f);if(h&&h[3]){var i=e.getElementById(h[3]);if(!i||!i.parentNode)return s([],f);if(i.id===h[3])return s([i],f)}try{return s(e.querySelectorAll(b),f)}catch(j){}}else if(e.nodeType===1&&e.nodeName.toLowerCase()!=="object"){var k=e,l=e.getAttribute("id"),n=l||d,p=e.parentNode,q=/^\s*[+~]/.test(b);l?n=n.replace(/'/g,"\\$&"):e.setAttribute("id",n),q&&p&&(e=e.parentNode);try{if(!q||p)return s(e.querySelectorAll("[id='"+n+"'] "+b),f)}catch(r){}finally{l||k.removeAttribute("id")}}}return a(b,e,f,g)};for(var e in a)m[e]=a[e];b=null}}(),function(){var a=c.documentElement,b=a.matchesSelector||a.mozMatchesSelector||a.webkitMatchesSelector||a.msMatchesSelector;if(b){var d=!b.call(c.createElement("div"),"div"),e=!1;try{b.call(c.documentElement,"[test!='']:sizzle")}catch(f){e=!0}m.matchesSelector=function(a,c){c=c.replace(/\=\s*([^'"\]]*)\s*\]/g,"='$1']");if(!m.isXML(a))try{if(e||!o.match.PSEUDO.test(c)&&!/!=/.test(c)){var f=b.call(a,c);if(f||!d||a.document&&a.document.nodeType!==11)return f}}catch(g){}return m(c,null,null,[a]).length>0}}}(),function(){var a=c.createElement("div");a.innerHTML="<div class='test e'></div><div class='test'></div>";if(!!a.getElementsByClassName&&a.getElementsByClassName("e").length!==0){a.lastChild.className="e";if(a.getElementsByClassName("e").length===1)return;o.order.splice(1,0,"CLASS"),o.find.CLASS=function(a,b,c){if(typeof b.getElementsByClassName!="undefined"&&!c)return b.getElementsByClassName(a[1])},a=null}}(),c.documentElement.contains?m.contains=function(a,b){return a!==b&&(a.contains?a.contains(b):!0)}:c.documentElement.compareDocumentPosition?m.contains=function(a,b){return!!(a.compareDocumentPosition(b)&16)}:m.contains=function(){return!1},m.isXML=function(a){var b=(a?a.ownerDocument||a:0).documentElement;return b?b.nodeName!=="HTML":!1};var y=function(a,b,c){var d,e=[],f="",g=b.nodeType?[b]:b;while(d=o.match.PSEUDO.exec(a))f+=d[0],a=a.replace(o.match.PSEUDO,"");a=o.relative[a]?a+"*":a;for(var h=0,i=g.length;h<i;h++)m(a,g[h],e,c);return m.filter(f,e)};m.attr=f.attr,m.selectors.attrMap={},f.find=m,f.expr=m.selectors,f.expr[":"]=f.expr.filters,f.unique=m.uniqueSort,f.text=m.getText,f.isXMLDoc=m.isXML,f.contains=m.contains}();var L=/Until$/,M=/^(?:parents|prevUntil|prevAll)/,N=/,/,O=/^.[^:#\[\.,]*$/,P=Array.prototype.slice,Q=f.expr.match.globalPOS,R={children:!0,contents:!0,next:!0,prev:!0};f.fn.extend({find:function(a){var b=this,c,d;if(typeof a!="string")return f(a).filter(function(){for(c=0,d=b.length;c<d;c++)if(f.contains(b[c],this))return!0});var e=this.pushStack("","find",a),g,h,i;for(c=0,d=this.length;c<d;c++){g=e.length,f.find(a,this[c],e);if(c>0)for(h=g;h<e.length;h++)for(i=0;i<g;i++)if(e[i]===e[h]){e.splice(h--,1);break}}return e},has:function(a){var b=f(a);return this.filter(function(){for(var a=0,c=b.length;a<c;a++)if(f.contains(this,b[a]))return!0})},not:function(a){return this.pushStack(T(this,a,!1),"not",a)},filter:function(a){return this.pushStack(T(this,a,!0),"filter",a)},is:function(a){return!!a&&(typeof a=="string"?Q.test(a)?f(a,this.context).index(this[0])>=0:f.filter(a,this).length>0:this.filter(a).length>0)},closest:function(a,b){var c=[],d,e,g=this[0];if(f.isArray(a)){var h=1;while(g&&g.ownerDocument&&g!==b){for(d=0;d<a.length;d++)f(g).is(a[d])&&c.push({selector:a[d],elem:g,level:h});g=g.parentNode,h++}return c}var i=Q.test(a)||typeof a!="string"?f(a,b||this.context):0;for(d=0,e=this.length;d<e;d++){g=this[d];while(g){if(i?i.index(g)>-1:f.find.matchesSelector(g,a)){c.push(g);break}g=g.parentNode;if(!g||!g.ownerDocument||g===b||g.nodeType===11)break}}c=c.length>1?f.unique(c):c;return this.pushStack(c,"closest",a)},index:function(a){if(!a)return this[0]&&this[0].parentNode?this.prevAll().length:-1;if(typeof a=="string")return f.inArray(this[0],f(a));return f.inArray(a.jquery?a[0]:a,this)},add:function(a,b){var c=typeof a=="string"?f(a,b):f.makeArray(a&&a.nodeType?[a]:a),d=f.merge(this.get(),c);return this.pushStack(S(c[0])||S(d[0])?d:f.unique(d))},andSelf:function(){return this.add(this.prevObject)}}),f.each({parent:function(a){var b=a.parentNode;return b&&b.nodeType!==11?b:null},parents:function(a){return f.dir(a,"parentNode")},parentsUntil:function(a,b,c){return f.dir(a,"parentNode",c)},next:function(a){return f.nth(a,2,"nextSibling")},prev:function(a){return f.nth(a,2,"previousSibling")},nextAll:function(a){return f.dir(a,"nextSibling")},prevAll:function(a){return f.dir(a,"previousSibling")},nextUntil:function(a,b,c){return f.dir(a,"nextSibling",c)},prevUntil:function(a,b,c){return f.dir(a,"previousSibling",c)},siblings:function(a){return f.sibling((a.parentNode||{}).firstChild,a)},children:function(a){return f.sibling(a.firstChild)},contents:function(a){return f.nodeName(a,"iframe")?a.contentDocument||a.contentWindow.document:f.makeArray(a.childNodes)}},function(a,b){f.fn[a]=function(c,d){var e=f.map(this,b,c);L.test(a)||(d=c),d&&typeof d=="string"&&(e=f.filter(d,e)),e=this.length>1&&!R[a]?f.unique(e):e,(this.length>1||N.test(d))&&M.test(a)&&(e=e.reverse());return this.pushStack(e,a,P.call(arguments).join(","))}}),f.extend({filter:function(a,b,c){c&&(a=":not("+a+")");return b.length===1?f.find.matchesSelector(b[0],a)?[b[0]]:[]:f.find.matches(a,b)},dir:function(a,c,d){var e=[],g=a[c];while(g&&g.nodeType!==9&&(d===b||g.nodeType!==1||!f(g).is(d)))g.nodeType===1&&e.push(g),g=g[c];return e},nth:function(a,b,c,d){b=b||1;var e=0;for(;a;a=a[c])if(a.nodeType===1&&++e===b)break;return a},sibling:function(a,b){var c=[];for(;a;a=a.nextSibling)a.nodeType===1&&a!==b&&c.push(a);return c}});var V="abbr|article|aside|audio|bdi|canvas|data|datalist|details|figcaption|figure|footer|header|hgroup|mark|meter|nav|output|progress|section|summary|time|video",W=/ jQuery\d+="(?:\d+|null)"/g,X=/^\s+/,Y=/<(?!area|br|col|embed|hr|img|input|link|meta|param)(([\w:]+)[^>]*)\/>/ig,Z=/<([\w:]+)/,$=/<tbody/i,_=/<|&#?\w+;/,ba=/<(?:script|style)/i,bb=/<(?:script|object|embed|option|style)/i,bc=new RegExp("<(?:"+V+")[\\s/>]","i"),bd=/checked\s*(?:[^=]|=\s*.checked.)/i,be=/\/(java|ecma)script/i,bf=/^\s*<!(?:\[CDATA\[|\-\-)/,bg={option:[1,"<select multiple='multiple'>","</select>"],legend:[1,"<fieldset>","</fieldset>"],thead:[1,"<table>","</table>"],tr:[2,"<table><tbody>","</tbody></table>"],td:[3,"<table><tbody><tr>","</tr></tbody></table>"],col:[2,"<table><tbody></tbody><colgroup>","</colgroup></table>"],area:[1,"<map>","</map>"],_default:[0,"",""]},bh=U(c);bg.optgroup=bg.option,bg.tbody=bg.tfoot=bg.colgroup=bg.caption=bg.thead,bg.th=bg.td,f.support.htmlSerialize||(bg._default=[1,"div<div>","</div>"]),f.fn.extend({text:function(a){return f.access(this,function(a){return a===b?f.text(this):this.empty().append((this[0]&&this[0].ownerDocument||c).createTextNode(a))},null,a,arguments.length)},wrapAll:function(a){if(f.isFunction(a))return this.each(function(b){f(this).wrapAll(a.call(this,b))});if(this[0]){var b=f(a,this[0].ownerDocument).eq(0).clone(!0);this[0].parentNode&&b.insertBefore(this[0]),b.map(function(){var a=this;while(a.firstChild&&a.firstChild.nodeType===1)a=a.firstChild;return a}).append(this)}return this},wrapInner:function(a){if(f.isFunction(a))return this.each(function(b){f(this).wrapInner(a.call(this,b))});return this.each(function(){var b=f(this),c=b.contents();c.length?c.wrapAll(a):b.append(a)})},wrap:function(a){var b=f.isFunction(a);return this.each(function(c){f(this).wrapAll(b?a.call(this,c):a)})},unwrap:function(){return this.parent().each(function(){f.nodeName(this,"body")||f(this).replaceWith(this.childNodes)}).end()},append:function(){return this.domManip(arguments,!0,function(a){this.nodeType===1&&this.appendChild(a)})},prepend:function(){return this.domManip(arguments,!0,function(a){this.nodeType===1&&this.insertBefore(a,this.firstChild)})},before:function(){if(this[0]&&this[0].parentNode)return this.domManip(arguments,!1,function(a){this.parentNode.insertBefore(a,this)});if(arguments.length){var a=f
.clean(arguments);a.push.apply(a,this.toArray());return this.pushStack(a,"before",arguments)}},after:function(){if(this[0]&&this[0].parentNode)return this.domManip(arguments,!1,function(a){this.parentNode.insertBefore(a,this.nextSibling)});if(arguments.length){var a=this.pushStack(this,"after",arguments);a.push.apply(a,f.clean(arguments));return a}},remove:function(a,b){for(var c=0,d;(d=this[c])!=null;c++)if(!a||f.filter(a,[d]).length)!b&&d.nodeType===1&&(f.cleanData(d.getElementsByTagName("*")),f.cleanData([d])),d.parentNode&&d.parentNode.removeChild(d);return this},empty:function(){for(var a=0,b;(b=this[a])!=null;a++){b.nodeType===1&&f.cleanData(b.getElementsByTagName("*"));while(b.firstChild)b.removeChild(b.firstChild)}return this},clone:function(a,b){a=a==null?!1:a,b=b==null?a:b;return this.map(function(){return f.clone(this,a,b)})},html:function(a){return f.access(this,function(a){var c=this[0]||{},d=0,e=this.length;if(a===b)return c.nodeType===1?c.innerHTML.replace(W,""):null;if(typeof a=="string"&&!ba.test(a)&&(f.support.leadingWhitespace||!X.test(a))&&!bg[(Z.exec(a)||["",""])[1].toLowerCase()]){a=a.replace(Y,"<$1></$2>");try{for(;d<e;d++)c=this[d]||{},c.nodeType===1&&(f.cleanData(c.getElementsByTagName("*")),c.innerHTML=a);c=0}catch(g){}}c&&this.empty().append(a)},null,a,arguments.length)},replaceWith:function(a){if(this[0]&&this[0].parentNode){if(f.isFunction(a))return this.each(function(b){var c=f(this),d=c.html();c.replaceWith(a.call(this,b,d))});typeof a!="string"&&(a=f(a).detach());return this.each(function(){var b=this.nextSibling,c=this.parentNode;f(this).remove(),b?f(b).before(a):f(c).append(a)})}return this.length?this.pushStack(f(f.isFunction(a)?a():a),"replaceWith",a):this},detach:function(a){return this.remove(a,!0)},domManip:function(a,c,d){var e,g,h,i,j=a[0],k=[];if(!f.support.checkClone&&arguments.length===3&&typeof j=="string"&&bd.test(j))return this.each(function(){f(this).domManip(a,c,d,!0)});if(f.isFunction(j))return this.each(function(e){var g=f(this);a[0]=j.call(this,e,c?g.html():b),g.domManip(a,c,d)});if(this[0]){i=j&&j.parentNode,f.support.parentNode&&i&&i.nodeType===11&&i.childNodes.length===this.length?e={fragment:i}:e=f.buildFragment(a,this,k),h=e.fragment,h.childNodes.length===1?g=h=h.firstChild:g=h.firstChild;if(g){c=c&&f.nodeName(g,"tr");for(var l=0,m=this.length,n=m-1;l<m;l++)d.call(c?bi(this[l],g):this[l],e.cacheable||m>1&&l<n?f.clone(h,!0,!0):h)}k.length&&f.each(k,function(a,b){b.src?f.ajax({type:"GET",global:!1,url:b.src,async:!1,dataType:"script"}):f.globalEval((b.text||b.textContent||b.innerHTML||"").replace(bf,"/*$0*/")),b.parentNode&&b.parentNode.removeChild(b)})}return this}}),f.buildFragment=function(a,b,d){var e,g,h,i,j=a[0];b&&b[0]&&(i=b[0].ownerDocument||b[0]),i.createDocumentFragment||(i=c),a.length===1&&typeof j=="string"&&j.length<512&&i===c&&j.charAt(0)==="<"&&!bb.test(j)&&(f.support.checkClone||!bd.test(j))&&(f.support.html5Clone||!bc.test(j))&&(g=!0,h=f.fragments[j],h&&h!==1&&(e=h)),e||(e=i.createDocumentFragment(),f.clean(a,i,e,d)),g&&(f.fragments[j]=h?e:1);return{fragment:e,cacheable:g}},f.fragments={},f.each({appendTo:"append",prependTo:"prepend",insertBefore:"before",insertAfter:"after",replaceAll:"replaceWith"},function(a,b){f.fn[a]=function(c){var d=[],e=f(c),g=this.length===1&&this[0].parentNode;if(g&&g.nodeType===11&&g.childNodes.length===1&&e.length===1){e[b](this[0]);return this}for(var h=0,i=e.length;h<i;h++){var j=(h>0?this.clone(!0):this).get();f(e[h])[b](j),d=d.concat(j)}return this.pushStack(d,a,e.selector)}}),f.extend({clone:function(a,b,c){var d,e,g,h=f.support.html5Clone||f.isXMLDoc(a)||!bc.test("<"+a.nodeName+">")?a.cloneNode(!0):bo(a);if((!f.support.noCloneEvent||!f.support.noCloneChecked)&&(a.nodeType===1||a.nodeType===11)&&!f.isXMLDoc(a)){bk(a,h),d=bl(a),e=bl(h);for(g=0;d[g];++g)e[g]&&bk(d[g],e[g])}if(b){bj(a,h);if(c){d=bl(a),e=bl(h);for(g=0;d[g];++g)bj(d[g],e[g])}}d=e=null;return h},clean:function(a,b,d,e){var g,h,i,j=[];b=b||c,typeof b.createElement=="undefined"&&(b=b.ownerDocument||b[0]&&b[0].ownerDocument||c);for(var k=0,l;(l=a[k])!=null;k++){typeof l=="number"&&(l+="");if(!l)continue;if(typeof l=="string")if(!_.test(l))l=b.createTextNode(l);else{l=l.replace(Y,"<$1></$2>");var m=(Z.exec(l)||["",""])[1].toLowerCase(),n=bg[m]||bg._default,o=n[0],p=b.createElement("div"),q=bh.childNodes,r;b===c?bh.appendChild(p):U(b).appendChild(p),p.innerHTML=n[1]+l+n[2];while(o--)p=p.lastChild;if(!f.support.tbody){var s=$.test(l),t=m==="table"&&!s?p.firstChild&&p.firstChild.childNodes:n[1]==="<table>"&&!s?p.childNodes:[];for(i=t.length-1;i>=0;--i)f.nodeName(t[i],"tbody")&&!t[i].childNodes.length&&t[i].parentNode.removeChild(t[i])}!f.support.leadingWhitespace&&X.test(l)&&p.insertBefore(b.createTextNode(X.exec(l)[0]),p.firstChild),l=p.childNodes,p&&(p.parentNode.removeChild(p),q.length>0&&(r=q[q.length-1],r&&r.parentNode&&r.parentNode.removeChild(r)))}var u;if(!f.support.appendChecked)if(l[0]&&typeof (u=l.length)=="number")for(i=0;i<u;i++)bn(l[i]);else bn(l);l.nodeType?j.push(l):j=f.merge(j,l)}if(d){g=function(a){return!a.type||be.test(a.type)};for(k=0;j[k];k++){h=j[k];if(e&&f.nodeName(h,"script")&&(!h.type||be.test(h.type)))e.push(h.parentNode?h.parentNode.removeChild(h):h);else{if(h.nodeType===1){var v=f.grep(h.getElementsByTagName("script"),g);j.splice.apply(j,[k+1,0].concat(v))}d.appendChild(h)}}}return j},cleanData:function(a){var b,c,d=f.cache,e=f.event.special,g=f.support.deleteExpando;for(var h=0,i;(i=a[h])!=null;h++){if(i.nodeName&&f.noData[i.nodeName.toLowerCase()])continue;c=i[f.expando];if(c){b=d[c];if(b&&b.events){for(var j in b.events)e[j]?f.event.remove(i,j):f.removeEvent(i,j,b.handle);b.handle&&(b.handle.elem=null)}g?delete i[f.expando]:i.removeAttribute&&i.removeAttribute(f.expando),delete d[c]}}}});var bp=/alpha\([^)]*\)/i,bq=/opacity=([^)]*)/,br=/([A-Z]|^ms)/g,bs=/^[\-+]?(?:\d*\.)?\d+$/i,bt=/^-?(?:\d*\.)?\d+(?!px)[^\d\s]+$/i,bu=/^([\-+])=([\-+.\de]+)/,bv=/^margin/,bw={position:"absolute",visibility:"hidden",display:"block"},bx=["Top","Right","Bottom","Left"],by,bz,bA;f.fn.css=function(a,c){return f.access(this,function(a,c,d){return d!==b?f.style(a,c,d):f.css(a,c)},a,c,arguments.length>1)},f.extend({cssHooks:{opacity:{get:function(a,b){if(b){var c=by(a,"opacity");return c===""?"1":c}return a.style.opacity}}},cssNumber:{fillOpacity:!0,fontWeight:!0,lineHeight:!0,opacity:!0,orphans:!0,widows:!0,zIndex:!0,zoom:!0},cssProps:{"float":f.support.cssFloat?"cssFloat":"styleFloat"},style:function(a,c,d,e){if(!!a&&a.nodeType!==3&&a.nodeType!==8&&!!a.style){var g,h,i=f.camelCase(c),j=a.style,k=f.cssHooks[i];c=f.cssProps[i]||i;if(d===b){if(k&&"get"in k&&(g=k.get(a,!1,e))!==b)return g;return j[c]}h=typeof d,h==="string"&&(g=bu.exec(d))&&(d=+(g[1]+1)*+g[2]+parseFloat(f.css(a,c)),h="number");if(d==null||h==="number"&&isNaN(d))return;h==="number"&&!f.cssNumber[i]&&(d+="px");if(!k||!("set"in k)||(d=k.set(a,d))!==b)try{j[c]=d}catch(l){}}},css:function(a,c,d){var e,g;c=f.camelCase(c),g=f.cssHooks[c],c=f.cssProps[c]||c,c==="cssFloat"&&(c="float");if(g&&"get"in g&&(e=g.get(a,!0,d))!==b)return e;if(by)return by(a,c)},swap:function(a,b,c){var d={},e,f;for(f in b)d[f]=a.style[f],a.style[f]=b[f];e=c.call(a);for(f in b)a.style[f]=d[f];return e}}),f.curCSS=f.css,c.defaultView&&c.defaultView.getComputedStyle&&(bz=function(a,b){var c,d,e,g,h=a.style;b=b.replace(br,"-$1").toLowerCase(),(d=a.ownerDocument.defaultView)&&(e=d.getComputedStyle(a,null))&&(c=e.getPropertyValue(b),c===""&&!f.contains(a.ownerDocument.documentElement,a)&&(c=f.style(a,b))),!f.support.pixelMargin&&e&&bv.test(b)&&bt.test(c)&&(g=h.width,h.width=c,c=e.width,h.width=g);return c}),c.documentElement.currentStyle&&(bA=function(a,b){var c,d,e,f=a.currentStyle&&a.currentStyle[b],g=a.style;f==null&&g&&(e=g[b])&&(f=e),bt.test(f)&&(c=g.left,d=a.runtimeStyle&&a.runtimeStyle.left,d&&(a.runtimeStyle.left=a.currentStyle.left),g.left=b==="fontSize"?"1em":f,f=g.pixelLeft+"px",g.left=c,d&&(a.runtimeStyle.left=d));return f===""?"auto":f}),by=bz||bA,f.each(["height","width"],function(a,b){f.cssHooks[b]={get:function(a,c,d){if(c)return a.offsetWidth!==0?bB(a,b,d):f.swap(a,bw,function(){return bB(a,b,d)})},set:function(a,b){return bs.test(b)?b+"px":b}}}),f.support.opacity||(f.cssHooks.opacity={get:function(a,b){return bq.test((b&&a.currentStyle?a.currentStyle.filter:a.style.filter)||"")?parseFloat(RegExp.$1)/100+"":b?"1":""},set:function(a,b){var c=a.style,d=a.currentStyle,e=f.isNumeric(b)?"alpha(opacity="+b*100+")":"",g=d&&d.filter||c.filter||"";c.zoom=1;if(b>=1&&f.trim(g.replace(bp,""))===""){c.removeAttribute("filter");if(d&&!d.filter)return}c.filter=bp.test(g)?g.replace(bp,e):g+" "+e}}),f(function(){f.support.reliableMarginRight||(f.cssHooks.marginRight={get:function(a,b){return f.swap(a,{display:"inline-block"},function(){return b?by(a,"margin-right"):a.style.marginRight})}})}),f.expr&&f.expr.filters&&(f.expr.filters.hidden=function(a){var b=a.offsetWidth,c=a.offsetHeight;return b===0&&c===0||!f.support.reliableHiddenOffsets&&(a.style&&a.style.display||f.css(a,"display"))==="none"},f.expr.filters.visible=function(a){return!f.expr.filters.hidden(a)}),f.each({margin:"",padding:"",border:"Width"},function(a,b){f.cssHooks[a+b]={expand:function(c){var d,e=typeof c=="string"?c.split(" "):[c],f={};for(d=0;d<4;d++)f[a+bx[d]+b]=e[d]||e[d-2]||e[0];return f}}});var bC=/%20/g,bD=/\[\]$/,bE=/\r?\n/g,bF=/#.*$/,bG=/^(.*?):[ \t]*([^\r\n]*)\r?$/mg,bH=/^(?:color|date|datetime|datetime-local|email|hidden|month|number|password|range|search|tel|text|time|url|week)$/i,bI=/^(?:about|app|app\-storage|.+\-extension|file|res|widget):$/,bJ=/^(?:GET|HEAD)$/,bK=/^\/\//,bL=/\?/,bM=/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi,bN=/^(?:select|textarea)/i,bO=/\s+/,bP=/([?&])_=[^&]*/,bQ=/^([\w\+\.\-]+:)(?:\/\/([^\/?#:]*)(?::(\d+))?)?/,bR=f.fn.load,bS={},bT={},bU,bV,bW=["*/"]+["*"];try{bU=e.href}catch(bX){bU=c.createElement("a"),bU.href="",bU=bU.href}bV=bQ.exec(bU.toLowerCase())||[],f.fn.extend({load:function(a,c,d){if(typeof a!="string"&&bR)return bR.apply(this,arguments);if(!this.length)return this;var e=a.indexOf(" ");if(e>=0){var g=a.slice(e,a.length);a=a.slice(0,e)}var h="GET";c&&(f.isFunction(c)?(d=c,c=b):typeof c=="object"&&(c=f.param(c,f.ajaxSettings.traditional),h="POST"));var i=this;f.ajax({url:a,type:h,dataType:"html",data:c,complete:function(a,b,c){c=a.responseText,a.isResolved()&&(a.done(function(a){c=a}),i.html(g?f("<div>").append(c.replace(bM,"")).find(g):c)),d&&i.each(d,[c,b,a])}});return this},serialize:function(){return f.param(this.serializeArray())},serializeArray:function(){return this.map(function(){return this.elements?f.makeArray(this.elements):this}).filter(function(){return this.name&&!this.disabled&&(this.checked||bN.test(this.nodeName)||bH.test(this.type))}).map(function(a,b){var c=f(this).val();return c==null?null:f.isArray(c)?f.map(c,function(a,c){return{name:b.name,value:a.replace(bE,"\r\n")}}):{name:b.name,value:c.replace(bE,"\r\n")}}).get()}}),f.each("ajaxStart ajaxStop ajaxComplete ajaxError ajaxSuccess ajaxSend".split(" "),function(a,b){f.fn[b]=function(a){return this.on(b,a)}}),f.each(["get","post"],function(a,c){f[c]=function(a,d,e,g){f.isFunction(d)&&(g=g||e,e=d,d=b);return f.ajax({type:c,url:a,data:d,success:e,dataType:g})}}),f.extend({getScript:function(a,c){return f.get(a,b,c,"script")},getJSON:function(a,b,c){return f.get(a,b,c,"json")},ajaxSetup:function(a,b){b?b$(a,f.ajaxSettings):(b=a,a=f.ajaxSettings),b$(a,b);return a},ajaxSettings:{url:bU,isLocal:bI.test(bV[1]),global:!0,type:"GET",contentType:"application/x-www-form-urlencoded; charset=UTF-8",processData:!0,async:!0,accepts:{xml:"application/xml, text/xml",html:"text/html",text:"text/plain",json:"application/json, text/javascript","*":bW},contents:{xml:/xml/,html:/html/,json:/json/},responseFields:{xml:"responseXML",text:"responseText"},converters:{"* text":a.String,"text html":!0,"text json":f.parseJSON,"text xml":f.parseXML},flatOptions:{context:!0,url:!0}},ajaxPrefilter:bY(bS),ajaxTransport:bY(bT),ajax:function(a,c){function w(a,c,l,m){if(s!==2){s=2,q&&clearTimeout(q),p=b,n=m||"",v.readyState=a>0?4:0;var o,r,u,w=c,x=l?ca(d,v,l):b,y,z;if(a>=200&&a<300||a===304){if(d.ifModified){if(y=v.getResponseHeader("Last-Modified"))f.lastModified[k]=y;if(z=v.getResponseHeader("Etag"))f.etag[k]=z}if(a===304)w="notmodified",o=!0;else try{r=cb(d,x),w="success",o=!0}catch(A){w="parsererror",u=A}}else{u=w;if(!w||a)w="error",a<0&&(a=0)}v.status=a,v.statusText=""+(c||w),o?h.resolveWith(e,[r,w,v]):h.rejectWith(e,[v,w,u]),v.statusCode(j),j=b,t&&g.trigger("ajax"+(o?"Success":"Error"),[v,d,o?r:u]),i.fireWith(e,[v,w]),t&&(g.trigger("ajaxComplete",[v,d]),--f.active||f.event.trigger("ajaxStop"))}}typeof a=="object"&&(c=a,a=b),c=c||{};var d=f.ajaxSetup({},c),e=d.context||d,g=e!==d&&(e.nodeType||e instanceof f)?f(e):f.event,h=f.Deferred(),i=f.Callbacks("once memory"),j=d.statusCode||{},k,l={},m={},n,o,p,q,r,s=0,t,u,v={readyState:0,setRequestHeader:function(a,b){if(!s){var c=a.toLowerCase();a=m[c]=m[c]||a,l[a]=b}return this},getAllResponseHeaders:function(){return s===2?n:null},getResponseHeader:function(a){var c;if(s===2){if(!o){o={};while(c=bG.exec(n))o[c[1].toLowerCase()]=c[2]}c=o[a.toLowerCase()]}return c===b?null:c},overrideMimeType:function(a){s||(d.mimeType=a);return this},abort:function(a){a=a||"abort",p&&p.abort(a),w(0,a);return this}};h.promise(v),v.success=v.done,v.error=v.fail,v.complete=i.add,v.statusCode=function(a){if(a){var b;if(s<2)for(b in a)j[b]=[j[b],a[b]];else b=a[v.status],v.then(b,b)}return this},d.url=((a||d.url)+"").replace(bF,"").replace(bK,bV[1]+"//"),d.dataTypes=f.trim(d.dataType||"*").toLowerCase().split(bO),d.crossDomain==null&&(r=bQ.exec(d.url.toLowerCase()),d.crossDomain=!(!r||r[1]==bV[1]&&r[2]==bV[2]&&(r[3]||(r[1]==="http:"?80:443))==(bV[3]||(bV[1]==="http:"?80:443)))),d.data&&d.processData&&typeof d.data!="string"&&(d.data=f.param(d.data,d.traditional)),bZ(bS,d,c,v);if(s===2)return!1;t=d.global,d.type=d.type.toUpperCase(),d.hasContent=!bJ.test(d.type),t&&f.active++===0&&f.event.trigger("ajaxStart");if(!d.hasContent){d.data&&(d.url+=(bL.test(d.url)?"&":"?")+d.data,delete d.data),k=d.url;if(d.cache===!1){var x=f.now(),y=d.url.replace(bP,"$1_="+x);d.url=y+(y===d.url?(bL.test(d.url)?"&":"?")+"_="+x:"")}}(d.data&&d.hasContent&&d.contentType!==!1||c.contentType)&&v.setRequestHeader("Content-Type",d.contentType),d.ifModified&&(k=k||d.url,f.lastModified[k]&&v.setRequestHeader("If-Modified-Since",f.lastModified[k]),f.etag[k]&&v.setRequestHeader("If-None-Match",f.etag[k])),v.setRequestHeader("Accept",d.dataTypes[0]&&d.accepts[d.dataTypes[0]]?d.accepts[d.dataTypes[0]]+(d.dataTypes[0]!=="*"?", "+bW+"; q=0.01":""):d.accepts["*"]);for(u in d.headers)v.setRequestHeader(u,d.headers[u]);if(d.beforeSend&&(d.beforeSend.call(e,v,d)===!1||s===2)){v.abort();return!1}for(u in{success:1,error:1,complete:1})v[u](d[u]);p=bZ(bT,d,c,v);if(!p)w(-1,"No Transport");else{v.readyState=1,t&&g.trigger("ajaxSend",[v,d]),d.async&&d.timeout>0&&(q=setTimeout(function(){v.abort("timeout")},d.timeout));try{s=1,p.send(l,w)}catch(z){if(s<2)w(-1,z);else throw z}}return v},param:function(a,c){var d=[],e=function(a,b){b=f.isFunction(b)?b():b,d[d.length]=encodeURIComponent(a)+"="+encodeURIComponent(b)};c===b&&(c=f.ajaxSettings.traditional);if(f.isArray(a)||a.jquery&&!f.isPlainObject(a))f.each(a,function(){e(this.name,this.value)});else for(var g in a)b_(g,a[g],c,e);return d.join("&").replace(bC,"+")}}),f.extend({active:0,lastModified:{},etag:{}});var cc=f.now(),cd=/(\=)\?(&|$)|\?\?/i;f.ajaxSetup({jsonp:"callback",jsonpCallback:function(){return f.expando+"_"+cc++}}),f.ajaxPrefilter("json jsonp",function(b,c,d){var e=typeof b.data=="string"&&/^application\/x\-www\-form\-urlencoded/.test(b.contentType);if(b.dataTypes[0]==="jsonp"||b.jsonp!==!1&&(cd.test(b.url)||e&&cd.test(b.data))){var g,h=b.jsonpCallback=f.isFunction(b.jsonpCallback)?b.jsonpCallback():b.jsonpCallback,i=a[h],j=b.url,k=b.data,l="$1"+h+"$2";b.jsonp!==!1&&(j=j.replace(cd,l),b.url===j&&(e&&(k=k.replace(cd,l)),b.data===k&&(j+=(/\?/.test(j)?"&":"?")+b.jsonp+"="+h))),b.url=j,b.data=k,a[h]=function(a){g=[a]},d.always(function(){a[h]=i,g&&f.isFunction(i)&&a[h](g[0])}),b.converters["script json"]=function(){g||f.error(h+" was not called");return g[0]},b.dataTypes[0]="json";return"script"}}),f.ajaxSetup({accepts:{script:"text/javascript, application/javascript, application/ecmascript, application/x-ecmascript"},contents:{script:/javascript|ecmascript/},converters:{"text script":function(a){f.globalEval(a);return a}}}),f.ajaxPrefilter("script",function(a){a.cache===b&&(a.cache=!1),a.crossDomain&&(a.type="GET",a.global=!1)}),f.ajaxTransport("script",function(a){if(a.crossDomain){var d,e=c.head||c.getElementsByTagName("head")[0]||c.documentElement;return{send:function(f,g){d=c.createElement("script"),d.async="async",a.scriptCharset&&(d.charset=a.scriptCharset),d.src=a.url,d.onload=d.onreadystatechange=function(a,c){if(c||!d.readyState||/loaded|complete/.test(d.readyState))d.onload=d.onreadystatechange=null,e&&d.parentNode&&e.removeChild(d),d=b,c||g(200,"success")},e.insertBefore(d,e.firstChild)},abort:function(){d&&d.onload(0,1)}}}});var ce=a.ActiveXObject?function(){for(var a in cg)cg[a](0,1)}:!1,cf=0,cg;f.ajaxSettings.xhr=a.ActiveXObject?function(){return!this.isLocal&&ch()||ci()}:ch,function(a){f.extend(f.support,{ajax:!!a,cors:!!a&&"withCredentials"in a})}(f.ajaxSettings.xhr()),f.support.ajax&&f.ajaxTransport(function(c){if(!c.crossDomain||f.support.cors){var d;return{send:function(e,g){var h=c.xhr(),i,j;c.username?h.open(c.type,c.url,c.async,c.username,c.password):h.open(c.type,c.url,c.async);if(c.xhrFields)for(j in c.xhrFields)h[j]=c.xhrFields[j];c.mimeType&&h.overrideMimeType&&h.overrideMimeType(c.mimeType),!c.crossDomain&&!e["X-Requested-With"]&&(e["X-Requested-With"]="XMLHttpRequest");try{for(j in e)h.setRequestHeader(j,e[j])}catch(k){}h.send(c.hasContent&&c.data||null),d=function(a,e){var j,k,l,m,n;try{if(d&&(e||h.readyState===4)){d=b,i&&(h.onreadystatechange=f.noop,ce&&delete cg[i]);if(e)h.readyState!==4&&h.abort();else{j=h.status,l=h.getAllResponseHeaders(),m={},n=h.responseXML,n&&n.documentElement&&(m.xml=n);try{m.text=h.responseText}catch(a){}try{k=h.statusText}catch(o){k=""}!j&&c.isLocal&&!c.crossDomain?j=m.text?200:404:j===1223&&(j=204)}}}catch(p){e||g(-1,p)}m&&g(j,k,m,l)},!c.async||h.readyState===4?d():(i=++cf,ce&&(cg||(cg={},f(a).unload(ce)),cg[i]=d),h.onreadystatechange=d)},abort:function(){d&&d(0,1)}}}});var cj={},ck,cl,cm=/^(?:toggle|show|hide)$/,cn=/^([+\-]=)?([\d+.\-]+)([a-z%]*)$/i,co,cp=[["height","marginTop","marginBottom","paddingTop","paddingBottom"],["width","marginLeft","marginRight","paddingLeft","paddingRight"],["opacity"]],cq;f.fn.extend({show:function(a,b,c){var d,e;if(a||a===0)return this.animate(ct("show",3),a,b,c);for(var g=0,h=this.length;g<h;g++)d=this[g],d.style&&(e=d.style.display,!f._data(d,"olddisplay")&&e==="none"&&(e=d.style.display=""),(e===""&&f.css(d,"display")==="none"||!f.contains(d.ownerDocument.documentElement,d))&&f._data(d,"olddisplay",cu(d.nodeName)));for(g=0;g<h;g++){d=this[g];if(d.style){e=d.style.display;if(e===""||e==="none")d.style.display=f._data(d,"olddisplay")||""}}return this},hide:function(a,b,c){if(a||a===0)return this.animate(ct("hide",3),a,b,c);var d,e,g=0,h=this.length;for(;g<h;g++)d=this[g],d.style&&(e=f.css(d,"display"),e!=="none"&&!f._data(d,"olddisplay")&&f._data(d,"olddisplay",e));for(g=0;g<h;g++)this[g].style&&(this[g].style.display="none");return this},_toggle:f.fn.toggle,toggle:function(a,b,c){var d=typeof a=="boolean";f.isFunction(a)&&f.isFunction(b)?this._toggle.apply(this,arguments):a==null||d?this.each(function(){var b=d?a:f(this).is(":hidden");f(this)[b?"show":"hide"]()}):this.animate(ct("toggle",3),a,b,c);return this},fadeTo:function(a,b,c,d){return this.filter(":hidden").css("opacity",0).show().end().animate({opacity:b},a,c,d)},animate:function(a,b,c,d){function g(){e.queue===!1&&f._mark(this);var b=f.extend({},e),c=this.nodeType===1,d=c&&f(this).is(":hidden"),g,h,i,j,k,l,m,n,o,p,q;b.animatedProperties={};for(i in a){g=f.camelCase(i),i!==g&&(a[g]=a[i],delete a[i]);if((k=f.cssHooks[g])&&"expand"in k){l=k.expand(a[g]),delete a[g];for(i in l)i in a||(a[i]=l[i])}}for(g in a){h=a[g],f.isArray(h)?(b.animatedProperties[g]=h[1],h=a[g]=h[0]):b.animatedProperties[g]=b.specialEasing&&b.specialEasing[g]||b.easing||"swing";if(h==="hide"&&d||h==="show"&&!d)return b.complete.call(this);c&&(g==="height"||g==="width")&&(b.overflow=[this.style.overflow,this.style.overflowX,this.style.overflowY],f.css(this,"display")==="inline"&&f.css(this,"float")==="none"&&(!f.support.inlineBlockNeedsLayout||cu(this.nodeName)==="inline"?this.style.display="inline-block":this.style.zoom=1))}b.overflow!=null&&(this.style.overflow="hidden");for(i in a)j=new f.fx(this,b,i),h=a[i],cm.test(h)?(q=f._data(this,"toggle"+i)||(h==="toggle"?d?"show":"hide":0),q?(f._data(this,"toggle"+i,q==="show"?"hide":"show"),j[q]()):j[h]()):(m=cn.exec(h),n=j.cur(),m?(o=parseFloat(m[2]),p=m[3]||(f.cssNumber[i]?"":"px"),p!=="px"&&(f.style(this,i,(o||1)+p),n=(o||1)/j.cur()*n,f.style(this,i,n+p)),m[1]&&(o=(m[1]==="-="?-1:1)*o+n),j.custom(n,o,p)):j.custom(n,h,""));return!0}var e=f.speed(b,c,d);if(f.isEmptyObject(a))return this.each(e.complete,[!1]);a=f.extend({},a);return e.queue===!1?this.each(g):this.queue(e.queue,g)},stop:function(a,c,d){typeof a!="string"&&(d=c,c=a,a=b),c&&a!==!1&&this.queue(a||"fx",[]);return this.each(function(){function h(a,b,c){var e=b[c];f.removeData(a,c,!0),e.stop(d)}var b,c=!1,e=f.timers,g=f._data(this);d||f._unmark(!0,this);if(a==null)for(b in g)g[b]&&g[b].stop&&b.indexOf(".run")===b.length-4&&h(this,g,b);else g[b=a+".run"]&&g[b].stop&&h(this,g,b);for(b=e.length;b--;)e[b].elem===this&&(a==null||e[b].queue===a)&&(d?e[b](!0):e[b].saveState(),c=!0,e.splice(b,1));(!d||!c)&&f.dequeue(this,a)})}}),f.each({slideDown:ct("show",1),slideUp:ct("hide",1),slideToggle:ct("toggle",1),fadeIn:{opacity:"show"},fadeOut:{opacity:"hide"},fadeToggle:{opacity:"toggle"}},function(a,b){f.fn[a]=function(a,c,d){return this.animate(b,a,c,d)}}),f.extend({speed:function(a,b,c){var d=a&&typeof a=="object"?f.extend({},a):{complete:c||!c&&b||f.isFunction(a)&&a,duration:a,easing:c&&b||b&&!f.isFunction(b)&&b};d.duration=f.fx.off?0:typeof d.duration=="number"?d.duration:d.duration in f.fx.speeds?f.fx.speeds[d.duration]:f.fx.speeds._default;if(d.queue==null||d.queue===!0)d.queue="fx";d.old=d.complete,d.complete=function(a){f.isFunction(d.old)&&d.old.call(this),d.queue?f.dequeue(this,d.queue):a!==!1&&f._unmark(this)};return d},easing:{linear:function(a){return a},swing:function(a){return-Math.cos(a*Math.PI)/2+.5}},timers:[],fx:function(a,b,c){this.options=b,this.elem=a,this.prop=c,b.orig=b.orig||{}}}),f.fx.prototype={update:function(){this.options.step&&this.options.step.call(this.elem,this.now,this),(f.fx.step[this.prop]||f.fx.step._default)(this)},cur:function(){if(this.elem[this.prop]!=null&&(!this.elem.style||this.elem.style[this.prop]==null))return this.elem[this.prop];var a,b=f.css(this.elem,this.prop);return isNaN(a=parseFloat(b))?!b||b==="auto"?0:b:a},custom:function(a,c,d){function h(a){return e.step(a)}var e=this,g=f.fx;this.startTime=cq||cr(),this.end=c,this.now=this.start=a,this.pos=this.state=0,this.unit=d||this.unit||(f.cssNumber[this.prop]?"":"px"),h.queue=this.options.queue,h.elem=this.elem,h.saveState=function(){f._data(e.elem,"fxshow"+e.prop)===b&&(e.options.hide?f._data(e.elem,"fxshow"+e.prop,e.start):e.options.show&&f._data(e.elem,"fxshow"+e.prop,e.end))},h()&&f.timers.push(h)&&!co&&(co=setInterval(g.tick,g.interval))},show:function(){var a=f._data(this.elem,"fxshow"+this.prop);this.options.orig[this.prop]=a||f.style(this.elem,this.prop),this.options.show=!0,a!==b?this.custom(this.cur(),a):this.custom(this.prop==="width"||this.prop==="height"?1:0,this.cur()),f(this.elem).show()},hide:function(){this.options.orig[this.prop]=f._data(this.elem,"fxshow"+this.prop)||f.style(this.elem,this.prop),this.options.hide=!0,this.custom(this.cur(),0)},step:function(a){var b,c,d,e=cq||cr(),g=!0,h=this.elem,i=this.options;if(a||e>=i.duration+this.startTime){this.now=this.end,this.pos=this.state=1,this.update(),i.animatedProperties[this.prop]=!0;for(b in i.animatedProperties)i.animatedProperties[b]!==!0&&(g=!1);if(g){i.overflow!=null&&!f.support.shrinkWrapBlocks&&f.each(["","X","Y"],function(a,b){h.style["overflow"+b]=i.overflow[a]}),i.hide&&f(h).hide();if(i.hide||i.show)for(b in i.animatedProperties)f.style(h,b,i.orig[b]),f.removeData(h,"fxshow"+b,!0),f.removeData(h,"toggle"+b,!0);d=i.complete,d&&(i.complete=!1,d.call(h))}return!1}i.duration==Infinity?this.now=e:(c=e-this.startTime,this.state=c/i.duration,this.pos=f.easing[i.animatedProperties[this.prop]](this.state,c,0,1,i.duration),this.now=this.start+(this.end-this.start)*this.pos),this.update();return!0}},f.extend(f.fx,{tick:function(){var a,b=f.timers,c=0;for(;c<b.length;c++)a=b[c],!a()&&b[c]===a&&b.splice(c--,1);b.length||f.fx.stop()},interval:13,stop:function(){clearInterval(co),co=null},speeds:{slow:600,fast:200,_default:400},step:{opacity:function(a){f.style(a.elem,"opacity",a.now)},_default:function(a){a.elem.style&&a.elem.style[a.prop]!=null?a.elem.style[a.prop]=a.now+a.unit:a.elem[a.prop]=a.now}}}),f.each(cp.concat.apply([],cp),function(a,b){b.indexOf("margin")&&(f.fx.step[b]=function(a){f.style(a.elem,b,Math.max(0,a.now)+a.unit)})}),f.expr&&f.expr.filters&&(f.expr.filters.animated=function(a){return f.grep(f.timers,function(b){return a===b.elem}).length});var cv,cw=/^t(?:able|d|h)$/i,cx=/^(?:body|html)$/i;"getBoundingClientRect"in c.documentElement?cv=function(a,b,c,d){try{d=a.getBoundingClientRect()}catch(e){}if(!d||!f.contains(c,a))return d?{top:d.top,left:d.left}:{top:0,left:0};var g=b.body,h=cy(b),i=c.clientTop||g.clientTop||0,j=c.clientLeft||g.clientLeft||0,k=h.pageYOffset||f.support.boxModel&&c.scrollTop||g.scrollTop,l=h.pageXOffset||f.support.boxModel&&c.scrollLeft||g.scrollLeft,m=d.top+k-i,n=d.left+l-j;return{top:m,left:n}}:cv=function(a,b,c){var d,e=a.offsetParent,g=a,h=b.body,i=b.defaultView,j=i?i.getComputedStyle(a,null):a.currentStyle,k=a.offsetTop,l=a.offsetLeft;while((a=a.parentNode)&&a!==h&&a!==c){if(f.support.fixedPosition&&j.position==="fixed")break;d=i?i.getComputedStyle(a,null):a.currentStyle,k-=a.scrollTop,l-=a.scrollLeft,a===e&&(k+=a.offsetTop,l+=a.offsetLeft,f.support.doesNotAddBorder&&(!f.support.doesAddBorderForTableAndCells||!cw.test(a.nodeName))&&(k+=parseFloat(d.borderTopWidth)||0,l+=parseFloat(d.borderLeftWidth)||0),g=e,e=a.offsetParent),f.support.subtractsBorderForOverflowNotVisible&&d.overflow!=="visible"&&(k+=parseFloat(d.borderTopWidth)||0,l+=parseFloat(d.borderLeftWidth)||0),j=d}if(j.position==="relative"||j.position==="static")k+=h.offsetTop,l+=h.offsetLeft;f.support.fixedPosition&&j.position==="fixed"&&(k+=Math.max(c.scrollTop,h.scrollTop),l+=Math.max(c.scrollLeft,h.scrollLeft));return{top:k,left:l}},f.fn.offset=function(a){if(arguments.length)return a===b?this:this.each(function(b){f.offset.setOffset(this,a,b)});var c=this[0],d=c&&c.ownerDocument;if(!d)return null;if(c===d.body)return f.offset.bodyOffset(c);return cv(c,d,d.documentElement)},f.offset={bodyOffset:function(a){var b=a.offsetTop,c=a.offsetLeft;f.support.doesNotIncludeMarginInBodyOffset&&(b+=parseFloat(f.css(a,"marginTop"))||0,c+=parseFloat(f.css(a,"marginLeft"))||0);return{top:b,left:c}},setOffset:function(a,b,c){var d=f.css(a,"position");d==="static"&&(a.style.position="relative");var e=f(a),g=e.offset(),h=f.css(a,"top"),i=f.css(a,"left"),j=(d==="absolute"||d==="fixed")&&f.inArray("auto",[h,i])>-1,k={},l={},m,n;j?(l=e.position(),m=l.top,n=l.left):(m=parseFloat(h)||0,n=parseFloat(i)||0),f.isFunction(b)&&(b=b.call(a,c,g)),b.top!=null&&(k.top=b.top-g.top+m),b.left!=null&&(k.left=b.left-g.left+n),"using"in b?b.using.call(a,k):e.css(k)}},f.fn.extend({position:function(){if(!this[0])return null;var a=this[0],b=this.offsetParent(),c=this.offset(),d=cx.test(b[0].nodeName)?{top:0,left:0}:b.offset();c.top-=parseFloat(f.css(a,"marginTop"))||0,c.left-=parseFloat(f.css(a,"marginLeft"))||0,d.top+=parseFloat(f.css(b[0],"borderTopWidth"))||0,d.left+=parseFloat(f.css(b[0],"borderLeftWidth"))||0;return{top:c.top-d.top,left:c.left-d.left}},offsetParent:function(){return this.map(function(){var a=this.offsetParent||c.body;while(a&&!cx.test(a.nodeName)&&f.css(a,"position")==="static")a=a.offsetParent;return a})}}),f.each({scrollLeft:"pageXOffset",scrollTop:"pageYOffset"},function(a,c){var d=/Y/.test(c);f.fn[a]=function(e){return f.access(this,function(a,e,g){var h=cy(a);if(g===b)return h?c in h?h[c]:f.support.boxModel&&h.document.documentElement[e]||h.document.body[e]:a[e];h?h.scrollTo(d?f(h).scrollLeft():g,d?g:f(h).scrollTop()):a[e]=g},a,e,arguments.length,null)}}),f.each({Height:"height",Width:"width"},function(a,c){var d="client"+a,e="scroll"+a,g="offset"+a;f.fn["inner"+a]=function(){var a=this[0];return a?a.style?parseFloat(f.css(a,c,"padding")):this[c]():null},f.fn["outer"+a]=function(a){var b=this[0];return b?b.style?parseFloat(f.css(b,c,a?"margin":"border")):this[c]():null},f.fn[c]=function(a){return f.access(this,function(a,c,h){var i,j,k,l;if(f.isWindow(a)){i=a.document,j=i.documentElement[d];return f.support.boxModel&&j||i.body&&i.body[d]||j}if(a.nodeType===9){i=a.documentElement;if(i[d]>=i[e])return i[d];return Math.max(a.body[e],i[e],a.body[g],i[g])}if(h===b){k=f.css(a,c),l=parseFloat(k);return f.isNumeric(l)?l:k}f(a).css(c,h)},c,a,arguments.length,null)}}),a.jQuery=a.$=f,typeof define=="function"&&define.amd&&define.amd.jQuery&&define("jquery",[],function(){return f})})(window);
/*! Springbok */
/*'use strict';*/

var arraySliceFunction=Array.prototype.slice;

window.S={
	ready:function(callback){ $(document).ready(callback); },
	redirect:function(url){ window.location=url; },
	setTitle:function(title){document.title=title;},
	
	loadScript:function(url){
		var script = document.createElement("script");
		script.type = "text/javascript";
		script.src = url;
		document.body.appendChild(script);
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
		jQuery.ajax({
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
			for (i=0; i<array_pattern.length; i++){
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
		$(document).bind('click',function(e){
			if(e.which==2){
				e.preventDefault();
				return false;
			}
		});
	},
	
	isString:function(varName){ return typeof(varName)==='string'; },
	isArray:Array.isArray || $.isArray,
	isObject:function(varName){ return typeof(varName)==='object' },
	
	clone:function(object){
		// clone like _.clone : Shallow copy
		//return $.extend({},object);
		return S.extendsObj({},object);
	},
	deepClone:function(object){
		return $.extend(true,{},object);
	},
	extendsClass:function(subclass,superclass,methods){
		var f=function (){};
		f.prototype=superclass.prototype;
		subclass.prototype=new f();
		subclass.prototype.constructor=subclass;
		subclass.superctor=superclass;
		subclass.superclass=superclass.prototype;
		
		if(methods) S.extendsPrototype(subclass,methods);
	},
	/*extendsMClass:function(subclass,superclass,methods){
		superclass=arraySliceFunction.call(arguments,1,-1);
		for(var i in superclass){
			
		}
		if(methods) S.extendsPrototype(subclass,methods);
	},*/
	extendsPrototype:function(targetclass,methods){
		for(var i in methods)
			targetclass.prototype[i]=methods[i];
		return targetclass;
	},
	addSetMethods:function(targetclass,methods){
		for(var i in methods.split(',')){
			var methodName=methods[i];
			targetclass.prototype[methodName]=function(val){ this['_'+methodName]=val; return this; };
		}
	},
	
	extendsObj:function(target,object){
		if(object)
			for(var i in object)
				target[i]=object[i];
		return target;
	},
	
	tableClick:function(){
		$('table.pointer tr').off('click').each(function(i,elt){
			elt=$(elt);
			var trTimeout,href=elt.attr('rel');
			elt.on('click',function(){trTimeout=setTimeout(function(){S.redirect(href)},250);});
			elt.find('a').off('click').on('click',function(e){ clearTimeout(trTimeout); e.stopPropagation(); e.preventDefault();
					var a=$(this),confirmMessage=a.data('confirm'); if(!confirmMessage || confirm(confirmMessage=='1' ? i18nc['Are you sure ?'] : confirmMessage)) S.redirect(a.attr('href')); })
		});
		
	}
};

S.extendsPrototype(String,{
	sbLcFirst:function(){return this.charAt(0).toLowerCase()+this.substr(1);},
	sbUcFirst:function(){return this.charAt(0).toUpperCase()+this.substr(1);},
	sbStartsWith:function(str){return this.indexOf(str)===0;},
	sbEndsWith:function(str){return this.match(RegExp.sbEscape(str)+"$")==str;},
	sbContains:function(str){return this.indexOf(str)!==-1},
	sbTrim:function(pattern){return this.sbLtrim(pattern).sbRtrim(pattern);},
	sbLtrim:function(pattern){if(pattern===undefined) pattern='\\s+'; return this.replace(new RegExp('^'+pattern,'g'),'');},
	sbRtrim:function(pattern){if(pattern===undefined) pattern='\\s+'; return this.replace(new RegExp(pattern+'$','g'),'');},
	sbRepeat:function(m){return new Array(m + 1).join(this)},
	sbIsEmpty:function(){return /^\s*$/.test(this)},
	sbRemoveSpecialChars:function(){
		var t=this;
		[
			[/æ|ǽ/g,'ae'],[/œ/g,'oe'],[/Ä|À|Á|Â|Ã|Ä|Å|Ǻ|Ā|Ă|Ą|Ǎ/g,'A'],[/ä|à|á|â|ã|å|ǻ|ā|ă|ą|ǎ|ª/g,'a'],
			[/Ç|Ć|Ĉ|Ċ|Č/g,'C'],[/ç|ć|ĉ|ċ|č/g,'c'],
			[/Ð|Ď|Đ/g,'D'],[/ð|ď|đ/g,'d'],
			[/È|É|Ê|Ë|Ē|Ĕ|Ė|Ę|Ě/g,'E'],[/è|é|ê|ë|ē|ĕ|ė|ę|ě/g,'e'],
			[/Ĝ|Ğ|Ġ|Ģ/g,'G'],[/ĝ|ğ|ġ|ģ/g,'g'],
			[/Ĥ|Ħ/g,'H'],[/ĥ|ħ/g,'h'],
			[/Ì|Í|Î|Ï|Ĩ|Ī|Ĭ|Ǐ|Į|İ/g,'I'],[/ì|í|î|ï|ĩ|ī|ĭ|ǐ|į|ı/g,'i'],
			[/Ĵ/g,'J'],[/ĵ/g,'j'],[/Ķ/g,'K'],[/ķ/g,'k'],
			[/Ĺ|Ļ|Ľ|Ŀ|Ł/g,'L'],[/ĺ|ļ|ľ|ŀ|ł/g,'l'],
			[/Ñ|Ń|Ņ|Ň/g,'N'],[/ñ|ń|ņ|ň|ŉ/g,'n'],
			[/Ö|Ò|Ó|Ô|Õ|Ō|Ŏ|Ǒ|Ő|Ơ|Ø|Ǿ/g,'O'],[/ö|ò|ó|ô|õ|ō|ŏ|ǒ|ő|ơ|ø|ǿ|º|°/g,'o'],
			[/Ŕ|Ŗ|Ř/g,'R'],[/ŕ|ŗ|ř/g,'r'],
			[/Ś|Ŝ|Ş|Š/g,'S'],[/ś|ŝ|ş|š|ſ/g,'s'],
			[/Ţ|Ť|Ŧ/g,'T'],[/ţ|ť|ŧ/g,'t'],
			[/Ü|Ù|Ú|Û|Ũ|Ū|Ŭ|Ů|Ű|Ų|Ư|Ǔ|Ǖ|Ǘ|Ǚ|Ǜ/g,'U'],[/ü|ù|ú|û|ũ|ū|ŭ|ů|ű|ų|ư|ǔ|ǖ|ǘ|ǚ|ǜ/g,'u'],
			[/Ý|Ÿ|Ŷ/g,'Y'],[/ý|ÿ|ŷ/g,'y'],
			[/Ŵ/g,'W'],[/ŵ/g,'w'],
			[/Ź|Ż|Ž/g,'Z'],[/ź|ż|ž/g,'z'],
			[/Æ|Ǽ/g,'AE'],[/ß/g,'ss'],[/Ĳ/g,'IJ'],[/ĳ/g,'ij'],[/Œ/g,'OE'],[/ƒ/g,'f'],[/&/g,'et'],[/þ/,'th'],[/Þ/,'TH']
		].sbEach(function(i,pattern){
			t=t.replace(pattern[0],pattern[1]);
		});
		return t;
	},
	sbSlug:function(replacement){
		if(replacement===undefined) replacement='-';
		var returnval=this;
		return returnval.sbTrim().sbRemoveSpecialChars()
			.replace(/([^\d\.])\.+([^\d\.]|$)/g,'$1 $2')
			.replace(/[^\w\d\.]/g,' ')
			.sbTrim()
			.replace(/\s+/g,replacement)
			.replace(new RegExp('^'+RegExp.sbEscape(replacement)+'+|'+RegExp.sbEscape(replacement)+'+$'),'');
	},

	/* http://phpjs.org/functions/strip_tags:535 */
	sbStripTags:function(allowed){
		var input=this;
		allowed = (((allowed || "") + "").toLowerCase().match(/<[a-z][a-z0-9]*>/g) || []).join(''); // making sure the allowed arg is a string containing only tags in lowercase (<a><b><c>)
		var tags = /<\/?([a-z][a-z0-9]*)\b[^>]*>/gi,
	    	commentsAndPhpTags = /<!--[\s\S]*?-->|<\?(?:php)?[\s\S]*?\?>/gi,
	    	/*  http://www.tutorialchip.com/tutorials/html5-block-level-elements-complete-list/ */
	    	blockTags='article,aside,blockquote,br,dd,div,dl,dt,embed,fieldset,figcaption,figure,footer,form,h1,h2,h3,h4,h5,h6,header,hgroup,hr,li,menu,nav,ol,output,p,pre,section,table,tbody,textarea,tfoot,th,thead,tr,ul'.split(',');
		return input.replace(commentsAndPhpTags, '').replace(tags,function($0,$1){
			var tag=$1.toLowerCase();
			return allowed.indexOf('<' + tag + '>') > -1 ? $0 : blockTags.sInArray(tag) ? "\n":'';
		}).replace(/\n+\s*\n*/,"\n");
	},
	sbWordsCount:function(){
		var t=this.replace(/\.\.\./g, ' ') // convert ellipses to spaces
			.replace(/[0-9.(),;:!?%#$?\'\"_+=\\\/-]*/g,'') // remove numbers and punctuation
			;
		var wordArray = t.match(/[\w\u2019\'\-]+/g); //u2019 == &rsquo;
		if(wordArray) return wordArray.length;
		return 0;
	},
	sbHtmlWordsCount:function(){
		return this.replace(/<.[^<>]*?>/g, ' ').replace(/&nbsp;|&#160;/gi, ' ') // remove html tags and space chars
			.replace(/(\w+)(&.+?;)+(\w+)/, "$1$3").replace(/&.+?;/g, ' ').sbWordsCount();
	},
	sbFormat:function(){
		return this.sbVFormat(arguments);
	},
	sbVFormat:function(args){
		var number=0;
		return this.replace(/%s/g,function(match){ return args[number++] || ''; });
	},
	sNl2br:function(){return this.replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1'+ '<br />' +'$2');},
	sBr2nl:function(){return this.replace(/<br\s*\/?>/mg,'\n');}
	
});S.extendsPrototype(Array,{
	//sHas
	sInArray:function(searchElement,i){
		if(this.indexOf) return this.indexOf(searchElement,i);
		/* See jQuery.inArray */
		var t=this,l=t.length;
		fromIndex=i ? i < 0 ? Math.max( 0, l + i ) : i : 0;
		for(; i < l; i++ )
			if(i in t && array[i] === searchElement) return i;
		return -1;
	},
	sbEach:function(callback){
		return $.each(this,callback);
	},
	sbFindBy:function(propName,val){
		var k=this.sbFindKeyBy(propName,val);
		if(k===false) return k;
		return this[k];
	},
	sbFindKeyBy:function(propName,val){
		var res=false;
		this.sbEach(function(k,v){
			if(v[propName] == val){
				res=k;
				return false;
			}
		});
		return res;
	},
	sbSortBy:function(propName,asc,sortFunc){
		if(!$.isFunction(sortFunc)) sortFunc=S.arraysort[sortFunc===undefined?'':sortFunc];
		return this.sort(function(a,b){
			if(asc) return sortFunc(a[propName],b[propName]);
			return sortFunc(b[propName],a[propName]);
		});
	},
	sbEqualsTo:function(array){
		if(typeof array !== 'array' || this.length != array.length) return false;
		for (var i = 0; i < array.length; i++) {
	        /*if (this[i].compare) { 
	            if (!this[i].compare(testArr[i])) return false;
	        }*/
	        if(this[i] !== array[i]) return false;
	    }
	    return true;
	},
	sbLast:function(){return this[this.length-1]}
});
RegExp.sbEscape=function(value){
	return value.replace( /([\^\$\(\)\[\]\{\}\*\.\+\?\|\\])/gi, "\\$1" );
};

(function($){
	$(document).on('focus','input.submit,button,.button',function(){ $(this).delay(1200).blur() });

	/* https://github.com/bgrins/bindWithDelay/blob/master/bindWithDelay.js */
	$.fn.delayedBind=function(delay,eventType,eventData,handler,throttle){
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
	};
	
	$.fn.delayedKeyup=function(delay,handler){
		if($.isFunction(delay)){
			handler=delay;
			delay=200;
		}
		return $(this).delayedBind(delay,'keyup',undefined,handler);
	};
	$.fn.sHide=function(){ this.addClass('hidden'); return this; };
	$.fn.sShow=function(){ this.removeClass('hidden'); return this; };
})(jQuery);

function handleError(e){
	//alert("An error has occurred!\n"+e);
	if(console) console.log(e);
	return true;
}
//window.onerror = handleError;

/*function extendBasic(subclass,superclass,basicsuperclass,varName,extendsPrototype){
	extend(subclass,superclass,extendsPrototype);
	for(var i in basicsuperclass.prototype)
		subclass.prototype[i]=function(){return basicsuperclass.prototype[i].apply(this[varName],arguments);}
}*/
(function($){
	var methods={
		beforeSubmit:function(){
			this.find('input.default').removeClass('default').val('');
			return this;
		},
		afterSubmit:function(){
			this.find('input.default').each(function(){this.val(this.title);});
			return this;
		}
	};
	$.fn.defaultInput = function(method){
		if(!method){
			var inputs;
			if(this.is('input')){
				inputs=this.addClass('default');
			}else inputs=this.find('input.default');
			
			inputs.each(function(){
				var $this=$(this);
				$this.val(function(i,v){if(!$this.is(':focus') && (v=='' || v==this.title)) return this.title; $this.removeClass('default');return v;})
				.focusin(function(){if($this.hasClass('default') || $this.val()===this.title) $this.removeClass('default').val('');})
				.focusout(function(e){if(!$this.hasClass('default') && $this.val()=='') $this.addClass('default').val($this.attr('title'));})
				.change(function(e){if($this.hasClass('default')) $this.val()!='' ? $this.removeClass('default') : $this.val($this.attr('title'))});
			});
			return inputs;
		}else if(methods[method]){
			//return methods[method].apply(this,Array.prototype.slice.call(arguments,1));
			return methods[method].apply(this);
		}
	};
	$.fn.reset=function(){
		this.find('input[type=text],input[type=email],input[type=url],input[type=number],input[type=search],input[type=password],textarea,select').val('');
		return this;
	};
	
	var num=0;
	$.fn.ajaxForm=function(url,success,beforeSubmit,error){
		if(!error) error=function(jqXHR, textStatus){alert('Error: '+textStatus);};
		var form=this,submit;
		if(!url) url=form.attr('action');
		this.unbind('submit').submit(function(evt){
			evt.preventDefault();
			evt.stopPropagation();
			submit=form.find(':submit');
			form.fadeTo(180,0.4);
			if(window.tinyMCE!==undefined) tinyMCE.triggerSave();
			if((beforeSubmit && beforeSubmit()===false) || (form.data('ht5ifv')!==undefined && !form.ht5ifv('valid'))){
				form.stop().fadeTo(0,1)
				return false;
			}
			var currentNum=num++,
			 ajaxOptions={
				type:'post',cache:false,
				beforeSend:function(){submit.hide();submit.parent().append($('<span/>').attr({id:'imgLoadingSubmit'+currentNum,'class':"img imgLoading"}));},
				data:form.serialize(),
				complete:function(){submit.show().blur();$('#imgLoadingSubmit'+currentNum).remove();form.fadeTo(150,1)},
				error:error
			};
			if(success) ajaxOptions.success=success;
			$.ajax(url,ajaxOptions);
			return false;
		})/*.find(':submit').unbind('click').click(function(){
			//var validator=form.data('validator');
			//if(validator && !validator.checkValidity()) return false;
			//submit=$(this);
			return true;
		});*/
		return this;
	};
	
	$.fn.ajaxChangeForm=function(url,success,beforeSubmit,error){
		if(!error) error=function(jqXHR, textStatus){alert(i18nc['Error:']+' '+textStatus);};
		var form=this;
		this.unbind('change').change(function(){
			form.fadeTo(180,0.4);
			if(beforeSubmit) beforeSubmit();
			if(window.tinymce) tinymce.triggerSave();
			if(form.data('ht5ifv')!==undefined && !form.ht5ifv('valid')){
				form.fadeTo(0,1)
				return false;
			}
			var currentNum=num++,
			 ajaxOptions={
				type:'post',cache:false,
				beforeSend:function(){form.before($('<span/>').attr({id:'imgLoadingForm'+currentNum,'class':"img imgLoading"}).offset({left:form.position().left+form.width()-16}).css({position:'absolute','z-index':5}));},
				data:form.serialize(),
				complete:function(){$('#imgLoadingForm'+currentNum).remove();form.fadeTo(150,1)},
				error:error
			};
			if(success) ajaxOptions.success=success;
			$.ajax(url,ajaxOptions);
			return false;
		});
	}
})(jQuery);


S.HForm=function(modelName,formAttributes,tagContainer,options){
	formAttributes=S.extendsObj({action:'',method:'post'},formAttributes);
	this.$=$('<form/>').attr(formAttributes);
	this.modelName=modelName||false;
	this.name=modelName?modelName.sbLcFirst():false;
	this.tagContainer=tagContainer!==undefined?tagContainer:'div';
};
S.HForm.prototype={
	end:function(submit){
		if(submit || submit==undefined) this.$.append(this.submit(submit))
		return this.$;
	},
	_container:function(res,defaultClass,attributes,labelFor,label,appendLabel){
		if(this.tagContainer && (attributes || attributes===undefined)){
			attributes=S.extendsObj({'class':defaultClass},attributes);
			res=$('<'+this.tagContainer+'/>').html(res);
			if(attributes.before){ res.prepend(attributes.before); delete attributes.before; }
			if(attributes.after){ res.append(attributes.after); delete attributes.after; }
			res.attr(attributes);
			if(label) res[appendLabel?'append':'prepend']($('<label/>').attr('for',labelFor).text(label));
		}
		return res;
	},
	_input:function(name,type,label,inputAttributes,containerAttributes){
		inputAttributes=S.extendsObj({
			id:(this.modelName ? this.modelName : 'Input')+name.sbUcFirst()+(inputAttributes&&inputAttributes.idSuffix?inputAttributes.idSuffix:''),
			name:(this.name ? this.name+'['+name+']' : name)
		},inputAttributes);
		delete inputAttributes.idSuffix;
	
		var res=$('<input type="'+type+'"/>');
		
		if(inputAttributes.before){ res.before(inputAttributes.before); delete inputAttributes.before; }
		if(inputAttributes.after){ res.after(inputAttributes.after); delete inputAttributes.after; }
		
		res.attr(inputAttributes);
		
		return this._container(res,'input '+(type!=='text'?'text ':'')+type,containerAttributes,inputAttributes.id,label);
	},
	inputText:function(name,label,inputAttributes,containerAttributes){
		return this._input(name,'text',label,inputAttributes,containerAttributes);
	},
	appendInputText:function(name,label,inputAttributes,containerAttributes){
		this.$.append(this.inputText(name,label,inputAttributes,containerAttributes));
		return this;
	},
	inputPassword:function(name,label,inputAttributes,containerAttributes){
		return this._input(name,'password',label,inputAttributes,containerAttributes);
	},
	appendInputPassword:function(name,label,inputAttributes,containerAttributes){
		this.$.append(this.inputPassword(name,label,inputAttributes,containerAttributes));
		return this;
	},
	inputNumber:function(name,label,inputAttributes,containerAttributes){
		return this._input(name,'number',label,inputAttributes,containerAttributes);
	},
	appendInputNumber:function(name,label,inputAttributes,containerAttributes){
		this.$.append(this.inputNumber(name,label,inputAttributes,containerAttributes));
		return this;
	},
	inputUrl:function(name,label,inputAttributes,containerAttributes){
		return this._input(name,'url',label,inputAttributes,containerAttributes);
	},
	appendInputUrl:function(name,label,inputAttributes,containerAttributes){
		this.$.append(this.inputUrl(name,label,inputAttributes,containerAttributes));
		return this;
	},
	inputEmail:function(name,label,inputAttributes,containerAttributes){
		return this._input(name,'email',label,inputAttributes,containerAttributes);
	},
	appendInputEmail:function(name,label,inputAttributes,containerAttributes){
		this.$.append(this.inputEmail(name,label,inputAttributes,containerAttributes));
		return this;
	},


	select:function(name,list,options,inputAttributes,containerAttributes){
		options=S.extendsObj({empty:undefined},options);
		inputAttributes=S.extendsObj({
			id:(this.modelName ? this.modelName : 'Select')+name.sbUcFirst()+(inputAttributes&&inputAttributes.idSuffix?inputAttributes.idSuffix:''),
			name:(this.name ? this.name+'['+name+']' : name)
		},inputAttributes);
		delete inputAttributes.idSuffix;
	
		var select=$('<select/>').attr(inputAttributes),t=this;
		if(options.empty != undefined)
			select.append($('<option value=""/>').text(options.empty));
		
		$.each(list,function(k,v){
			if(v) select.append(t.option(k,v,options.selected));
		});
		
		return this._container(select,'input select',containerAttributes,inputAttributes.id,options.label);
	},
	appendSelect:function(name,list,options,inputAttributes,containerAttributes){
		this.$.append(this.select(name,list,options,inputAttributes,containerAttributes));
		return this;
	},

	option:function(value,name,selected){
		return $('<option'+(selected===undefined?'':(selected==value?' selected="selected"':''))+'/>').attr('value',value).text(name);
	},


	selectHour:function(name,options,inputAttributes,containerAttributes){
		return this.select(name,['0',1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23],options,inputAttributes,containerAttributes);
	},
	appendSelectHour:function(name,options,inputAttributes,containerAttributes){
		this.$.append(this.selectHour(name,options,inputAttributes,containerAttributes));
		return this;
	},

	selectHourMorning:function(name,options,inputAttributes,containerAttributes){
		return this.select(name,['0',1,2,3,4,5,6,7,8,9,10,11,12],options,inputAttributes,containerAttributes);
	},
	appendSelectHourMorning:function(name,options,inputAttributes,containerAttributes){
		this.$.append(this.selectHourMorning(name,options,inputAttributes,containerAttributes));
		return this;
	},

	selectHourAfternoon:function(name,options,inputAttributes,containerAttributes){
		return this.select(name,{12:12,13:13,14:14,15:15,16:16,17:17,18:18,19:19,20:20,21:21,22:22,23:23},options,inputAttributes,containerAttributes);
	},
	appendSelectHourAfternoon:function(name,options,inputAttributes,containerAttributes){
		this.$.append(this.selectHourAfternoon(name,options,inputAttributes,containerAttributes));
		return this;
	},
	
	
	textarea:function(name,label,inputAttributes,containerAttributes){
		inputAttributes=S.extendsObj({
			id:(this.modelName ? this.modelName : 'Textarea')+name.sbUcFirst()+(inputAttributes&&inputAttributes.idSuffix?inputAttributes.idSuffix:''),
			name:(this.name ? this.name+'['+name+']' : name)
		},inputAttributes);
		delete inputAttributes.idSuffix;
		
		var res=$('<textarea/>');
		if(inputAttributes.value){
			res.text(inputAttributes.value);
			delete inputAttributes.value;
		}
		res.attr(inputAttributes);
		
		return this._container(res,'input textarea',containerAttributes,inputAttributes.id,label);
	},
	appendTextarea:function(name,options,inputAttributes,containerAttributes){
		this.$.append(this.textarea(name,options,inputAttributes,containerAttributes));
		return this;
	},


	checkbox:function(name,label,attributes,containerAttributes){
		attributes=S.extendsObj({
			type:'checkbox',
			id:(this.modelName ? this.modelName : 'Checkbox')+name.sbUcFirst()+(attributes&&attributes.idSuffix?attributes.idSuffix:''),
			name:(this.name ? this.name+'['+name+']' : name)
		},attributes);
		delete attributes.idSuffix;
		attributes.checked ? attributes.checked='checked' : delete attributes.checked;
		
		var res=$('<input/>').attr(attributes);
		return this._container(res,'input checkbox',containerAttributes,attributes.id,label,true);
	},
	appendCheckbox:function(name,options,inputAttributes,containerAttributes){
		this.$.append(this.checkbox(name,options,inputAttributes,containerAttributes));
		return this;
	},
	
	
	submit:function(title,attributes,containerAttributes){
		if(title===undefined) title=i18nc.Save;
		attributes=S.extendsObj({'class':'submit'},attributes);
		var str=$('<input type="submit"/>').attr('value',title).attr(attributes);
		if(this.tagContainer !== 'div' || containerAttributes!==undefined)
			str=$('<'+this.tagContainer+' class="submit"/>').attr(containerAttributes||{}).html(str); 
		return str;
	},
	appendSubmit:function(title,attributes,containerAttributes){
		this.$.append(this.submit(title,attributes,containerAttributes));
		return this;
	}
};S.tinymce={
	attrs:{},
	
	load:function(plugins){
		if(window.tinymce===undefined){
			S.loadSyncScript(webdir+'js/tinymce.js');
			S.loadSyncScript(webdir+'js/tinymce.'+S.lang+'.js');
			// bug for ajax partial load - document.ready should not be necessary, but we never know !
			/*S.ready(function(){tinymce.dom.Event._pageInit(window)});*/
		}
		return this;
	},
	
	init:function(w,h,barType,withImageManager){
		this.attrs={
			theme:'advanced',language:S.lang,
			skin:'o2k7',skin_variant:'black',
			
			theme_advanced_toolbar_location:'top', theme_advanced_toolbar_align:'left', theme_advanced_statusbar_location:'bottom',//  theme_advanced_resizing:false,
			document_base_url:basedir,
			width:w, height:h,
			
			font_size_style_values:"7pt,8pt,9pt,10pt,11pt,12pt,14pt,18pt,24pt,36pt",
			
			convert_urls:false,
			
			entity_encoding:'raw',
			convert_fonts_to_spans:true,
			
			style_formats:[
				{title:'Bold text',selector:'span,p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes:'bold'},
				{title:'Italic text',selector:'span,p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes:'italic'},
				{title:'Clear',selector:'span,p,h1,h2,h3,h4,h5,h6,div,ul,table,img', classes:'clear'},
				{title:'Clearfix',selector:'div', classes:'clearfix'},
			],
			formats:{
				alignleft:{selector:'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes:'alignLeft'},
				bold:{inline:'strong'},italic:{inline:'i'},
				alignleft:{selector:'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes:'alignLeft'},
				aligncenter:{selector:'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes:'center'},
				alignright:{selector:'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes:'alignRight'},
			},
			
			doctype : '<!DOCTYPE html>',
			verify_css_classes:true, apply_source_formatting:false,
			theme_advanced_styles:'Center=center;Clear=clear;Pointer=pointer;Smallinfo=smallinfo;Margin-Right 10=mr10;Margin-Right 20=mr20;Margin-Left 10=ml10;Margin-Left 20=ml20;Margin-Top 10=mt10;Margin-Top 20=mt20;',
			
			
			content_css:webdir+'css/main.css',
			body_class:'variable'
		};
		
		if(barType==='basic'){
			this.attrs.plugins="springbok,style,fullscreen,inlinepopups,contextmenu,advlink"+(withImageManager?',advimage,springbokgallery':'');
			this.attrs.theme_advanced_buttons1="fullscreen,code,|,bold,italic,underline,strikethrough,|,styleselect,fontsizeselect,,forecolor,|,bullist,numlist,|,link,unlink"+(withImageManager?',image,springbokAddImage':'')+',|,removeformat,visualaid';
			this.attrs.theme_advanced_buttons2=this.attrs.theme_advanced_buttons3="";
			//attrs.theme_advanced_buttons4="insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,pagebreak";
		}else if(barType==='basicAdvanced'){
			this.attrs.plugins="springbok,style,fullscreen,inlinepopups,contextmenu,springboklink"+(withImageManager?',advimage,springbokgallery':'')+',springbokclean';
			this.attrs.theme_advanced_buttons1="fullscreen,code,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,,styleselect,fontsizeselect,formatselect,|,bullist,numlist,sub,sup,|,link,unlink"+(withImageManager?',image,springbokAddImage':'')+',|,removeformat,visualaid';
			this.attrs.theme_advanced_buttons2=this.attrs.theme_advanced_buttons3="";
		}else{
			this.attrs.plugins="springbok,pagebreak,style,fullscreen,table,advimage,advlink,inlinepopups,media,searchreplace,contextmenu,paste"+(withImageManager?',springbokgallery':'')+',springbokclean';
			this.attrs.theme_advanced_buttons1="fullscreen,code,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,fontsizeselect,|,cut,copy,paste,pastetext,pasteword,|,cleanup,help";
			this.attrs.theme_advanced_buttons2="bullist,numlist,|,sub,sup,|,link,unlink,anchor,image,"+(withImageManager?'springbokAddImage,':'')+"charmap,media,syntaxhl,|,forecolor,backcolor,|,search,replaceoutdent,indent,blockquote,|,hr,tablecontrols,|,undo,redo";
			this.attrs.theme_advanced_buttons3="";
		}
		
		this.load(this.attrs.plugins);
		
		/*        this.attrs.style_formats=[
                //{title : 'Bleu canard', inline:'span', classes:'special'},
                {title: 'Bleu canard',                inline:'span',styles:{color:'#00959e'}},
		*/
		
		return this;
	},
	wordCount:function(){ this.attrs.plugins+=',wordcount'; return this; },
	syntaxhl:function(){ this.attrs.plugins+=',syntaxhl'; return this; },
	autolink:function(){ this.attrs.plugins+=',autolink'; return this; },
	autoSave:function(){ this.attrs.plugins+=',autosave'; this.attrs.theme_advanced_buttons1+=",|,restoredraft"; return this; },
	simpleText:function(){
		this.attrs.force_br_newlines=true;
		this.attrs.force_p_newlines=false;
		this.attrs.forced_root_block='';
		return this;
	},
	
	absoluteUrls:function(withDomain){
		this.attrs.convert_urls=true;
		this.attrs.relative_urls=false;
		this.attrs.remove_script_host=withDomain?true:false;
		this.attrs.document_base_url="http://www.site.com/"+base_url;
	},
	
	addAttr:function(name,value){
		this.attrs[name]=value;
		return this;
	},

	forEmail:function(){
		S.extendsObj(this.attrs,{
			/* border=0 for img */
			extended_valid_elements: "img[class|src|border=0|alt|title|hspace|vspace|width|height|align|onmouseover|onmouseout|name]"
		});
		return this;
	},
	
	withAjaxFileManager:function(){
		this.attrs.file_browser_callback="ajaxfilemanager";
		return this;
	},
	
	onChange:function(callback){
		this.attrs.onchange_callback=callback;
		return this;
	},
	
	handleEvent:function(callback){
		this.attrs.handle_event_callback=callback;
		return this;
	},
	
	validXHTML:function(){
		this.attrs.entity_encoding='named';
		this.attrs.valid_elements =""
+"a[accesskey|charset|class|coords|dir<ltr?rtl|href|hreflang|id|lang|name|rel|rev|shape<circle?default?poly?rect|style|tabindex|title|target|type],"
+"abbr[class|dir<ltr?rtl|id|lang|le|title],"
+"acronym[class|dir<ltr?rtl|id|id|lang|le|title],"
+"address[class|align|dir<ltr?rtl|id|lang|le|title],"
+"area[accesskey|alt|class|coords|dir<ltr?rtl|href|id|lang|nohref<nohref|shape<circle?default?poly?rect|style|tabindex|title|target],"
+"base[href|target],"
+"basefont[color|face|id|size],"
+"bdo[class|dir<ltr?rtl|id|lang|style|title],"
+"big[class|dir<ltr?rtl|id|lang|le|title],"
+"blockquote[cite|class|dir<ltr?rtl|id|lang|le|title],"
+"body[alink|background|bgcolor|class|dir<ltr?rtl|id|lang|link|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onload|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|onunload|style|title|text|vlink],"
+"br[class|clear<all?left?none?right|id|style|title],"
+"button[accesskey|class|dir<ltr?rtl|disabled<disabled|id|lang|name|style|tabindex|title|type|value],"
+"caption[align<bottom?left?right?top|class|dir<ltr?rtl|id|lang|le|title],"
+"center[class|dir<ltr?rtl|id|lang|le|title],"
+"cite[class|dir<ltr?rtl|id|lang|le|title],"
+"code[class|dir<ltr?rtl|id|lang|le|title],"
+"col[align<center?char?justify?left?right|char|charoff|class|dir<ltr?rtl|id|lang|span|style|title|valign<baseline?bottom?middle?top|width],"
+"colgroup[align<center?char?justify?left?right|char|charoff|class|dir<ltr?rtl|id|lang|span|style|title|valign<baseline?bottom?middle?top|width],"
+"dd[class|dir<ltr?rtl|id|lang|le|title],"
+"del[cite|class|datetime|dir<ltr?rtl|id|lang|le|title],"
+"dfn[class|dir<ltr?rtl|id|lang|le|title],"
+"dir[class|compact<compact|dir<ltr?rtl|id|lang|le|title],"
+"div[align<center?justify?left?right|class|dir<ltr?rtl|id|lang|le|title],"
+"dl[class|compact<compact|dir<ltr?rtl|id|lang|le|title],"
+"dt[class|dir<ltr?rtl|id|lang|le|title],"
+"em/i[class|dir<ltr?rtl|id|lang|le|title],"
+"fieldset[class|dir<ltr?rtl|id|lang|le|title],"
+"font[class|color|dir<ltr?rtl|face|id|lang|size|style|title],"
+"form[accept|accept-charset|action|class|dir<ltr?rtl|enctype|id|lang|method<get?post|name|onreset|onsubmit|style|title|target],"
+"frame[class|frameborder|id|longdesc|marginheight|marginwidth|name|noresize<noresize|scrolling<auto?no?yes|src|style|title],"
+"frameset[class|cols|id|onload|onunload|rows|style|title],"
+"h1[align<center?justify?left?right|class|dir<ltr?rtl|id|lang|le|title],"
+"h2[align<center?justify?left?right|class|dir<ltr?rtl|id|lang|le|title],"
+"h3[align<center?justify?left?right|class|dir<ltr?rtl|id|lang|le|title],"
+"h4[align<center?justify?left?right|class|dir<ltr?rtl|id|lang|le|title],"
+"h5[align<center?justify?left?right|class|dir<ltr?rtl|id|lang|le|title],"
+"h6[align<center?justify?left?right|class|dir<ltr?rtl|id|lang|le|title],"
+"head[dir<ltr?rtl|lang|profile],"
+"hr[class|dir<ltr?rtl|id|lang|noshade<noshade|size|style|title|width],"
+"html[dir<ltr?rtl|lang|version],"
+"iframe[align<bottom?left?middle?right?top|class|frameborder|height|id|longdesc|marginheight|marginwidth|name|scrolling<auto?no?yes|src|style|title|width],"
+"img[align<bottom?left?middle?right?top|alt|border|class|dir<ltr?rtl|height|hspace|id|ismap<ismap|lang|longdesc|name|src|style|title|usemap|vspace|width],"
+"input[accept|accesskey|align<bottom?left?middle?right?top|alt|checked<checked|class|dir<ltr?rtl|disabled<disabled|id|ismap<ismap|lang|maxlength|name|onselect|readonly<readonly|size|src|style|tabindex|title|type<button?checkbox?file?hidden?image?password?radio?reset?submit?text|usemap|value],"
+"ins[cite|class|datetime|dir<ltr?rtl|id|lang|le|title],"
+"isindex[class|dir<ltr?rtl|id|lang|prompt|style|title],"
+"kbd[class|dir<ltr?rtl|id|lang|le|title],"
+"label[accesskey|class|dir<ltr?rtl|for|id|lang|style|title],"
+"legend[align<bottom?left?right?top|accesskey|class|dir<ltr?rtl|id|lang|le|title],"
+"li[class|dir<ltr?rtl|id|lang|le|title|type|value],"
+"link[charset|class|dir<ltr?rtl|href|hreflang|id|lang|media|rel|rev|style|title|target|type],"
+"map[class|dir<ltr?rtl|id|lang|name|le|title],"
+"menu[class|compact<compact|dir<ltr?rtl|id|lang|le|title],"
+"meta[content|dir<ltr?rtl|http-equiv|lang|name|scheme],"
+"noframes[class|dir<ltr?rtl|id|lang|le|title],"
+"noscript[class|dir<ltr?rtl|id|lang|style|title],"
+"object[align<bottom?left?middle?right?top|archive|border|class|classid|codebase|codetype|data|declare|dir<ltr?rtl|height|hspace|id|lang|name|standby|style|tabindex|title|type|usemap|vspace|width],"
+"ol[class|compact<compact|dir<ltr?rtl|id|lang|start|style|title|type],"
+"optgroup[class|dir<ltr?rtl|disabled<disabled|id|label|lang|le|title],"
+"option[class|dir<ltr?rtl|disabled<disabled|id|label|lang|selected<selected|style|title|value],"
+"p[align<center?justify?left?right|class|dir<ltr?rtl|id|lang|le|title],"
+"param[id|name|type|value|valuetype<DATA?OBJECT?REF],"
+"pre/listing/plaintext/xmp[align|class|dir<ltr?rtl|id|lang|le|title|width],"
+"q[cite|class|dir<ltr?rtl|id|lang|le|title],"
+"s[class|dir<ltr?rtl|id|lang|le|title],"
+"samp[class|dir<ltr?rtl|id|lang|le|title],"
+"script[charset|defer|language|src|type],"
+"select[class|dir<ltr?rtl|disabled<disabled|id|lang|multiple<multiple|name|size|style|tabindex|title],"
+"small[class|dir<ltr?rtl|id|lang|le|title],"
+"span[align<center?justify?left?right|class|dir<ltr?rtl|id|lang|le|title],"
+"strike[class|class|dir<ltr?rtl|id|lang|le|title],"
+"strong/b[class|dir<ltr?rtl|id|lang|le|title],"
+"style[dir<ltr?rtl|lang|media|title|type],"
+"sub[class|dir<ltr?rtl|id|lang|le|title],"
+"sup[class|dir<ltr?rtl|id|lang|le|title],"
+"table[bgcolor|border|cellpadding|cellspacing|class|dir<ltr?rtl|frame|height|id|lang|rules|style|summary|title|width],"
+"tbody[align<center?char?justify?left?right|char|class|charoff|dir<ltr?rtl|id|lang|le|title|valign<baseline?bottom?middle?top],"
+"td[abbr|align<center?char?justify?left?right|axis|bgcolor|char|charoff|class|colspan|dir<ltr?rtl|headers|height|id|lang|nowrap<nowrap|rowspan|scope<col?colgroup?row?rowgroup|style|title|valign<baseline?bottom?middle?top|width],"
+"textarea[accesskey|class|cols|dir<ltr?rtl|disabled<disabled|id|lang|name|onselect|readonly<readonly|rows|style|tabindex|title],"
+"tfoot[align<center?char?justify?left?right|char|charoff|class|dir<ltr?rtl|id|lang|le|title|valign<baseline?bottom?middle?top],"
+"th[abbr|align<center?char?justify?left?right|axis|bgcolor|char|charoff|class|colspan|dir<ltr?rtl|headers|height|id|lang|nowrap<nowrap|rowspan|scope<col?colgroup?row?rowgroup|style|title|valign<baseline?bottom?middle?top|width],"
+"thead[align<center?char?justify?left?right|char|charoff|class|dir<ltr?rtl|id|lang|le|title|valign<baseline?bottom?middle?top],"
+"title[dir<ltr?rtl|lang],"
+"tr[abbr|align<center?char?justify?left?right|bgcolor|char|charoff|class|rowspan|dir<ltr?rtl|id|lang|le|title|valign<baseline?bottom?middle?top],"
+"tt[class|dir<ltr?rtl|id|lang|le|title],"
+"u[class|dir<ltr?rtl|id|lang|le|title],"
+"ul[class|compact<compact|dir<ltr?rtl|id|lang|le|title|type],"
+"var[class|dir<ltr?rtl|id|lang|le|title]";
		return this;
	},
	
	validSimpleHTML:function(){
		this.attrs.valid_elements=""
+"a[accesskey|charset|class|coords|dir<ltr?rtl|href|hreflang|id|lang|name|rel|rev|shape<circle?default?poly?rect|style|tabindex|title|target|type],"
+"br[class|id|style|title],"
+"dd[class|dir<ltr?rtl|id|lang|le|title],"
+"del[cite|class|datetime|dir<ltr?rtl|id|lang|le|title],"
+"div[class|dir<ltr?rtl|id|lang|le|title],"
+"dl[class|compact<compact|dir<ltr?rtl|id|lang|le|title],"
+"dt[class|dir<ltr?rtl|id|lang|le|title],"
+"hr[class|dir<ltr?rtl|id|lang|noshade<noshade|size|style|title|width],"
+"ins[cite|class|datetime|dir<ltr?rtl|id|lang|le|title],"
+"li[class|dir<ltr?rtl|id|lang|le|title|type|value],"
+"link[charset|class|dir<ltr?rtl|href|hreflang|id|lang|media|rel|rev|style|title|target|type],"
+"ol[class|compact<compact|dir<ltr?rtl|id|lang|start|style|title|type],"
+"p[align<center?justify?left?right|class|dir<ltr?rtl|id|lang|le|title],"
+"pre/listing/plaintext/xmp[align|class|dir<ltr?rtl|id|lang|le|title|width],"
+"small[class|dir<ltr?rtl|id|lang|le|title],"
+"span[align<center?justify?left?right|class|dir<ltr?rtl|id|lang|le|title],"
+"strong/b[class|dir<ltr?rtl|id|lang|le|title],"
+"sub[class|dir<ltr?rtl|id|lang|le|title],"
+"sup[class|dir<ltr?rtl|id|lang|le|title],"
+"table[bgcolor|border|cellpadding|cellspacing|class|dir<ltr?rtl|frame|height|id|lang|rules|style|summary|title|width],"
+"tbody[align<center?char?justify?left?right|char|class|charoff|dir<ltr?rtl|id|lang|le|title|valign<baseline?bottom?middle?top],"
+"td[abbr|align<center?char?justify?left?right|axis|bgcolor|char|charoff|class|colspan|dir<ltr?rtl|headers|height|id|lang|nowrap<nowrap|rowspan|scope<col?colgroup?row?rowgroup|style|title|valign<baseline?bottom?middle?top|width],"
+"tfoot[align<center?char?justify?left?right|char|charoff|class|dir<ltr?rtl|id|lang|le|title|valign<baseline?bottom?middle?top],"
+"th[abbr|align<center?char?justify?left?right|axis|bgcolor|char|charoff|class|colspan|dir<ltr?rtl|headers|height|id|lang|nowrap<nowrap|rowspan|scope<col?colgroup?row?rowgroup|style|title|valign<baseline?bottom?middle?top|width],"
+"thead[align<center?char?justify?left?right|char|charoff|class|dir<ltr?rtl|id|lang|le|title|valign<baseline?bottom?middle?top],"
+"tr[abbr|align<center?char?justify?left?right|bgcolor|char|charoff|class|rowspan|dir<ltr?rtl|id|lang|le|title|valign<baseline?bottom?middle?top],"
+"u[class|dir<ltr?rtl|id|lang|le|title],"
+"ul[class|compact<compact|dir<ltr?rtl|id|lang|le|title|type]";
		return this;
	},
	
	validBasicHTML:function(){
		this.attrs.valid_elements=""
+"a[accesskey|charset|class|coords|dir<ltr?rtl|href|hreflang|id|lang|name|rel|rev|shape<circle?default?poly?rect|style|tabindex|title|target|type],"
+"br[class|clear<all?left?none?right|id|style|title],"
+"dd[class|dir<ltr?rtl|id|lang|style|title],"
+"del[cite|class|datetime|dir<ltr?rtl|id|lang|style|title],"
+"dl[class|compact<compact|dir<ltr?rtl|id|lang|style|title],"
+"dt[class|dir<ltr?rtl|id|lang|style|title],"
+"ins[cite|class|datetime|dir<ltr?rtl|id|lang|style|title],"
+"li[class|dir<ltr?rtl|id|lang|style|title|type|value],"
+"ol[class|compact<compact|dir<ltr?rtl|id|lang|start|style|title|type],"
+"small[class|dir<ltr?rtl|id|lang|style|title],"
+"span[align<center?justify?left?right|class|dir<ltr?rtl|id|lang|style|title],"
+"strong/b[class|dir<ltr?rtl|id|lang|style|title],"
+"sub[class|dir<ltr?rtl|id|lang|style|title],"
+"sup[class|dir<ltr?rtl|id|lang|style|title],"
+"u[class|dir<ltr?rtl|id|lang|style|title],"
+"ul[class|compact<compact|dir<ltr?rtl|id|lang|style|title|type]";
		return this;
	},
	
	createForTextareas:function(){
		this.attrs.mode='textareas';
		return tinymce.init(this.attrs);
	},
	
	createForIds:function(ids){
		this.attrs.mode='exact';
		this.attrs.elements=ids;
		tinymce.init(this.attrs);
	},
	
	
	createForId:function(id){
		this.attrs.mode='none';
		this.attrs.elements=id;
		tinymce.init(this.attrs);
		setTimeout(function(){tinymce.execCommand('mceAddControl',false,id)},600);
	},
	
	ajaxSave:function(name,url){
		var ed=tinymce.get(name);
		ed.setProgressState(1);
		$.post(url,{text:ed.getContent()},function(){ed.setProgressState(0)});
	},
	
	
	switchtoHtml:function(editorId){
		var ed=tinyMCE.get(editorId),dom=tinymce.DOM,txtarea_el = dom.get(editorId);
		//tinyMCE.execCommand('mceRemoveControl',false,1);
		if(!ed || ed.isHidden()) return false;
		txtarea_el.style.height = ed.getContentAreaContainer().offsetHeight+20+'px';
		ed.hide();
	},
	switchtoVisual:function(editorId){
		var ed=tinyMCE.get(editorId)/*,dom=tinymce.DOM,txtarea_el = dom.get(editorId)*/;
		//tinyMCE.execCommand('mceAddControl',false,1);
		if(!ed || !ed.isHidden()) return false;
		ed.show();
	}
};
/* http://documentcloud.github.com/backbone/backbone.js */
(function($){
	var historyStarted=false,routeStripper=/^[#\/]*/,isIE=/msie [\w.]+/;
	S.history={
		options:{pushState:true},interval:50,
		
		start:function(){
			if (historyStarted) throw new Error("history has already been started");
			historyStarted = true;
			this.options=S.extendsObj({root:basedir.substr(1)},this.options);
			this._wantsPushState= !!this.options.pushState;
			this._hasPushState= !!(this.options.pushState && window.history && window.history.pushState);
			var fragment=this.getFragment(),docMode=document.documentMode,oldIE=(isIE.exec(navigator.userAgent.toLowerCase()) && (!docMode || docMode <= 7));
			if(oldIE){
				this.iframe = $('<iframe src="javascript:0" tabindex="-1" />').hide().appendTo('body')[0].contentWindow;
				this.navigate(fragment);
			}
			
			// Depending on whether we're using pushState or hashes, and whether
			// 'onhashchange' is supported, determine how we check the URL state.
			if(this._hasPushState) $(window).bind('popstate', this.checkUrl);
			else if(('onhashchange' in window) && !oldIE) $(window).bind('hashchange', this.checkUrl);
			else this._checkUrlInterval=setInterval(this.checkUrl, this.interval);
			
			// Determine if we need to change the base url, for a pushState link
			// opened by a non-pushState browser.
			this.fragment = fragment;
			var loc = window.location/*, atRoot=loc.pathname == this.options.root*/,hash;
			
			// If we've started off with a route from a `pushState`-enabled browser,
			// but we're currently in a browser that doesn't support it...
			/*if (this._wantsPushState && !this._hasPushState && !atRoot) {
				this.fragment = this.getFragment(null, true);
				window.location.replace(this.options.root + '#' + this.fragment);
				// Return immediately as browser will do redirect to new url
				return true;
			
			// Or if we've started out with a hash-based route, but we're currently
			// in a browser where it could be `pushState`-based instead...
			} else */if (this._wantsPushState && this._hasPushState && (hash=this.getHash())) {
				this.fragment = hash.replace(routeStripper, '');
				window.history.replaceState({fragment:this.fragment},document.title,loc.protocol + '//' + loc.host + basedir + this.fragment);
				return false;
			}
			return this._hasPushState || fragment===''?true:false;
		},
		
		getHash:function(windowOverride){
			var match = (windowOverride ? windowOverride.location : window.location).href.match(/#\/(.*)$/);
			return match ? match[1] : '';
		},
		getFragment:function(fragment, forcePushState){
			if(fragment == null){
				if(this._hasPushState || forcePushState){
					fragment=window.location.pathname;
					var search=window.location.search;
					if(search) fragment+=search;
					///if(fragment.indexOf(this.options.root) == 0) fragment = fragment.substr(this.options.root.length);
				}else fragment=this.getHash();
			}
			if(fragment.sbStartsWith(basedir)) fragment=fragment.substr(basedir.length);
			else if(fragment.sbStartsWith(this.options.root)) fragment=fragment.substr(this.options.root.length);
			return fragment.replace(routeStripper,'');
		},
		
		// Checks the current URL to see if it has changed, and if it has,
		// calls `loadUrl`, normalizing across the hidden iframe.
		checkUrl:function(e){
			var history=S.history, current = history.getFragment();
			if(current == history.fragment && history.iframe) current = history.getFragment(history.getHash(history.iframe));
			if(current == history.fragment) return false;
			if(history.iframe) history.navigate(current);
			history.loadUrl();
		},
		
		// Attempt to load the current URL fragment.
		loadUrl:function(fragmentOverride,state){
			var fragment = basedir+( this.fragment = this.getFragment(fragmentOverride));
			if(fragment){
				var a=$('a[href="'+fragment+'"]');
				a.length===0 ? S.redirect(fragment) : a.click();
			}
		},
		
		navigate:function(fragment,replace){
			var frag = (fragment || '').replace(routeStripper, ''),loc=window.location;
			if(frag.substr(0,1)==='?') frag=loc.pathname+frag;
			if(this.fragment == frag) return;
			if(window._gaq) _gaq.push(['_trackPageview',frag]);
			
			if(frag.sbStartsWith(basedir)) frag=frag.substr(basedir.length);
			else if(frag.sbStartsWith(this.options.root)) frag=frag.substr(this.options.root.length);
			if(this.fragment == frag) return;
			
			this.fragment=frag;
			if(this._hasPushState){
				//if(console && console.log) console.log('push: '+loc.protocol + '//' + loc.host + basedir+frag);
				//var title=document.title;
				window.history[replace?'replaceState':'pushState']({},document.title, loc.protocol+'//'+loc.host + basedir+frag);
			}else{
				this._updateHash(loc,frag,replace);
				if(this.iframe && (frag != this.getFragment(this.iframe.location.hash))){
					if(!replace) this.iframe.document.open().close();
					this._updateHash(this.iframe.location,frag,replace);
				}
			}
		},
		_updateHash:function(location,fragment,replace){
			replace ? location.replace(location.toString().replace(/(javascript:|#).*$/, '') + '#/' + fragment) : location.hash = '/'+fragment; 
		}
	};
})(jQuery);$.fn.sSlideTo=function(content,callback){
	var t=$(this),tW=t.width(),parent=t.parent().css({'position':'relative','overflow-x':'hidden'}),parentW=parent.width(),
		tHeight=t.height(),heightPlusParent=parent.height()-tHeight,tOldPositioning=t.css('position');
	
	t.css({position:'absolute',width:tW});
	var newContent=t.clone(true,true).html(content).css({left:parentW}),newContentHeight;
	t.css({height:tHeight});
	
	parent.append(newContent).css('height',((newContentHeight=newContent.height()) > tHeight ? newContentHeight : tHeight) + heightPlusParent);
	
	t.add(newContent).animate({left: "-="+parentW},function(){
		t.remove();
		parent.add(newContent).removeAttr('style');
		callback();
	});
	
	return newContent;
};(function($){
	var lastConfirmResult=true,readyCallbacks=$.Callbacks();
	document.confirm=function(param){return lastConfirmResult=window.confirm(param);};
	var divContainer,divPage,divVariable,divContent,linkFavicon,normalFaviconHref;
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
	S.setTitle=function(title){ document.title=title; divVariable.find('h1:first').text(title) }
	S.ajax={
		init:function(){
			$(document).on('click',
						//'a[href]:not([href="javascript:;"]):not([href="#"]):not([href^="mailto:"]):not([target]):not([href^="http://"])'
						'a[href]:not([href="#"]):not([target]):not([href*=":"])'
				,function(evt){
				if($(evt.target).is('a[onclick^="return"]') && !lastConfirmResult) return false;
				evt.preventDefault();
				evt.stopPropagation();
				var a=$(this),rel='content',menu,url=a.attr('href');
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
				var form=$(this);
				S.ajax.load(form.attr('action'),form.serialize(),form.attr('method')==='get'?0:'post',form.has('input[type="password"]'));
				return false;
			});
		},
		
		updateVariable:function(divPage){
			divVariable=divPage.find('div.variable:first');
			divContent=divVariable.is('.content') && divVariable.has('h1').length===0 ? divVariable : divVariable.find('.content:first');
		},
		load:function(url,data,type,forceNotAddDataToGetORdoNotDoTheEffect){
			if(url.substr(0,1)==='?') url=location.pathname+url;
			var ajaxurl=url,headers={},divLoading=$('<div class="globalAjaxLoading"/>').text(i18nc['Loading...']).prepend('<span/>');
			
			if(data && !forceNotAddDataToGetORdoNotDoTheEffect) url+=(url.indexOf('?')==-1?'?':'&')+data;
			
			headers.SpringbokAjaxPage=divPage.length>0?divPage.data('layoutname')||'0':'0';
			headers.SpringbokAjaxContent=divContent.length>0?divContent.data('layoutname'):'';
			if(S.breadcrumbs) headers.SpringbokBreadcrumbs='1';
			
			document.title=i18nc['Loading...'];
			//$('body').fadeTo(0.4);
			$('body').addClass('cursorWait').append(divLoading);
			if(normalFaviconHref) linkFavicon.attr('href',webdir+'img/ajax-roller.gif');
			
			$.ajax(ajaxurl,{
				type:type?type:'GET', data:data, headers:headers,
				async:false,
				complete:function(jqXHR){
					S.history.navigate(url);
					
				/*	$('body').removeClass('cursorWait');
					divLoading.remove();
				},
				success:function(data,textStatus,jqXHR){*/
					var h,div,to;
					
					if(h=jqXHR.getResponseHeader('SpringbokRedirect')){
						divLoading.remove();
						S.ajax.load(h,false,false,true);
						return;
					}
					
					if(h=jqXHR.getResponseHeader('SpringbokAjaxTitle')) S.setTitle($.parseJSON(h));
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
					
					if(normalFaviconHref) linkFavicon.attr('href',normalFaviconHref);
					
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
			
			if(true && to === 'content' && !forceNotAddDataToGetORdoNotDoTheEffect){
				divContent=div.sSlideTo(content,OnReadyCallbacks);
			}else{
				div.html(content);//.fadeTo(0,1);
				OnReadyCallbacks();
			}
		}
	};
})(jQuery);


/**
 * $ HTML5 Inline Form Validator (ht5ifv) Plugin
 * Copyright(c) 2011 Isidro Vila Verde (jvverde@gmail.com)
 * Dual licensed under the MIT and GPL licenses
 * Version: 0.9.7
 * Last Revision: 2011-09-12
 *
 * Requires jQuery 1.6.2
 *
 *
 *ChangeLog
 *version 0.9.4 (29-08-2011)
 *fixed problem with ISODatestring
 *version 0.9.5 (05-09-2011)
 *Support for multiple in type email (http://www.w3.org/TR/html5/states-of-the-type-attribute.html#e-mail-state)
 *New static methods
 *version 0.9.6 (10-09-2011)
 *Support required attribute over a group of checkboxes sharing the same name (see examples 9 to 14)
 *version 0.9.7 (12-09-2011)
 *New static method $.ht5ivf('defaults') which expose all defaults values.
 */
 
(function($){
	function checkDateFormat($val){
		//http://www.w3.org/TR/html5/common-microsyntaxes.html#valid-date-string
		return $val.match(/^(\d{4})-(\d{2})-(\d{2})$/) && (function(){
			var $m = parseInt(RegExp.$2,10);
			var $d = parseInt(RegExp.$3,10);
			var $maxDay = [
				function(){return false}, //0 is not a month
				function(){return $d <= 31},
				function(){
					var $y = parseInt(RegExp.$1);
					return ($d <= 28) || ($d == 29) && ($y % 4 == 0) && (($y % 100 != 0) || ($y % 400 == 0));
				},		
				function(){return $d <= 31},
				function(){return $d <= 30},
				function(){return $d <= 31},
				function(){return $d <= 30},
				function(){return $d <= 31},
				function(){return $d <= 31},
				function(){return $d <= 30},
				function(){return $d <= 31},
				function(){return $d <= 30},
				function(){return $d <= 31}
			];
			return $m < 13 && $d < 32 && $m > 0 && $d > 0 && $maxDay[$m]()
		})()
	};
	
	var $getDateStruct = (function(){
		var $datetimePattern = /^((\d{4})-(\d{2})-(\d{2}))(T(([01][0-9]|2[0-3]):([0-5][0-9])(:([0-5][0-9])(\.\d{1,3})?)?)(Z|(\+|-)([01][0-9]|2[0-3]):([0-5][0-9]))?)?$/;
		return function($d){
			var $m;
			var $s;
			if ($m = $d.match($datetimePattern)){
				$s = {
					date: $m[1],
					year: $m[2],
					month: $m[3],
					day: $m[4],
					type: 1 //date
				};
				if ($m[5]){
					$s.type = 2; //datetime-local
					$s.time = $m[6];
					$s.hour = $m[7];
					$s.min = $m[8];
					if($m[9]){
						$s.sec = $m[10];
						if ($m[11]){
							$s.milisec = Number('0'+$m[11]) * 1000;
						}
					}
					if($m[12]){
						$s.type = 3; //datetime
						$s.offset = 0;
						if ($m[14]){
							$s.offset = Number($m[14]) * 60 + Number($m[15]);
							if ($m[13] == '-') $s.offset = -$s.offset; 
						}
					}
				}   
			}
			return $s;
		}
	})();
	function getDateFromISO($d){ //all because IE < 9 doesn't support ISODateStrings
		var $ds = $getDateStruct($d);
		if (!$ds) return $ds;
		var $date;
		if ($ds.type === 1){
			$date = new Date($ds.year,$ds.month - 1, $ds.day);
		}else{
			if($ds.type === 3 || $ds.type === 2){ //datetime
				var $date = new Date($ds.year,$ds.month - 1, $ds.day, $ds.hour, $ds.min);
				if ($ds.sec !== undefined){ 
					$date.setSeconds($ds.sec);
					if($ds.milisec) $date.setMilliseconds($ds.milisec);
				}
			}
			if ($ds.type === 3){
				var $offset = $ds.offset + $date.getTimezoneOffset();
				var $t = (Number($date) - ($offset * 60 * 1000));
				$date.setTime($t);
			}
		}
		return $date;
	}
	function checkTimeFormat($val){
		//http://www.w3.org/TR/html5/common-microsyntaxes.html#valid-time-string
		return /^([01][0-9]|2[0-3]):([0-5][0-9])(:[0-5][0-9](\.\d{1,3})?)?$/.test($val)
	};
	var $inputTypes = { //Defines restrictions for each input type
		color: {
			restrictions:{
				//http://www.w3.org/TR/html5/common-microsyntaxes.html#rules-for-serializing-simple-color-values
				type: function($node,$ignoreEmpty){
					var $val = $node.val();
					return ($val == '' && $ignoreEmpty) || /^#[0-9A-F]{6}$/i.test($val)
				}
			}
		},
		date: {
			restrictions:{
				type: function($node,$ignoreEmpty){ 
					var $val = $node.val();
					return ($val == '' && $ignoreEmpty) || checkDateFormat($val);
				},
				min:function($node){
					var $min = getDateFromISO($node.attr('min'));
					var $val = getDateFromISO($node.val());
					return isNaN($val) || $min <= $val;
				},
				max:function($node){
					var $max = getDateFromISO($node.attr('max'));
					var $val = getDateFromISO($node.val());
					return isNaN($val) || $val <= $max;
				}
			}
		},
		'datetime': {
			restrictions:{
				//http://www.w3.org/TR/html5/common-microsyntaxes.html#valid-global-date-and-time-string
				type: function($node,$ignoreEmpty){
					var $val = $node.val();
					return ($val == '' && $ignoreEmpty) 
						|| $val.match(/^(\d{4}-\d{2}-\d{2})T(.+)(Z|(\+|-)([01][0-9]|2[0-3]):([0-5][0-9]))$/) 
						&& (function(){
							var $date = RegExp.$1;
							var $time = RegExp.$2;
							return checkDateFormat($date) && checkTimeFormat($time);
						})()
				},
				min:function($node){return $inputTypes.date.restrictions.min($node)},
				max:function($node){return $inputTypes.date.restrictions.max($node)}
			}
		},
		//http://www.w3.org/TR/html5/states-of-the-type-attribute.html#local-date-and-time-state
		'datetime-local': {
			restrictions:{
				type: function($node,$ignoreEmpty){
					var $val = $node.val();
					return ($val == '' && $ignoreEmpty) 
						|| $val.match(/^(\d{4}-\d{2}-\d{2})T(.+)$/)
						&& (function(){
							var $date = RegExp.$1;
							var $time = RegExp.$2;
							return checkDateFormat($date) && checkTimeFormat($time);
						})()
				},
				min:function($node){return $inputTypes.date.restrictions.min($node)},
				max:function($node){return $inputTypes.date.restrictions.max($node)}
			}
		},
		email: {
			restrictions:{
				//From http://bassistance.de/jquery-plugins/jquery-plugin-validation/
				type: function($node,$ignoreEmpty){
					var $val = $node.val();
					//the $node.get(0).getAttribute('multiple') is a workaround for IE9
					var $values = $node.attr('multiple') || $node.get(0).getAttribute('multiple') ? $val.split(/\s*,\s*/) : [$val];
					function validate(){
						var $val = $values.pop();
						return !$val || $val &&
							$val.match(/^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i) 
							&& validate()
					}
					return ($val == '' && $ignoreEmpty) || validate()
				}
			} 
		}, 
		month: (function(){
			//http://www.w3.org/TR/html5/the-input-element.html#attr-input-type
			var $RE = /^(\d{4})-(0[1-9]|1[0-2])$/;
			return {
				restrictions:{
					type: function($node,$ignoreEmpty){
						var $val = $node.val();
						return ($val == '' && $ignoreEmpty) || $RE.test($val);
					},
					min:function($node){
						$RE.test($node.val());
						var $yv = parseInt(RegExp.$1,10); 
						var $mv = parseInt(RegExp.$2,10);
						$RE.test($node.attr('min'));
						var $ym = parseInt(RegExp.$1,10); 
						var $mm = parseInt(RegExp.$2,10);
						return isNaN($yv) || isNaN($mv) || isNaN($ym) || isNaN($mm) || $ym < $yv || $ym == $yv && $mm <= $mv;
					},
					max:function($node){
						$RE.test($node.val());
						var $yv = parseInt(RegExp.$1,10); 
						var $mv = parseInt(RegExp.$2,10);
						$RE.test($node.attr('max'));
						var $ym = parseInt(RegExp.$1,10); 
						var $mm = parseInt(RegExp.$2,10);
						return isNaN($yv) || isNaN($mv) || isNaN($ym) || isNaN($mm) || $yv < $ym || $yv == $ym && $mv <= $mm;
					}
				}
			}
		})(),
		number: {
			restrictions:{
				//From http://www.w3.org/TR/html5/common-microsyntaxes.html#real-numbers
				type: function($node,$ignoreEmpty){ 
						var $val = $node.val();
						return ($val == '' && $ignoreEmpty) || /^-?[0-9]+(\.[0-9]*)?(E(-|\+)?[0-9]+)?$/i.test($val)
				},
				min: function($node){
					var $min = parseFloat($node.attr('min'),10);
					var $val = parseFloat($node.val(),10);
					return isNaN($val) || $min <= $val
				},	
				max: function($node){
					var $max = parseFloat($node.attr('max'),10);
					var $val = parseFloat($node.val(),10);
					return isNaN($val) || $val <= $max
				}
			}
		},
		password: {
			restrictions:{
				type: function($node,$ignoreEmpty){return $inputTypes.text.restrictions.type($node,$ignoreEmpty)} //the same as text fields
			}
		},	
		range: {	//the same as number fields
			restrictions:{
				type: function($node,$ignoreEmpty){ return $inputTypes.number.restrictions.type($node,$ignoreEmpty)}, 
				min: function(val){ return $inputTypes.number.restrictions.min(val)},	
				max: function(val){ return $inputTypes.number.restrictions.max(val)}
			}
		},
		search: {
			restrictions:{
				type: function($node,$ignoreEmpty){return $inputTypes.text.restrictions.type($node,$ignoreEmpty)}	//the same as text fields
			}
		},
		tel: {
			restrictions:{
				type: function($node,$ignoreEmpty){return $inputTypes.text.restrictions.type($node,$ignoreEmpty)}	//the same as text fields
			}
		},
		text: {
			restrictions:{
				//http://www.w3.org/TR/html5/states-of-the-type-attribute.html#text-state-and-search-state
				type: function($node,$ignoreEmpty){
						var $val = $node.val();
						return ($val == '' && $ignoreEmpty) || /^[^\n\r]*$/.test($val)}
			}
		},	
		time: {
			restrictions:{
				type: function($node,$ignoreEmpty){
					var $val = $node.val();
					return ($val == '' && $ignoreEmpty) || checkTimeFormat($val)
				},
				min:function($node){
					var $min = getDateFromISO('2000-01-01T'+$node.attr('min'));
					var $val = getDateFromISO('2000-01-01T'+$node.val());
					return isNaN($val) || $min <= $val
				},
				max:function($node){
					var $max = getDateFromISO('2000-01-01T'+$node.attr('max'));
					var $val = getDateFromISO('2000-01-01T'+$node.val());
					return isNaN($val) || $val <= $max
				}
			}
		},
		url: {
			restrictions:{
				//RE copied from http://bassistance.de/jquery-plugins/jquery-plugin-validation/
				type: function($node,$ignoreEmpty){ 
					var $val = $node.val();
					return ($val == '' && $ignoreEmpty) || /^(https?|ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(\#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test($val)
				}
			}
		},			
		week: (function(){
			function numberOfWeeks(Y) { //From http://www.merlyn.demon.co.uk/weekinfo.htm#WPY
				var X1 = new Date(Y, 0, 4), X2 = new Date(Y, 11, 28), NW;
				X1.setDate(X1.getDate() - (6 + X1.getDay()) % 7);
				X2.setDate(X2.getDate() + (7 - X2.getDay()) % 7);
				NW = Math.round((X2 - X1) / 604800000); //60*60*24*7*1000
				return NW;
			};
			//http://www.w3.org/TR/html5/common-microsyntaxes.html#valid-week-string
			var $RE = /^(\d{4})-W(0[1-9]|[1-4][0-9]|5[0-3])$/;
			return {
				restrictions:{
					type: function($node,$ignoreEmpty){
						var $val = $node.val();
						return ($val == '' && $ignoreEmpty) || $RE.test($val) && RegExp.$2 <= numberOfWeeks(RegExp.$1);
					},
					min:function($node){
						$RE.test($node.val());
						var $yv = parseInt(RegExp.$1,10); 
						var $wv = parseInt(RegExp.$2,10);
						$RE.test($node.attr('min'));
						var $ym = parseInt(RegExp.$1,10); 
						var $wm = parseInt(RegExp.$2,10);
						return isNaN($yv) || isNaN($wv) || isNaN($ym) || isNaN($wm) || $ym < $yv || $ym == $yv && $wm <= $wv;
					},
					max:function($node){
						$RE.test($node.val());
						var $yv = parseInt(RegExp.$1,10); 
						var $wv = parseInt(RegExp.$2,10);
						$RE.test($node.attr('max'));
						var $ym = parseInt(RegExp.$1,10); 
						var $wm = parseInt(RegExp.$2,10);
						return isNaN($yv) || isNaN($wv) || isNaN($ym) || isNaN($wm) || $yv < $ym || $yv == $ym && $wv <= $wm;
					}
				}
			};
		})(),
		_defaults:{
			restrictions:{
				type: function(){return true}			
			}
		}
	};
	var $monitClass = 'ht5ifv-monitoring'; //Each field under monitoring process has this class
	var $errorClasses = { //these classes are used internaly to signal if the field is in error and what type of error
		min: 'ht5ifv-min-error',
		max: 'ht5ifv-max-error',
		type: 'ht5ifv-type-error',
		pattern: 'ht5ifv-pattern-error',
		required: 'ht5ifv-required-error'
	};
	var $defaults = {
		classes: function($restriction){ return 'ht5ifv-show-' + $restriction},  	
		targets: function($this, $restriction){return $this},
		callbacks: function($this,$restriction){},
		filter: function($inputs){return $inputs.not('[type="hidden"]');},
		types:$inputTypes,
		checkbox:{
			restrictions:{
				required: function($node){
					return $node.is(':checked')
				}
			},
			events:{
				validate: 'change.ht5ifv',
				check: 'change.ht5ifv'
			}		
		},
		radio:{
			restrictions:{
				required:function ($radioGroup,$node){
					return $radioGroup.filter(':checked').length > 0
				}
			},
			events:{
				validate: 'change.ht5ifv',
				check: 'change.ht5ifv'
			}
		},
		textarea:{ //don't delete it
		},
		select:{
			restrictions:{
				required:function ($node){
					return $node.val() != null;
				}
			},
			events:{
				validate: 'change.ht5ifv',
				check: 'change.ht5ifv'
			}
		},	
		restrictions:{
			required:function ($node){
				return $node.val() != ''
			},
			pattern: function($node,$ignoreEmpty){ //as this is the same for all types it is defined here
				var $pattern = $node.attr('pattern');
				var $val = $node.val();
				var $re = new RegExp('^'+$pattern+'$');
				return (!$val && $ignoreEmpty) || $re.test($val);	
			}	
		},
		events:{  //by default this are the events which trigger validate and check actions.
			validate: 'focus.ht5ifv keyup.ht5ifv', //validate every time a key is released or the field get the focus
			check: 'blur.ht5ifv'
		}
	};
	var $extensionsArray = [];
	function getRestrictions($options){		//We need to know all restrictions present.
		if(!$options){
			console.warn('No options to getRestrictions');
			return [];
		}
		var $restrictions = {};
		//get global restrictions keys
		if ($options.restrictions && $options.restrictions instanceof Object) $.each($options.restrictions,function($r){
			$restrictions[$r] = true;
		})
		//add restrictions defined in these fields
		$.each(['select','textarea','checkbox','radio'],function($n,$e){
			if($options[$e] && $options[$e].restrictions && $options[$e].restrictions instanceof Object){ 
				$.each($options[$e].restrictions,function($r){
					$restrictions[$r] = true;
				});
			}
		});
		//add also the restrictions defined in each input type
		if ($options.types && $options.types instanceof Object) $.each($options.types,function($e,$x){
			if ($options.types[$e].restrictions && $options.types[$e].restrictions instanceof Object){ 
				$.each($options.types[$e].restrictions,function($r){
					$restrictions[$r] = true;
				});
			}
		});
		var $result = ['valid','invalid']; //should this be here, for extensions global-like options?Need to thinks about!
		$.each($restrictions,function($r){
			$result.push($r);
		});
		return $result;
	}
	function uniformart($f){ //uniformize the format
		if ($f.restrictions && $f.restrictions instanceof Object) $.each($f.restrictions,function($r,$obj){
			if (!($obj instanceof Function) && $obj instanceof Object && $obj.handler && $obj.handler instanceof Function){
				var $restrictions = {};
				$restrictions[$r] = $obj.handler;	
				var $new = {restrictions: $restrictions};
				if ($obj.classe !== undefined){
					$new.classes = {};
					$new.classes[$r] = $obj.classe;
				}
				if ($obj.target !== undefined){
					$new.targets = {};
					$new.targets[$r] = $obj.target;
				}
				if ($obj.callback !== undefined){
					$new.callbacks = {};
					$new.callbacks[$r] = $obj.callback;
				}
				$.extend(true,$f,$new);
			}
		});
		return $f;
	}
	function expand($o,$allRestrictions){
		if(!$o){
			console.warn('No options to expand');
			return $options;
		}
		var $options = $.extend(true,{},$o); //make a  working copy
		var $expand = function($n,$f){
			if (!$f || !($f instanceof Object)) return;
			//first expand classes if it is provided as a string or an array
			if($f.classes && $f.classes instanceof Function){
				var $g = $f.classes;
				$f.classes = {};
				$.each($allRestrictions,function($n,$r){
					$f.classes[$r] = $g($r);
				});
			}else if($f.classes && !($f.classes instanceof Object)){
				delete $f.classes;
				console.warn('the $options.classes must be an Object or an Function');
			}
			//Decompose targets if it is provided as Function
			if ($f.targets && $f.targets instanceof Function){
				var $target = $f.targets; 
				$f.targets = {};
				$.each($allRestrictions,function($n,$r){
					$f.targets[$r] = function($node){return $target($node,$r)};
				});
			}else if($f.targets && !($f.targets instanceof Object)){
				delete $f.targets;
				console.warn('the $options.targets must be an Object or an Function');
			}
			//Decompose callbacks if it is provided as Function
			if ($f.callbacks && $f.callbacks instanceof Function){
				var $callback = $f.callbacks; 
				$f.callbacks = {};
				$.each($allRestrictions,function($n,$r){
					$f.callbacks[$r] = function($node){$callback($node,$r)};
				});
			}else if($f.callbacks && !($f.callbacks instanceof Object)){
				delete $f.callbacks;
				console.warn('the $options.callbacks must be an Object or an Function');
			}
			uniformart($f); //restrictions can have two diferents formats
		};
		$expand('',$options);
		$expand('.select',$options.select);
		$expand('.textarea',$options.textarea);
		$expand('.radio',$options.radio);
		$expand('.checkbox',$options.checkbox);
		if ($options.types && $options.types instanceof Object) $.each($options.types, function($n,$t){
			$expand('.types.'+$n,$t);
		});
		return $options;
	};
	//demultiplexing the defaults and apply its members to each html form field if not specifically defined
	function apply_defaults($options){
		if(!$options){
			console.warn('No options to apply_defaults');
			return $options;
		}
		var $defaults = {};
		if ($options.classes) $.extend(true,$defaults,{classes: $options.classes});
		if ($options.targets) $.extend(true,$defaults,{targets: $options.targets});
		if ($options.events) $.extend(true,$defaults,{events: $options.events});
		if ($options.restrictions) $.extend(true,$defaults,{restrictions: $options.restrictions});
		if ($options.callbacks) $.extend(true,$defaults,{callbacks: $options.callbacks});		
		function $apply($obj){
			return $.extend(true,{},$defaults,$obj)
		}
		if ($options.select !== undefined) $options.select = $apply($options.select);
		if ($options.textarea !== undefined) $options.textarea = $apply($options.textarea);
		if ($options.radio !== undefined) $options.radio = $apply($options.radio);
		if ($options.checkbox !== undefined) $options.checkbox = $apply($options.checkbox);			
		if ($options.types && $options.types instanceof Object) $.each($options.types, function($t){
			$options.types[$t] = $apply($options.types[$t]);					
		});
		return $options;
	};
	function remove_globals($options){
		if(!$options){
			console.warn('No options to remove_globals');
			return $options;
		}
		delete $options.events;
		if(!$options.restrictions || !($options.restrictions instanceof Object)){ //delete all globals if global restrictions doesn't exist
			delete $options.classes;
			delete $options.targets;
			delete $options.restrictions;
			delete $options.callbacks;
		}else{
			$.each(['classes','targets','callbacks'],function($i,$o){
				if ($options['$o']) $.each($options[$o],function($r){ 
					if (!$options.restrictions[$r]){	//Only delete global if a global restrictions don't exist
						delete $options[$o][$r];
					}
				});
			});
		};
		return $options;
	};
	function check($options){
		if(!$options){
			console.warn('No options to check');
			return $options;
		}
		function $check($field,$fn){
			if($field.events && $field.events instanceof Object){
			}else{
				$field.events = {
					validate: 'change.ht5ifv',
					check: 'change.ht5ifv'
				};
				console.warn($fn + ".events set to {validate: 'change.ht5ifv',check: 'change.ht5ifv'}");
			}
			if($field.restrictions && $field.restrictions instanceof Object){
				$.each(['classes','targets','callbacks'], function($x,$n){
					if (!($field[$n] && $field[$n] instanceof Object)){
						$field[$n] = {};
						console.warn($fn + '.' + $n + ' set to an empty Associative Array');
					};
				})
				$.each($field.restrictions, function($r){
					$.each(['targets','callbacks'],function($x,$n){
						if ($field[$n][$r] === undefined){
							$field[$n][$r] = function($this){return $this};
							console.warn($fn + '.' + $n + '.' + $r + ' set to an function function($this){return $this}');
						}
					});
					if ($field.classes[$r] === undefined || typeof $field.classes[$r] != 'string'){
						$field.classes[$r] = '';
						console.warn($fn + '.classes.' + $r + ' set to an empty string');
					}
				})
			}else{
				console.warn('.'+$i+' have no restrictions');
			};
		};
		$.each(['select','textarea','radio','checkbox'], function($n,$i){
			if ($options[$i]){
				if ($options[$i] instanceof Object){
					$check($options[$i],'$options.'+$i);
				}else{
					console.warn('$options.' + $i + ' is missing or is not an Associative Array');
				}
			}			
		});
		if ($options.types && $options.types instanceof Object) $.each($options.types,function($i,$t){
			if ($t instanceof Object){
				$check($t,'$options.type.'+$i);
			}else{
				throw 'ht5ifv - $options.type.'+$i+' must be an Associative Array';
			}
		});
		return $options;
	};
	
	
	function clean($options){
		if(!$options){
			console.warn('No options to clean');
			return $options;
		}
		function $clean($field,$fn){
			$.each(['classes','targets','callbacks'], function($x,$n){
				if ($field[$n] && $field[$n] instanceof Object) $.each($field[$n], function($r){
					if ($r != 'valid' && $r != 'invalid' 
						&& ($field.restrictions === undefined || $field.restrictions[$r] === undefined)){ 
							delete $field[$n][$r];
						}
				})
			})
		};
		$.each(['select','textarea','radio','checkbox'], function($n,$i){
			if ($options[$i] && $options[$i] instanceof Object){
				$clean($options[$i]);
			}
		});
		if ($options.types && $options.types instanceof Object) $.each($options.types,function($i,$t){
			if ($t instanceof Object) $clean($t);
		});
		return $options;
	};
	
	var $methods = {
		init: function($o){
			//first, let perform a bunch of tests
			if ($o){
				$.each($defaults,function($k){
					if ($k == 'classes') return //classes are a special case
					if ($o[$k] !== undefined){
						if ($o[$k] === null || !($o[$k] instanceof Object)){
							throw 'ht5ivf - the option ' + $k + 'must be a Function or an Associative Array';
						}
					}
				});
				if ($o.classes !== undefined){
					if ($o.classes === null || !(typeof $o.classes == 'string' || $o.classes instanceof Object)){
						throw 'ht5ivf - the option.classes must be a String or an Array or an Associative Array';
					}
				}
				if ($o.types) $.each($o.types,function($k){
					if ($o[$k] === null || !($o.types[$k] instanceof Object)){
						throw 'ht5ivf - the option ' + $k + 'must be a Function or an Associative Array';
					}
				});
			}else $o = {};
			//end of bunch of tests
			
			
			//get all restrictions present all definition's variables (defaults, extensions and receiced options)
			var $allRestrictions = (function(){ 
				var $allOptions = $.extend(true,{},$defaults); 	//start by default
				$.each($extensionsArray,function($i,$ext){		//then iterate over and add all extensions
					$.extend(true,$allOptions,$ext.definitions)
				});
				$.extend(true,$allOptions,$o)					//add received options
				return getRestrictions($allOptions);			//return all restrictions of all definitions
			})()

			//before merge defaults with extensions and received options we need to convert it to a desmultiplexed format
			
			var $_defaults = expand($defaults,$allRestrictions); //expand the defaults over all restrictions
			$.each($extensionsArray,function($i,$ext){
				var $_ext = (function(){
					if ($ext.global){
						return expand($ext.definitions,$allRestrictions);
					}else{
						var $exp = expand($ext.definitions,getRestrictions($ext.definitions));
						apply_defaults($exp);
						remove_globals($exp);
						clean($exp)
						if ($ext.selfContained) check($exp);
						return $exp;
					}
				})();
				$.extend(true,$_defaults,$_ext);
			});

			var $_o = expand($o, $o.localDefinitions ? getRestrictions($o) : $allRestrictions);	//expand all received options 
			
			//now define our $options
			var $options = $.extend(true,{
				browserValidate:false,
				callbackOnlyStatusTransitions: true,
				callbackOnlyErrorTransitions: true,
				ignoreEmptyFields: true,	
				safeValidate: true,
				checkDisable:true,
				validateOnSubmit:true   
			},$_defaults,$_o);

			apply_defaults($options); 	//apply the default values where appropriate
			clean($options);			//remove extras 	
			check($options);			//just in case the user forget some definitions	
			//remove_globals($options); //should i? maybe!
			//Now the real coding
			return this.each(function(){
				var $form = $(this);
				if ($form.data('ht5ifv') && $form.data('ht5ifv').method){
					console.warn('The method has already been called for the form:',$form);
					return;
				};

				var $controls = $options.filter((function(){
						return $form.is('input,textarea,select') ? $form: $('input,textarea,select',$form)
					})().not('[type="reset"],[type="submit"],[type="image"],[type="button"]')
				);
				var $inputs = $controls.filter('input').not('[type="checkbox"],[type="radio"]');
				var $checkboxes = $controls.filter('input[type="checkbox"]');
				var $radios = $controls.filter('input[type="radio"]');
				var $textareas = $controls.filter('textarea');
				var $selects = $controls.filter('select');
				
				/*Now, for each input type, use _defaults if missing in $options.types*/
				$inputs.each(function(){ 
					var $self = $(this);
					var $type = $self.attr('type');
					if ($type && $options.types[$type] === undefined){ //use _defaults if missing
						$options.types[$type] = $options.types._defaults;
					}
				});

				/*****************Begin of the binding events ********************/

		
				/* clear event */
				function bind_clearEvent($node,$definitions){
					var $restrictions = $definitions.restrictions;
					$node.bind('clear.ht5ifv',function(){ //chear the visual signals errors = status classes + display classes
						var $self = $(this);
						$.each($statusClasses,function($s, $statusClass){ 	//status (include error) classes are removed from element
							$self.removeClass($statusClass);
						});
						$.each($restrictions,function($r){ 	//Removed from targets classes associated with restrictions errors
							var $target = $definitions.targets[$r]($self);
							var $class = $definitions.classes[$r];
							$target.removeClass($class);
						});
						$.each(['valid','invalid'],function($i,$r){ 	//Removed from targets classes associated with status errors
							try{
								var $target = $definitions.targets[$r]($self);
								var $class = $definitions.classes[$r];
								$target.removeClass($class);
							}catch($e){
								console.error($e);
								console.log('The object Error is: %o',$e);
								throw 'ht5ifv: "clear" error ('+$e+')';
							}
						});
						return false; //stop propagation
					});
				}
				/* compute the valid or invalide state */
				function bind_checkEvent($node,$definitions){
					var $targetv = $definitions.targets['valid']($node);
					var $callbackv = $definitions.callbacks['valid'];
					var $classv = $definitions.classes['valid'];
					var $targeti = $definitions.targets['invalid']($node);
					var $callbacki = $definitions.callbacks['invalid'];
					var $classi = $definitions.classes['invalid'];
					$node.bind('check.ht5ifv',function(){
						var $self = $(this);
						if ($options.checkDisable && $self.parents('form,fieldset').andSelf().is('[disabled]')) return;
						if($options.safeValidate) $self.trigger('validate.ht5ifv');
						var $notErrors = true;
						$.each($errorClasses,function($type, $errorClass){
							if ($self.hasClass($errorClass)){	//the test fail if (at least) one error (class) is found 
								$notErrors = false;
							} 
						});
						if ($notErrors){
							$targeti.removeClass($classi);
							$self.removeClass($statusClasses.invalid);
							var $showIt = $self.val() !='' || !$options.ignoreEmptyFields; 
							if($showIt && !($self.hasClass($statusClasses.valid) && $options.callbackOnlyStatusTransitions)){ 
								try{
									$callbackv($self);
								}catch($e){
									throw 'ht5ifv: error in "valid" callback ('+$e+')';
								}	
							}
							if($showIt){
								$targetv.addClass($classv);
								$self.addClass($statusClasses.valid);	
							}else{	
								$targetv.removeClass($classv);
								$self.removeClass($statusClasses.valid);	//this is a tri-state (not valid and not invalid)
							}
						}else{
							$targetv.removeClass($classv);
							$targeti.addClass($classi);
							$self.removeClass($statusClasses.valid);
							if(!($self.hasClass($statusClasses.invalid) && $options.callbackOnlyStatusTransitions)){ 
								$self.addClass($statusClasses.invalid);	
								try{
									$callbacki($self);
								}catch($e){
									throw 'ht5ifv: error in "invalid" callback ('+$e+')';
								}
							}	
						}
					})
				}
				
				/*install events (validate, check and clear) for each restriction */
				function install_restriction($node,$definitions){
					var $restrictions = $definitions.restrictions;
					if(!$restrictions) return;
					$.each($restrictions,function($restriction,$evaluate){		//for each restriction
						if($node.is('[' + $restriction + ']')){		//if the restriction is present on this field
							var $errorClass = $errorClasses[$restriction] = 'ht5ifv-' + $restriction +'-error'; //add restriction to global errorClasses
							//var $target = getAndCheckTarget($restriction,$node);
							//var $callback = $options.callbacks[$restriction];
							var $target = $definitions.targets[$restriction]($node);
							var $callback = $definitions.callbacks[$restriction];
							var $class = $definitions.classes[$restriction];
							$node.bind('validate.ht5ifv',function(){		//bind a validate event	
								if ($evaluate($node,$options.ignoreEmptyFields)){	
									$target.removeClass($class);
									$node.removeClass($errorClass);	
								}else if (!($options.checkDisable && $node.parents('form,fieldset').andSelf().is('[disabled]'))){
									$target.addClass($class);
									if(!($node.hasClass($errorClass) && $options.callbackOnlyErrorTransitions)){
										$node.addClass($errorClass);	
										try{
											$callback($node);	
										}catch($e){
											throw 'ht5ifv: error in callback ('+$e+')';
										}	
									}
								}
							}).addClass($monitClass);
							bind_checkEvent($node,$definitions); //install check event
							bind_clearEvent($node,$definitions);
							$node.bind($definitions.events.validate,function(){
								$(this).trigger('validate.ht5ifv');
							}).bind($definitions.events.check,function(){
								$(this).trigger('check.ht5ifv');
							});
						} //if end	
					});
				}
				/* install_restrictions for inputs*/
				$inputs.each(function(){
					var $self = $(this);
					var $type = $self.attr('type') || '_defaults';
					var $field = $options.types[$type];
					install_restriction($self,$field);
				});
				/* install_restrictions for textareas*/
				$textareas.each(function(){ 
					install_restriction($(this),$options.textarea);
				});
				/* install_restrictions for selects*/
				$selects.each(function(){ 
					install_restriction($(this),$options.select);
				});				
				/* install_restrictions for checkboxes*/
				/*changed in version 0.9.6 */
			/*	$checkboxes.each(function(){ 
					install_restriction($(this),$options.checkbox);
				});*/
				
				/* Radio/checkboxes buttons are a very special case and needs a very special treatment*/
				
				var $htmlForm = (function(){
					return $form.is('form') ? $form : (function(){
						var $f = $form.parents('form').first();
						return $f.is('form') ? $f : $('body');
					})()
				})()
				$radios.add($checkboxes).each(function(){//required constrain for radio/checkbox. Needs a very special treatment
					var $self = $(this);
					var $definitions = $options[$self.attr('type')];
					var $restrictions = $definitions.restrictions;
					$.each($restrictions,function($restriction,$evaluate){
						if($self.is('[' + $restriction + ']')){
							var $errorClass = $errorClasses[$restriction] = 'ht5ifv-' + $restriction +'-error'; //add restriction to global errorClasses
							var $target = $definitions.targets[$restriction]($self);
							var $callback = $definitions.callbacks[$restriction];
							var $class = $definitions.classes[$restriction];
							//get the radio group with same name in the same form
							var $radioGroup = $self.attr('name') ?
								$('input[type="' + $self.attr('type') + '"][name="' + $self.attr('name') + '"]',$htmlForm)
								: $self; //if the field haven't a name there is no group
							if ($radioGroup.filter('[' + $restriction + ']').first().is($self)){ //avoid to bind the event multiple times
								//bind to all radios from same group regardless the restriction is present or not
								//If we are here, it means at least one radio, in this group, has this restriction set. 
								//As so, all of them must perform validation
								$radioGroup.bind('validate.ht5ifv',function(){	
									var $self = $(this);
									if ($options.checkDisable && $self.parents('form,fieldset').andSelf().is('[disabled]')) return;
									if (!$self.hasClass('ht5ifv-radioControlGroup')){ 
										//if it the first from the group also trigger the others
										$radioGroup.addClass('ht5ifv-radioControlGroup'); //flag it
										$radioGroup.not($self.get(0)).trigger('validate.ht5ifv'); //trigger the others
									};
									$self.removeClass('ht5ifv-radioControlGroup'); //remove the flag;
									if($evaluate($radioGroup,$self,$options.ignoreEmptyFields)){
										$target.removeClass($class);
										$self.removeClass($errorClass);	
									}else{				
										$target.addClass($class);
										if(!($self.hasClass($errorClass) && $options.callbackOnlyErrorTransitions)){
											$self.addClass($errorClass);	
											try{
												$callback($self);	
											}catch($e){
												throw 'ht5ifv: error in callback ('+$e+')';
											}	
										}
									}
								}).addClass($monitClass);
								bind_checkEvent($radioGroup,$definitions); //install check event
								bind_clearEvent($radioGroup,$definitions);
								$radioGroup.bind($definitions.events.validate,function(){
									$(this).trigger('validate.ht5ifv');
								}).bind($definitions.events.check,function(){
									$(this).trigger('check.ht5ifv');
								});
							};
						};
					});		
				});
				
				
				var $statusClasses = $.extend({},$errorClasses,{ //Very important to be here, don't move it
					valid: 'ht5ifv-valid-status',
					invalid: 'ht5ifv-invalid-status'
				});
				
				/************************************Last things************************************************/
				var $monitoredControls = $controls.filter('.'+$monitClass);
				

				if($options.browserValidate != true){
					$htmlForm.attr('novalidate','novalidate'); //turn off browser internal validation
				}
				$("input[type='reset']",$htmlForm).click(function(){	//also clear the signaling classes when the reset button is pressed
					$monitoredControls.trigger('clear.ht5ifv');
				});
				if($options.validateOnSubmit){ //before submit check it. This is the only event attached to the form
					$htmlForm.bind('submit.ht5ifv',function(){			
						return $check();	
					});					
				};
				var $check = function(){ //to be used internally
					$monitoredControls.trigger('validate.ht5ifv').trigger('check.ht5ifv'); 	//first validate each control
					var $valid = true;				//then compute valid status
					$monitoredControls.each(function(){
						$valid = $valid && !$(this).hasClass($statusClasses.invalid);
					});
					return $valid;
				}
				function setDefaults(){ //apply the default values to each control type
					$selects.each(function(){
						var $self = $(this);
						var $list = new Array;
						$.each($('option[selected]',$self),function(){
							var $t = $(this);
							var $v = $t.attr('value') || $t.text();
							$list.push($v)
						});
						$self.val($list);
					});
					$checkboxes.each(function(){
						var $self = $(this);
						var $list = new Array;
						$checkboxes.filter('[name="'+$self.attr('name')+'"]').each(function(){
							if(this.defaultChecked){
								var $o = $(this);
								var $v = $o.attr('value');
								$list.push($v)
							};
						});
						if ( $list.length > 0 ) $self.val($list);
					});
					$radios.each(function(){
						var $self = $(this);
						$self.val(['__ht5ifv__some_unprobably_text_algum_texto_'])
						if(this.defaultChecked){
							var $v = $self.attr('value');
							$self.val([$v]);
						};
					});
					$inputs.add($textareas).each(function(){//set de default value
						var $this = $(this);	
						var $default = this.defaultValue || '';			
						$this.val($default);
					});
				};
				$form.data('ht5ifv',{ 					//store these values for future accesses
					form: $form,
					method:{
						_validate: function(){$monitoredControls.trigger('validate.ht5ifv').trigger('check.ht5ifv');},
						_check:$check,
						_reset:function(){
							$monitoredControls.trigger('clear.ht5ifv');
							try{
								$htmlForm.get(0).reset();
							}catch(e){ //The user could hide the reset by defining something like this <input id="reset" ... />
								console.warn('The DOM reset method does not exist. It was problably overriden. '+
									'Chech in your form for a tag with the value "reset" assigned to an attribute id or name. '+
									'It just a hint. I will try again in a different way');
								var $resetButton = $('[type="reset"]',$htmlForm);
								if($resetButton.length){
									$resetButton.first().click();
								}else{ 			//third and the last attemp 
									console.warn('Reset form: the last try. Fallback to reset_fields only');
									setDefaults();
									//$.each($monitoredControls,function(i,e){//set de default value
								} 
							}
						},
						_defaults:function(){
							$monitoredControls.trigger('clear.ht5ifv');
							setDefaults();
						},
						_clean:function(){
							$monitoredControls.trigger('clear.ht5ifv');
							$inputs.val('');//set controls value to null string
							$textareas.val('');//set controls value to null string
							$checkboxes.val(['__ht5ifv__some_unprobably_text_algum_texto_']);
							$radios.val(['__ht5ifv__some_unprobably_text_algum_texto_']);
							$selects.val(['__ht5ifv__some_unprobably_text_algum_texto_']);
						},
						_destroy:function(){
							$monitoredControls.trigger('clear.ht5ifv');
							$monitoredControls.unbind('.ht5ifv');
							$form.unbind('.ht5ifv');
							$form.removeData('ht5ifv');
						}
					}
				});
			});//end of each
		},//end of init method
		validate: function(){ //just validate and apply the classes
			return this.each(function(){
				try{
					$(this).data('ht5ifv').method._validate();
				}catch($e){
					console.error($e);
					console.log('The object Error is: %o',$e);
					throw 'ht5ifv: "validate" error ('+$e+')';
				}
			});
		},
		valid: function(){ //validate and check 
			var $test = true;
			this.each(function(){
				try{
					$test = $test && $(this).data('ht5ifv').method._check();
				}catch($e){
					throw 'ht5ifv: "valid" error ('+$e+')';
				}
			});
			return $test;
		},
		reset: function(){ //reset the form (values and views)
			return this.each(function(){
				try{
					var $d = $(this).data('ht5ifv');
					$(this).data('ht5ifv').method._reset();
				}catch($e){
					throw 'ht5ifv: "reset" error ('+$e+')';
				}
			});
		},
		reload: function(){ 
			return this.each(function(){
				try{
					var $d = $(this).data('ht5ifv');
					$(this).data('ht5ifv').method._defaults();
				}catch($e){
					throw 'ht5ifv: "default" error ('+$e+')';
				}
			});
		},
		clean: function(){ //reset the form (values and views)
			return this.each(function(){
				try{
					$(this).data('ht5ifv').method._clean();
				}catch($e){
					throw 'ht5ifv: "clean" error ('+$e+')';
				}
			});
		},
		destroy: function(){
			return this.each(function(){
				try{
					$(this).data('ht5ifv').method._destroy();
				}catch($e){
					throw 'ht5ifv: "destroy" error ('+$e+')';
				}
			})
		}
	}
	$.fn.ht5ifv = function($method){
		if ($methods[$method]){
	  		return $methods[$method].apply( this, Array.prototype.slice.call(arguments, 1));
		} else if ($method instanceof Object || ! $method){
	  		return $methods.init.apply(this, arguments);
		} else {
	  		$.error('Method ' +  $method + ' does not exist on jQuery.ht5ifv plugin');
		}	
	};
	var $registry = {};
	var $staticMethods = {
		extend: function($definitions,$global, $selfContained){
			$extensionsArray.push({definitions:$definitions,global:$global, selfContained: $selfContained});
		},
		register: function($name,$function,$force){
			if ($name && ($registry[$name] === undefined || $force)){
				$registry[$name] = $function;
			}else{
				console.warn("Cannot registry the handler with name: %s",$name);
			}
		},
		unregister:function($name){
			delete $registry[$name];
		},
		clean:function(){
			$registry = {};
		},
		registerType: function($name,$handler,$force){
			$staticMethods.register($name, function(){
				return {	   
					types: (function($o){
						$o[$name] = {restrictions: {type: $handler}}
						return $o;
					})({})
				};
			},$force);
		},
		registerRestriction: function($name,$handler,$force){
			$staticMethods.register($name, function(){
				return {	   
					restrictions: (function($o){
						$o[$name] = $handler;
						return $o;
					})({})
				};
			},$force);
		},
		use: function($list){   //install the restrictions
			if($list){
				for (var $i = 0; $i < arguments.length; $i++){
					var $arg = arguments[$i];
					if (typeof $arg == 'string'){
						if ($registry[$arg]) $.ht5ifv('extend',$registry[$arg].apply(this))
						else console.warn("Cannot find the handler with name: %s",$arg);	
					}else if($arg instanceof Array){	
						/*if the parameter is an array it must have the name in the first position and then the args for the handler*/
						var $name = $arg.shift(); 
						if ($name && $registry[$name]) $.ht5ifv('extend',$registry[$name].apply(this,$arg))
						else console.warn("Cannot find the handler with name: %s",$arg);  
					}
				}
			}else{  //by ommiting the list arguments, everything will be installed
				$.each($registry,function($name,$handler){
					$.ht5ifv('extend',$handler.apply(this)) //no parameters will be passed
				});
			}
		},
		defaults: function(){return $defaults}
	}
	$.ht5ifv = function($staticMethod){
		if ($staticMethods[$staticMethod]){
	  		return $staticMethods[$staticMethod].apply( this, Array.prototype.slice.call(arguments, 1));
		} else {
	  		$.error('Static Method ' +  $staticMethod + ' does not exist on jQuery.ht5ifv plugin');
		}
	}
})(jQuery);
/*
var _={};

_.contacts={
	isInit:false,
	
	groups:[],
	contacts:[],
	
	init:function(){
		if(this.isInit!==false) return;
		this.isInit=true;
		
	},
	
	loadPage:function(){
		this.init();
		var $nav=$('#page div.fixed:first nav.list-groups:first ul');
		if(this.groups.length===0) $nav.html($('<li/>').addClass('italic').text(_t('No group yet.')));
		else{
			this.groups.each(function(i,g){
				$nav.append($('<li/>').html(S.html.link(g.name,basedir+'contacts/group/'+g.id,{'class':'categ',style:g.style})));
			});
		}
	}
};
*/})(window,document,Object,Array,Math);