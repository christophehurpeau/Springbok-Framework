includeLib('jquery-1.7.min');
includeCore('springbok.base');

$$.loadSyncScript(staticUrl+'js/i18n-'+i18n_lang+'.js');

(function($){
	var readyCallbacks=$.Callbacks();
	$$.app={
		name:'',version:1,
		header:'',footer:true,
		
		jsapp:function(name,version){this.name=name;this.version=version;},
		
		init:function(){
			if(this.footer===true) this.footer=this.name+' | '+$$.dates.niceDateTime(this.version*1000)+' | '+$$.html.powered();
			$('#container').html('').append($('<header/>').html(this.header))
				.append('<div id="page"/>')
				.append($('<footer/>').html(this.footer));
		},
		
		ready:function(callback){
			readyCallbacks.add(callback);
		},
		
		run:function(){
			$$.app.init();
			readyCallbacks.fire();
			$$.history.loadUrl();//TODO duplicate if #
		}
	};
	$$.ready=$$.app.ready;
}(jQuery));

includeCore('springbok.langs');
includeCore('springbok.router');
includeCore('springbok.html');
includeCore('springbok.menu');
includeCore('springbok.forms');
includeCore('springbok.date');
includeCore('springbok.ajax');

$$.history.loadUrl=function(fragmentOverride){
	var fragment = $$.history.getFragment(fragmentOverride);
	if(fragment){
		if(fragment.sbStartsWith(basedir)) fragment = fragment.substr(basedir.length);
		try{
			var route=$$.router.find(fragment);
		}catch(err){
			console.log(err);
		}
	}
};

$$.app.run();