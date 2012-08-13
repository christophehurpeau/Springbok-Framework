(function(){
	S.Route=function(attrs){
		S.extendsObj(this,attrs);
	};
	S.Route.prototype={
		
	};
	var routes={},routesLangs={};
	S.router={
		DEFAULT:{controller:'Site',action:'Index'},
		init:function(r,rl){
			$.each(r,function(url,route){
				routes[url]={_:route[0]};
				var paramsDef=route[1] || null, langs=route[2] || null;
				if(route.ext) routes[url].ext=route.ext;
				
				route=langs || {};
				route['en']=url;
				$.each(route,function(lang,routeLang){
					var paramsNames=[],specialEnd,specialEnd2,routeLangPreg;
					if(specialEnd=routeLang.sbEndsWith('/*')) routeLangPreg=routeLang.substr(0,-2);
					else if(specialEnd2=routeLang.sbEndsWith('/*)?')) routeLangPreg=routeLang.substr(0,routeLang.length-4)+routeLang.substr(routeLang.length-2);
					else routeLangPreg=routeLang;
					
					routeLangPreg=routeLangPreg.replace('/','\/').replace('-','\-').replace('*','(.*)').replace('(','(?:');
					if(specialEnd) routeLangPreg+='(?:\/(.*))?';
					else if(specialEnd2) routeLangPreg=routeLangPreg.substr(0,routeLang.length-2)+'(?:\/(.*))?'+routeLangPreg.substr(routeLang.length-2);
					
					routes[url][lang]=[new RegExp("^"+routeLangPreg.replace(/(\(\?)?\:([a-zA-Z_]+)/g,function(str,p1,p2){
						if(p1) return str;
						paramsNames.push(p2);
						if(paramsDef && paramsDef[p2]) return paramsDef[p2]==='id' ? '([0-9]+)' : '('+paramsDef[p2]+')';
						if(['id'].sHas(p2) !== -1) return '([0-9]+)';
						return '([^\/]+)';
					}) + (routes[url].ext ? (routes[url].ext==='html' ? '(?:\.html)?':'\.'+routes[url].ext) : '')+"$"),
						routeLang.replace(/(\:[a-zA-Z_]+)/g,'%s').replace(/[\?\(\)]/g,'').replace('/*','%s').sbRtrim()];
					if(paramsNames) routes[url][':']=paramsNames;
				});
			});
			
			//langs
			$.each(rl,function(s,t){
				$.each(t,function(lang,s2){
					if(!routesLangs['en->'+lang]){
						routesLangs['en->'+lang]={};
						routesLangs[lang+'->en']={};
					}
					routesLangs['en->'+lang][s]=s2;
					routesLangs[lang+'->en'][s2]=s;
				});
			});
		},
		
		find:function(all){
			all=this.all='/'+all.sbTrim('/');
			console.log('router: find: "'+all+'"');
			var t=this,route=false,lang=S.langs.get(),m;
			$.each(routes,function(i,r){
				console.log('try: ',(r[lang]||r['en'])[0],(r[lang]||r['en'])[0].exec(all));
				if(m=(r[lang]||r['en'])[0].exec(all)){
					//console.log('match : ',m,r);
					var c_a=r['_'].split('::'),params={};
					
					if(r[':']){
						m.shift(); // remove m[0];
						var nbNamedParameters=r[':'].length,countMatches=m.length;
						if(countMatches !== 0){
							r[':'].sbEach(function(k,v){ params[v]=m.shift(); });
						}
						['controller','action'].sbEach(function(k,v){
							if(c_a[k]==='!'){
								if(params[v]){
									c_a[k]=t.untranslate(params[v]).sbUcFirst();
									delete params[v];
								}else c_a[k]=t.DEFAULT[v];
							}
						});
					}
					
					route=new S.Route({
						all:all,
						controller:c_a[0],
						action:c_a[1],
						nParams:params,//named
						sParams:m,//simple
						ext:false
					});
					return false;
				}
			});
			
			if(!route) notFound();
			return route;
		},
		
		getLink:function(url){
			return S.isString(url) ? this.getStringLink(url) : this.getArrayLink(url);
		},
		
		/* Exemples :
		* S.router.getArrayLink(['/:id-:slug',post.id,post.slug])
		*/
		getArrayLink:function(params){
			var options=S.isObject(url.sbLast()) ? url.pop() : {},
				plus=options['?'] ? '?'+options['?'] : '',
				route=routes[params.shift()];
			if(options.ext) plus+='.'+params.ext;
			else if(route.ext) plus+= '.'+route.ext;
			if(options['#']) plus+='#'+options['#'];
		
			return params ? route[S.lang][1].sbVFormat(params)+plus : route[S.lang][1]+plus;
		},
		
		/* Exemples :
		* S.html.url('/site/login')
		*/
		getStringLink:function(params){
			var route=params.sbTrim('\/').split('/',3), controller=route[0], action=route[1] || this.DEFAULT_ACTION, params= route[2];
			route=routes['/:controller(/:action/*)?'];
			var froute= action==this.DEFAULT_ACTION ?  '/'+ this.translate(controller) : 
				route['en'][1].sbFormat(this.translate(controller),this.translate(action),params ? '/'+params : '');
			return froute + (route.ext && !froute.sbEndsWith('.'+route.ext)?'.'+route.ext:'');
		},
		
		translate:function(string){
			return routesLangs['en->'+S.lang][string] || string;
		},
		untranslate:function(string){
			return routesLangs[S.lang+'->en'][string] || string;
		}
	};
})();
