<?php
class CRoute{
	const DEFAULT_CONTROLLER='Site';
	const DEFAULT_ACTION='index';
	
	/* DEV */public static $_prefix,$TESTED_ROUTES=array();/* /DEV */
	private static $_routes,$_langs,
		$all,$controller,$action,$params,$ext;

	public static function init(/* DEV */$prefix/* /DEV */){
		$routes=App::configArray('routes');
		self::$_routes=$routes['routes'];
		self::$_langs=$routes['langs'];
		/* DEV */self::$_prefix=$prefix;/* /DEV */
		
		$all=CHttpRequest::getPathInfo();
		self::initRoute($all);
	}
	
	public static function initRoute($all){
		$all='/'.trim($all,'/');
		self::$all=$all;

		$route=CRoute::find($all);
		if(!$route)
			/* DEV */ throw new Exception('No route was found for the url : '.$all); /* /DEV */
			/* PROD */ notFound(); /* /PROD */
		
		list(self::$controller,self::$action,self::$params,self::$ext)=$route;
	}
	
	public static function setControllerAndAction($controller,$action){
		self::$controller=$controller;
		self::$action=$action;
	}
	
	public static function resolveRoute($url){
		return CRoute::find('/'.trim($url,'/'));
	}
	
	public static function cliinit(/* DEV */$prefix/* /DEV */){
		$routes=App::configArray('routes');
		self::$_routes=$routes['routes'];
		self::$_langs=$routes['langs'];
		/* DEV */self::$_prefix=$prefix;/* /DEV */
	}
	
	public static function getAll(){return self::$all;}
	public static function getController(){return self::$controller;}
	public static function getAction(){return self::$action;}
	public static function getControllerActionRoute(){return '/'.self::$controller.(self::$action!==self::DEFAULT_ACTION?'/'.self::$action:''); }
	public static function getRoute(){return array(true,'/'.self::getControllerActionRoute());}
	public static function getParams(){return self::$params;}
	public static function getExt(){return self::$ext;}

	public static function find($all){
		$lang=CLang::get(); $matches=array();
		foreach(self::$_routes[Springbok::$scriptname] as $route){
			if(preg_match(/* DEV */self::$TESTED_ROUTES[]=/* /DEV */'/^'.(isset($route[$lang])?$route[$lang][0]:$route['en'][0]).'$/Ui',$all,$matches)){
				/*$ext=isset($matches['ext'])?array_pop($matches):NULL;
				unset($matches[0],$matches['ext']);
				(?:\.(?<ext>[a-z]{2,4}))?
				*/
				$ext=$route['ext']===null?null:substr(array_pop($matches),1);/*$route['ext'];*/
				unset($matches[0]);
				
				list($controller,$action)=explode('::',$route['_'],2);
				
				if(isset($route[':'])){
					$nbNamedParameters=count($route[':']);
					$countMatches=count($matches);
					
					if($ext!==null){
						while($countMatches > 1){
							//$i=$countMatches;debugVar($matches[$i]);
							if($matches[$countMatches]===''){
								unset($matches[$countMatches]);
								$countMatches--;
							}else break;
						}
					}
					
					if($countMatches !== 0){
						if($countMatches===$nbNamedParameters)
							$params=array_combine($route[':'],$matches);
						elseif($countMatches > $nbNamedParameters)
							$params=array_combine($route[':'],array_slice($matches,0,$nbNamedParameters));
						else
							$params=array_combine(array_slice($route[':'],0,$countMatches),$matches);
					}else $params=array();

					if($controller==='!'){
						if(!empty($params['controller'])){
							$controller=ucfirst(self::untranslate($params['controller'],$lang));
							unset($params['controller']);
						}else $controller=self::DEFAULT_CONTROLLER;
					}elseif(substr($controller,-1)==='!'){
						if(isset($params['controller'])){
							$controller=substr($controller,0,-1).ucfirst(self::untranslate($params['controller'],$lang));
							unset($params['controller']);
						}else $controller=substr($controller,0,-1).self::DEFAULT_CONTROLLER;
					}
					if($action=='!' || isset($params['action'])){
						if(!empty($params['action'])){
							$action=self::untranslate($params['action'],$lang);
							unset($params['action']);
						}else $action=self::DEFAULT_ACTION;
					}
					
					if(!empty($matches[$i=($nbNamedParameters+1)])) $params=$params+explode('/',$matches[$i]);
				}else{
					$params=array();
					if($controller==='!') $controller=self::DEFAULT_CONTROLLER;
					if($action==='!') $action=self::DEFAULT_ACTION;
				}
				return array($controller,$action,$params,$ext);
			}
		}
		return false;
	}
	
	public static function getArrayLink($entry,$params){
		$plus='';
		$link=array_shift($params);
		if($link !==true){
			$route=self::$_routes[$entry][$link];
			/* DEV */
			if($route===null) throw new Exception("CRoute getLink: This route does not exists: ".$link);
			/* /DEV */
		}
		if(isset($params['ext'])) $plus.='.'.$params['ext'];
		elseif(isset($route['ext'])) $plus.= '.'.$route['ext'];
		if(isset($params['?'])) $plus.='?'.$params['?'];
		if(isset($params['#'])) $plus.='#'.$params['#'];
		
		if(isset($params['lang'])) $lang=$params['lang']; else $lang=CLang::get();
		
		unset($params['ext'],$params['?'],$params['#'],$params['lang']);
		
		if(empty($params)) return $route[$lang][1].$plus;
		$url=($link===true?self::getStringLink($entry,$params[0]):vsprintf($route[CLang::get()][1],$params));
		return /* DEV */self::$_prefix./* /DEV */($url==='/'?'/':rtrim($url,'/')).$plus;
	}
	
	public static function getStringLink($entry,$params){
		$route=explode('/',trim($params,'/'),3);
		$controller=$route[0];
		$action=isset($route[1])?$route[1]:self::DEFAULT_ACTION;
		$params=isset($route[2])?$route[2]:null;
		$lang=CLang::get(); $route=self::$_routes[$entry]['/:controller(/:action/*)?'];
		
		if($action==self::DEFAULT_ACTION)
			$froute='/'.self::translate($controller,$lang);
		else
			$froute=sprintf($route['en'][1],self::translate($controller,$lang),self::translate($action,$lang),$params===null?'':'/'.$params); 
		
		return /* DEV */self::$_prefix./* /DEV */$froute.(isset($route['ext'])&&!endsWith($froute,'.'.$route['ext'])?'.'.$route['ext']:'');
	}

	public static function translate($string,$lang){
		$stringT=strtolower($string);
		if(!isset(self::$_langs['en->'.$lang][$stringT])) return $string;
		return self::$_langs['en->'.$lang][$stringT];
	}

	public static function untranslate($string,$lang){
		$stringT=strtolower($string);
		if(!isset(self::$_langs[$lang.'->en'][$stringT])) return $string;
		return self::$_langs[$lang.'->en'][$stringT];
	}
}