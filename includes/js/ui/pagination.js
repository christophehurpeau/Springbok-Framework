S.HPagination=function(container,options,callback){
	if(S.isFunc(options)){ callback=options; options={}; }
	this.callback=callback || function(page){
		//TODO : default behavior
	};
	this.$pager=$('<ul class="pager">').appendTo($('<div class="pager">').appendTo(container));
	
	//this.totalPages=pager.find('li.page:last').text();
	this.options=S.extObj({nbBefore:3,nbAfter:3,hidden:true,withText:false},options);
};



S.HPagination.prototype={
	init:function(result,page){
		this.empty();
		this.totalPages=result.totalPages;
		this.update(page||1);
	},
	empty:function(){
		this.$pager.empty();
	},
	load:function(page){
		this.$pager.empty();
		this.callback(page);
		this.update(page);
	},
	update:function(page){
		var t=this,options=t.options,pager=t.$pager,totalPages=this.totalPages,
			createLink=function(page,text){ return $('<a/>').click(function(){t.load(page)}).html(text||page); }
		if(options.withText){
			if(page==1){
				if(options.hidden) pager.append('<li class="first hidden"><a href="javascript:;">&lt;&lt;'+(options.withText?' '+i18nc.Start:'')+'</a></li>'
						+'<li class="previous hidden"><a href="javascript:;">&lt;'+(options.withText?' '+i18nc.Previous:'')+'</a></li>');
			}else{
				pager.append($('<li class="first">').html(createLink(1,'&lt;&lt;'+(options.withText?' '+i18nc.Start:''))),
					$('<li class="previous">').html(createLink(page-1,'&lt;&lt;'+(options.withText?' '+i18nc.Previous:''))));
			}
		}

		var start=page-options.nbBefore;

		if(start <= 1) start=1;
		else{
			var i=1; pager.append($('<li class="page">').html(createLink(i)));
			i=Math.ceil(start/2);
			if(i != 1){
				if(i != 2) pager.append('<li>&nbsp</li>');
				pager.append($('<li class="page">').html(createLink(i)));
				if(i+1 != start) pager.append('<li>&nbsp</li>');
			}
		}
		
		var end=page+options.nbAfter; if(end > totalPages) end=totalPages;
		
		var i=start;
		while(i<=end)
			pager.append($('<li class="page'+(i==page?' selected':'')+'">').html(createLink(i++)));

		if(end < totalPages){
			i=Math.floor((totalPages-i)/2+end);
			if(i != end){
				if(end+1 != i) pager.append('<li>&nbsp</li>');
				pager.append($('<li class="page">').html(createLink(i)));
			}
			if(i!=totalPages-1) pager.append('<li>&nbsp</li>');
			i=totalPages;
			pager.append($('<li class="page">').html(createLink(i)));
		}
		
		if(options.withText){
			if(page>=totalPages){
				if(options.hidden) pager.append('<li class="next hidden"><a href="javascript:;">'+(options.withText?i18nc.Next+' ':'')+'&gt;</a></li>'
					+'<li class="last hidden"><a href="javascript:;">'+(options.withText?i18nc.End+' ':'')+'&gt;&gt;</a></li>');
			}else{
				pager.append($('<li class="next">').html(createLink(page+1,(options.withText?i18nc.Next+' ':'')+'&gt;')),
					$('<li class="last">').html(createLink(totalPages),(options.withText?i18nc.End+' ':'')+'&gt;&gt;'));
			}
		}
	}
};