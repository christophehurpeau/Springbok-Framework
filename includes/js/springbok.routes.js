var Route=function(route){
	this.controller=route.controller;
	this.action=route.action;
	this.params=route.params;
	this.ext=route.ext;
}

(function(){
	var routes=[],routesLangs=[];
	$$.router={
		langs:{},
		all:'',controller:'',action:'',params:'',ext:'',
		
		init:function(newRoutes){
			routes=[];
			$.each(routes,function(i,r){
				routes.push({'en':new RegExp('^'+r[0]+'$')});
			});
		},
		
		initLangs:function(rL){
			rl.each(function(s,t){
				t.each(function(lang,s2){
					routesLangs['en->'+lang][s]=s2;
					routesLangs[lang+'->en'][s2]=s;
				});
			});
		},
		
		find:function(all){
			this.all='/'+all.trim('/');
			console.log(this.all);
			var route=false,lang=$$.langs.get();
			$.each(routes,function(i,r){
				if(r[lang].matches(all)){
					console.log(['matches!',r]);
					route=new Route({
						controller:r.controller
					});
					return false;
				}
			});
			
			if(!route) notFound();
		},
		
		getLink:function(url){
			return $$.isString(url) ? this.getStringLink(url) : this.getArrayLink(url);
		},
		
		//['/:controller/:action','']
		getArrayLink:function(params){
			var plus='';
			if(params['?']){ plus='?'+params['?']; delete params['?']; }
			var link=array_shift($params);
			route=routes[link];
			if(params.ext){ plus+='.'+params.ext; delete params.ext; }
			else if(route.ext){ plus+= '.'+route.ext; }
			if(params['#']){ plus+='#'+params['#']; delete params['#']; }
		
			if(!params) return route[i18n_lang][1]+plus;
			///* DEV */self::$_prefix./* /DEV */
			return vsprintf(route[i18n_lang][1],params)+plus;
		},
	
	public static function getStringLink(&$params){
		$route=explode('/',trim($params,'/'),3);
		$controller=$route[0];
		$action=isset($route[1])?$route[1]:self::DEFAULT_ACTION;
		$params=isset($route[2])?$route[2]:NULL;
		if($action==self::DEFAULT_ACTION) $route=self::$_routes['/:controller'];
		else $route=self::$_routes['/:controller/:action/*'];
		$lang=CLang::get();
		$froute=/* DEV */self::$_prefix./* /DEV */sprintf($route['en'][1],CRoute::translate($controller,$lang),CRoute::translate($action,$lang),$params===NULL?'':'/'.$params);
		return $froute.(isset($route['ext'])&&!endsWith($froute,'.'.$route['ext'])?'.'.$route['ext']:'');
	}

	public static function translate($string,$lang){
		if(!isset(self::$_langs['en->'.$lang][$string])) return $string;
		return self::$_langs['en->'.$lang][$string];
	}

	public static function untranslate($string,$lang){
		if(!isset(self::$_langs[$lang.'->en'][$string])) return $string;
		return self::$_langs[$lang.'->en'][$string];
	}
	};
})();
