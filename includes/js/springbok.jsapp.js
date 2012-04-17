includeLib('jquery-1.7.min');
includeCore('springbok.base');

S.loadSyncScript(staticUrl+'js/i18n-'+i18n_lang+'.js');

(function($){
	var readyCallbacks=$.Callbacks(),loadedRequired={};
	S.app={
		name:'',version:1,
		header:'',footer:true,page:0,
		controllers:{},layouts:{},
		
		jsapp:function(name,version){this.name=name;this.version=version;},
		
		init:function(){
			if(this.footer===true) this.footer=this.name+' | '+S.dates.niceDateTime(this.version*1000)+' | '+S.html.powered();
			$('#container').html('').append($('<header/>').html(this.header),this.page=$('<div id="page"/>'),$('<footer/>').html(this.footer));
		},
		
		ready:function(callback){
			readyCallbacks.add(callback);
		},
		
		run:function(){
			S.app.init();
			readyCallbacks.fire();
			S.ajax.load(S.history.getFragment());//TODO duplicate if #
		},
		
		require:function(){
			$.each(arguments,function(k,fileName){
				if(!loadedRequired[fileName]){
					loadedRequired[fileName]=true;
					S.loadSyncScript(staticUrl+'js/app/'+fileName+'.js'/* DEV */+'?'+(new Date().getTime())/* /DEV */);
				}
			});
		},
		
		api:{
			//List,Retrieve
			get:function(url){
				var result,headers={};
				jQuery.ajax({
					headers:headers,
					url: url,
					data: data,
					success: function(r){result=r;},
					dataType:'json',
					async:false
				});
				return result;
			},
			//Create
			post:function(){},
			//Replace
			put:function(){},
			//Delete
			del:function(){},
		}
	};
	S.ready=S.app.ready;
}(jQuery));

includeCore('jsapp/httpexceptions');
includeCore('jsapp/langs');
includeCore('jsapp/controller');
includeCore('jsapp/layout');
includeCore('springbok.router');
includeCore('springbok.html');
includeCore('springbok.menu');
includeCore('springbok.forms');
includeCore('springbok.date');
includeCore('springbok.ajax');
includeCore('springbok.storage');

S.app.load=S.ajax.load=function(url){
	if(url.sbStartsWith(basedir)) url = url.substr(basedir.length);
	try{
		var route=S.router.find(url);
		//console.log(route);
		S.history.navigate(url);
		S.app.require('c/'+route.controller);
		S.app.controllers[route.controller].dispatch(route);
	}catch(err){
		if(err instanceof HttpException){
			
		}
		console.log("APP : catch error :",err);
	}
};
