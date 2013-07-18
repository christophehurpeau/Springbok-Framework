S.HElt=function(tag){ this.elt=$('<'+tag+'>')/*.data('sElt',this)*/; };
S.HElt.prototype={
	attrs:function(attrs){this.elt.attr(attrs); return this; },
	attr:function(attrName,value){this.elt.attr(attrName,value); return this; },
	id:function(value){this.elt.attr('id',value); return this; },
	setClass:function(value){ this.attr('class',value); return this; },
	addClass:function(value){this.elt.addClass(value); return this; },
	rmClass:function(value){this.elt.removeClass(value); return this; },
	style:function(value){this.attr('style',value); return this; },
	click:function(value){this.elt.click(value); return this; },
	removeAttr:function(name){this.elt.attr(name,undefined); return this; },
	
	append:function(){ this.elt.append.apply(this.elt,arguments); return this; }
};