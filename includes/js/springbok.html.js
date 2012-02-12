$$.html={
	link:function(title,url,options){
		options=$.extend({},{escape:true},options);
		var a=$('<a/>');
		options && options.escape ? a.text(title) : a.html(title);
		delete options.escape;
		
		if(options) a.attr(options);
		
		if(url === false) url='javascript:;';
		else if(url[0]==='/') url=basedir+url.substr(1);
		else if(url.substr(0,2)=='\/') url=url.substr(1);
		
		return a.attr('href',url);
	},
	
	powered:function(){
		return i18nc['Powered by']+' <a href="http://www.springbok-framework.com" target="_blank">Springbok Framework</a>.';
	},
	
	iconLink:function(icon,text,url,options){
		options=$.extend({},{'class':'aicon'},options);
		options.escape=false;
		return this.link($('<span/>').attr('class','icon '+icon).afterText(' '+text),url,options);
	},
	
	iconAction:function(icon,url,options){
		options=options||{};
		options['class']='action icon '+icon;
		return this.link('',url,options);
	}
};

(function($){
	$.fn.appendText=function(text){
		if(text === undefined) return this;
		return this.append((this[0] && this[0].ownerDocument || document).createTextNode( text ));
	};
	$.fn.afterText=function(text){
		if(text === undefined) return this;
		return this.after((this[0] && this[0].ownerDocument || document).createTextNode( text ));
	};
	$.fn.beforeText=function(text){
		if(text === undefined) return this;
		return this.before((this[0] && this[0].ownerDocument || document).createTextNode( text ));
	};
})(jQuery);