S.html={
	baseurl:basedir.substr(0,basedir.length-1),
	link:function(title,url,options){
		options=UObj.extend({escape:true},options);
		
		if(url===false) url=this.url(url,options.fullUrl);
		else if(!url) title=url=this.url(title,options.fullUrl);
		else if(url!=='#' && url[0]!=='?' && (S.isArray(url) || (url.substr(0,11)!=='javascript:' && url.substr(0,7)!=='mailto:')))
				url=this.url(url,options.fullUrl);
		delete options.fullUrl;
		
		var a=$('<a/>'),current=false;
		options.escape ? a.text(title) : a.html(title);
		delete options.escape;
		
		if(options.current !== undefined){
			if(options.current===1) current=true;
			else if(options.current && url!==false && url !==this.baseurl) current=url.startsWith(window.location.pathname);
			else current=url===window.location.pathname;
			
			delete options.current;
		}
		
		if(options) a.attr(options);
		if(current) a.addClass('current');
		
		return a.attr('href',url);
	},
	linkHtml:function(title,url,options){ options=options||{}; options.escape=false; return this.link(title,url,options); },
	
	tag:function(tag,attrs,content,escape){
		tag=$('<'+tag+'/>');
		if(attrs) tag.attr(attrs);
		if(content) escape ? tag.text(content) : tag.html(content);
		return tag;
	},
	
	powered:function(){
		return i18nc['Powered by']+' <a href="http://www.springbok-framework.com" target="_blank">Springbok Framework</a>.';
	},
	
	iconLink:function(icon,text,url,options){
		options=UObj.extend({'class':'aicon'},options);
		options.escape=false;
		return this.link($('<span/>').attr('class','icon '+icon).afterText(' '+text),url,options);
	},
	
	iconAction:function(icon,url,options){
		options=options||{};
		options['class']='action icon '+icon;
		return this.link('',url,options);
	},
	
	/* Exemples :
	* S.html.url(['/:id-:slug',post.id,post.slug])
	* S.html.url('/site/login')
	* S.html.url(['/:id-:slug',post.id,post.slug,{'target':'_blank','?':'page=2'}])
	*/
	url:function(url,full){
		if(S.isStr(url) || !url){
			if(url) url=url.trim();
			if(!url || url==='/') return (full || '') + this.baseurl + '/';
			else{
				if(url.contains('://')) return url;
				if(url.startsWith('\\/')) return url.substr(1);
				if(url.substr(0,1)==='/') return (full || '') + this.baseurl + (S.router ? S.router.getStringLink(url.substr(1)) : url);
			}
		}else{
			return (full || '') + this.baseurl + (S.router ? S.router.getArrayLink(url) : url);
		}
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