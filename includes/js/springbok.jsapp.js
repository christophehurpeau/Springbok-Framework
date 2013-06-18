includeCore('libs/jquery-latest');
includeCore('springbok.base');

S.loadSyncScript(webUrl+'js/'+INCLPREFIX+'i18n-'+(S.lang=$('meta[name="language"]').attr('content'))+'.js');

(function(){
	var readyCallbacks=$.Callbacks(),loadedRequired={};
	window.App={
		header:'',footer:true,page:0,
		
		jsapp:function(name,version){this.name=name;this.version=version;},
		
		init:function(){
			if(this.footer===true) this.footer=this.name+' | '+S.dates.niceDateTime(this.version*1000)+' | '+S.html.powered();
			$('#container').html('').append($('<header/>').html(this.header),this.page=$('<div id="page"/>'),$('<footer/>').html(this.footer));
		},
		
		ready:function(callback){
			readyCallbacks.add(callback);
		},
		
		run:function(){
			App.init();
			readyCallbacks.fire();
			S.ajax.load(S.history.getFragment());//TODO duplicate if #
		},
		
		require:function(){
			$.each(arguments,function(k,fileName){
				if(!loadedRequired[fileName]){
					loadedRequired[fileName]=true;
					S.loadSyncScript(webUrl+'js/'+INCLPREFIX+fileName+'.js'/*#if DEV*/+'?'+(new Date().getTime())/*#/if*/);
				}
			});
		},
		
		api:{
			//List,Retrieve
			get:function(url,data,callback,type,async){
				if(S.isFunc(data)){ callback=data; data=undefined; }
				var headers={};
				if(S.CSecure&&S.CSecure.isConnected()) headers.SAUTH=S.CSecure._token;
				jQuery.ajax({
					type:type,
					headers:headers,
					url:baseUrl+'api/'+url,
					data:data,
					success:function(r){ callback(r); },
					error:function(jqXHR, textStatus, errorThrown){
						console&&console.log('Error:',jqXHR);
						if(jqXHR.status===403){
							if(S.CSecure&&S.CSecure.isConnected()) S.CSecure.reconnect();
						}
					},
					dataType:'json', cache:false, async:async
				});
			},
			getSync:function(url,data,type){
				var result;
				this.get(url,data,function(r){result=r},type,false);
				return result;
			},
			
			//Create
			post:function(url,data,callback){
				return this.get(url,data,callback,'POST');
			},
			//Replace
			put:function(url,data){
				return this.get(url,data,callback,'PUT');
			},
			//Delete
			del:function(url,data){
				return this.get(url,data,callback,'DELETE');
			}
		}
	};
	S.ready=App.ready;
}());

function FatalError(error){
	alert(error);
	$('#jsAppLoadingMessage').addClass('message error').text(error);
}

includeCore('jsapp/httpexceptions');
includeCore('jsapp/langs');
includeCore('jsapp/controller');
includeCore('jsapp/model');
includeCore('jsapp/layout');
includeCore('helpers/form');
includeCore('springbok.router');
includeCore('springbok.html');
includeCore('springbok.menu');
includeCore('springbok.forms');
includeCore('springbok.date');
includeCore('springbok.ajax');
includeCore('springbok.storage');

App.load=S.ajax.load=function(url){
	if(url.startsWith(baseUrl)) url = url.substr(baseUrl.length);
	try{
		var route=S.router.find(url);
		if(!route) notFound();
		//console.log(route);
		S.history.navigate(url);
		App.require('c/'+route.controller);
		var c=C[route.controller];
		/*#if DEV*/ if(!c) console&&console.log('This controller doesn\'t exists: "'+route.controller+'".'); /*#/if*/
		if(!c) notFound();
		c.dispatch(route);
	}catch(err){
		if(err instanceof S.Controller.Stop) return;
		if(err instanceof HttpException){
			console&&console.log("APP : catch HttpException :",err);
		}
		console&&console.log("APP : catch error :",err,S.isObj(err) ? 'message='+err.message:'');
		throw err;
	}
};
