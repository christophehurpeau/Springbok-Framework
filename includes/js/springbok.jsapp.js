includeLib('jquery-1.7.min');
includeCore('springbok.base');
includeCore('springbok.html');
includeCore('springbok.menu');
includeCore('springbok.forms');

(function($){
	$$.app={
		name:'',version:1,
		header:'',footer:true,
		
		jsapp:function(name,version){this.name=name;this.version=version;},
		
		init:function(){
			if(this.footer===true) this.footer=this.name+' | '+$$.dates.niceDateTime(this.version*1000)+' | '+$$.html.powered();
			$('#container').html('').append($('<header/>').html(this.header))
				.append('<div id="page"/>')
				.append($('<footer/>').html(this.footer));
		}
	};
	
	$(document).ready(function(){
		$$.app.init();
	});
}(jQuery));
	
includeCore('springbok.ajax');

$$.history.loadUrl=function(fragmentOverride){
	var fragment = $$.history.getFragment(fragmentOverride);
	if(fragment){
		if(fragment.startsWith(basedir)) fragment = fragment.substr(basedir.length);
		try{
			var route=$$.router.find(fragment);
		}catch(err){
			console.log(err);
		}
	}
};

$(document).ready(function(){
	$$.history.loadUrl();
});