<?php
class CRoute{
	const DEFAULT_CONTROLLER='Site';
	const DEFAULT_ACTION='index';
	
	/* DEV */public static $_prefix,$TESTED_ROUTES=array();/* /DEV */
	private static $_routes,$_langs,
		$all,$controller,$action,$params,$ext;

	public static function init($prefix,$suffix){
		$routes=App::configArray('routes'.$suffix);
		self::$_routes=&$routes['routes'];
		self::$_langs=&$routes['langs'];
		/* DEV */self::$_prefix=$prefix;/* /DEV */
		
		$all=CHttpRequest::getPathInfo();
		self::initRoute($all);
	}
	
	public static function initRoute($all){
		$all='/'.trim($all,'/');
		self::$all=&$all;

		$route=CRoute::find($all);
		if(!$route) notFound();
		list(self::$controller,self::$action,self::$params,self::$ext)=$route;
	}
	
	public static function cliinit($prefix,$suffix){
		$routes=App::configArray('routes'.$suffix);
		self::$_routes=&$routes['routes'];
		self::$_langs=&$routes['langs'];
		/* DEV */self::$_prefix=$prefix;/* /DEV */
	}
	
	public static function &getAll(){return self::$all;}
	public static function &getController(){return self::$controller;}
	public static function &getAction(){return self::$action;}
	public static function &getParams(){return self::$params;}
	public static function &getExt(){return self::$ext;}

	public static function find($all){
		$lang=CLang::get(); $matches=array();
		foreach(self::$_routes as $route){
			if(preg_match(/* DEV */self::$TESTED_ROUTES[]=/* /DEV */'/^'.(isset($route[$lang])?$route[$lang][0]:$route['en'][0]).($route['ext']===NULL?'':'.'.$route['ext']).'$/Ui',$all,$matches)){
				/*$ext=isset($matches['ext'])?array_pop($matches):NULL;
				unset($matches[0],$matches['ext']);
				(?:\.(?<ext>[a-z]{2,4}))?
				*/
				
				$ext=$route['ext']===NULL?NULL:$route['ext'];
				unset($matches[0]);
				
				list($controller,$action)=explode('::',$route['_'],2);

				if(isset($route[':'])){
					$nbNamedParameters=count($route[':']);
					$countMatches=count($matches);
					if($countMatches !== 0){
						if($countMatches===$nbNamedParameters)
							$params=array_combine($route[':'],$matches);
						elseif($countMatches > $nbNamedParameters)
							$params=array_combine($route[':'],array_slice($matches,0,$nbNamedParameters));
						else
							$params=array_combine(array_slice($route[':'],0,$countMatches),$matches);
					}else $params=array();

					if($controller=='!'){
						if(isset($params['controller'])){
							$controller=ucfirst(self::untranslate($params['controller'],$lang));
							unset($params['controller']);
						}else $controller=self::DEFAULT_CONTROLLER;
					}elseif(substr($controller,-1)==='!'){
						if(isset($params['controller'])){
							$controller=substr($controller,0,-1).ucfirst(self::untranslate($params['controller'],$lang));
							unset($params['controller']);
						}else $controller=substr($controller,0,-1).self::DEFAULT_CONTROLLER;
					}
					if($action=='!'){
						if(isset($params['action'])){
							$action=self::untranslate($params['action'],$lang);
							unset($params['action']);
						}else $action=self::DEFAULT_ACTION;
					}
				}else{
					$params=array();
					if($controller==='!') $controller=self::DEFAULT_CONTROLLER;
					if($action==='!') $action=self::DEFAULT_ACTION;
				}
				if(isset($route[':']) && !empty($matches[$i=($nbNamedParameters+1)])) $params=$params+explode('/',$matches[$i]);
				return array($controller,$action,$params,$ext);
			}
		}
		return false;
	}
	public static function getLink($params){
		return is_array($params) ? self::getArrayLink($params) : self::getStringLink($params);
	}
	
	public static function getArrayLink(&$params){
		$plus='';
		if(isset($params['?'])){$plus='?'.$params['?']; unset($params['?']); }
		$link=array_shift($params);
		$route=&self::$_routes[$link];
		if(isset($params['ext'])){ $plus.='.'.$params['ext']; unset($params['ext']); }
		elseif(isset($route['ext'])){ $plus.= '.'.$route['ext']; }
		if(isset($params['#'])){$plus.='#'.$params['#']; unset($params['#']); }
		
		if(empty($params)) return $route[CLang::get()][1].$plus;
		return /* DEV */self::$_prefix./* /DEV */vsprintf($route[CLang::get()][1],$params).$plus;
	}
	
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
}