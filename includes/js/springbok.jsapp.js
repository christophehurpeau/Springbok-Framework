includeLib('jquery-1.7.min');
includeCore('springbok.base');

S.loadSyncScript(staticUrl+'js/i18n-'+i18n_lang+'.js');

(function($){
	var readyCallbacks=$.Callbacks();
	S.app={
		name:'',version:1,
		header:'',footer:true,
		
		jsapp:function(name,version){this.name=name;this.version=version;},
		
		init:function(){
			if(this.footer===true) this.footer=this.name+' | '+S.dates.niceDateTime(this.version*1000)+' | '+S.html.powered();
			$('#container').html('').append($('<header/>').html(this.header))
				.append('<div id="page"/>')
				.append($('<footer/>').html(this.footer));
		},
		
		ready:function(callback){
			readyCallbacks.add(callback);
		},
		
		run:function(){
			S.app.init();
			readyCallbacks.fire();
			S.history.loadUrl();//TODO duplicate if #
		}
	};
	S.ready=S.app.ready;
}(jQuery));

includeCore('jsapp/httpexceptions');
includeCore('jsapp/langs');
includeCore('jsapp/controller');
includeCore('springbok.router');
includeCore('springbok.html');
includeCore('springbok.menu');
includeCore('springbok.forms');
includeCore('springbok.date');
includeCore('springbok.ajax');

S.history.loadUrl=function(fragmentOverride){
	var fragment = S.history.getFragment(fragmentOverride),loadedControllers={};
	if(fragment){
		if(fragment.sbStartsWith(basedir)) fragment = fragment.substr(basedir.length);
		try{
			var route=S.router.find(fragment);
			console.log(route);
			if(!loadedControllers[route.controller]){
				loadedControllers[route.controller]=true;
				S.loadSyncScript(staticUrl+'js/jsapp/'+route.controller+'.js');
			}
			
		}catch(err){
			if(err instanceof HttpException){
				
			}
			console.log(err);
		}
	}
};

S.app.run();
