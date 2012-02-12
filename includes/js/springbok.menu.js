$$.menu={
	tagName:'menu',
	top:function(links,options){
		return this.create(links,options,'top');
	},
	ajaxTop:function(links,options){
		return this.create(links,options,'top ajax');
	},
	left:function(links,options){
		return this.create(links,options,'left');
	},
	ul:function(links,options){
		this.tagname='ul';
		var res=this.create(links,options,'');
		this.tagname='menu';
		return res;
	},
	create:function(links,options,type){
		options=$.extend({},{menuAttributes:{'class':type},lioptions:{},
			linkoptions:{},startsWith:false},options);
		var res=$('<'+this.tagName+'/>').attr(options.menuAttributes);
		return $('<div/>').html(res).html();
	}
};