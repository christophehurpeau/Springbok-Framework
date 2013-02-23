S.menu={
	tagName:'nav',separator:'-',
	top:function(options,links){
		return this.create(options,links,'top');
	},
	ajaxTop:function(options,links){
		return this.create(options,links,'top ajax');
	},
	left:function(options,links){
		return this.create(options,links,'left');
	},
	ul:function(options,links){
		this.tagname='ul';
		var res=this.create(options,links,'');
		this.tagname='nav';
		return res;
	},
	
	/* Exemples :
	* S.menu.left([
	* 	['Home',false],
	* 	['Login','/user/login'],
	* 	['Logout','/user/logout',{'class':'logout'}]
	* ])
	*/
	create:function(options,links,type){
		if(S.isArray(options)){
			links=options;
			options={};
		}
		options=UObj.extend({menuAttributes:{'class':type},lioptions:{},linkoptions:{},startsWith:false},options);
		var t=this,res=S.html.tag(this.tagName,options.menuAttributes),ul=this.tagName==='ul'?res:$('<ul/>').appendTo(res);
		links.forEach(function(item,k){
			if(!item[0]){ ul.append(S.html.tag('li',{'class':'separator'},this.separator,1)); return; }
			var linkOptions=UObj.extend(options.linkoptions,item[2]);
			
			if(linkOptions.visible===false) return;
			delete linkOptions.visible;
			ul.append(t.link(item[0],item[1],linkOptions,{startsWith:options.startsWith},options.lioptions));
		});
		return res;
		/*return $('<div/>').html(res).html(); //TODO à revoir*/
	},
	
	link:function(title,url,linkoptions,options,lioptions){
		linkoptions=linkoptions||{};
		if(linkoptions.current!==undefined){
			if(linkoptions.current) linkoptions.current=1;
		}else{
			linkoptions.current=linkoptions.startsWith!==undefined ? linkoptions.startsWith : options.startsWith;
			delete linkoptions.startsWith;
		}
		var res=S.html.link(title,url,linkoptions);
		if(linkoptions!=null) res=S.html.tag('li',lioptions,res,0);
		return res;
	}
};