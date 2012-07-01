S.HElt=function(tag){ this.elt=$('<'+tag+'>'); };
S.HElt.prototype={
	attrs:function(attrs){this.elt.attr(attrs); return this; },
	attr:function(attrName,value){this.elt.attr(attrName,value); return this; },
	id:function(value){this.elt.attr('id',value); return this; },
	attrClass:function(value){ this.attr('class',value); return this; },
	addClass:function(value){this.elt.addClass(value); return this; },
	rmClass:function(value){this.elt.removeClass(value); return this; },
	style:function(value){this.attr('style',value); return this; },
	click:function(value){this.elt.click(value); return this; },
	rmAttr:function(name){this.attr(name,undefined); return this; },
	
	append:function(){ this.elt.append.apply(this.elt,arguments); return this; }
};