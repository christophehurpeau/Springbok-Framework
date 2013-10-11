<?php
/**
 * Html Helper
 */
class HHtml{
	private static $isIElt8=false;
	
	/**
	 * Return html5 doctype or xhtml for old IE browsers
	 * 
	 * @return string
	 */
	public static function doctype(){
		return (self::$isIElt8=CHttpUserAgent::isIElt8()) ? '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">' : '<!DOCTYPE html>';
	}
	
	/** @deprecated */
	public static function linkRSS($title,$url){
		/*#if DEV */throw new Exception('Use HHead::linkRss() now'); /*#/if*/
		echo '<link rel="alternate" type="application/rss+xml" href="'.self::url($url).'" title="'.$title.'"/>';
	}
	
	/** @deprecated */
	public static function linkAtom($title,$url){
		/*#if DEV */throw new Exception('Use HHead::linkAtom() now'); /*#/if*/
		echo '<link rel="alternate" type="application/atom+xml" href="'.self::url($url).'" title="'.$title.'"/>';
	}
	
	/**
	 * Return the meta charset in html5 or xhtml
	 * 
	 * @param string
	 * @return string
	 */
	public static function metaCharset($encoding='utf-8'){
		/*#if DEV */if(self::$isIElt8===null) throw new Exception('Call HHtml::doctype() to know if request is IE < 8'); /*#/if*/
		return self::$isIElt8 ? '<meta http-equiv="Content-Type" content="text/html; charset='.$encoding.'"/>' : '<meta charset="'.$encoding.'">';
	}
	
	/**
	 * Return the meta language, if lang is null with default lang
	 * 
	 * @param string|null
	 * @return string
	 * @uses CLang::get()
	 */
	public static function metaLanguage($lang=null){
		if($lang===null) $lang=CLang::get();
		return '<meta name="language" content="'.$lang.'"/><meta http-equiv="content-language" content="'.$lang.'"/>';
	}
	
	/** @deprecated */
	public static function cssLink($url='/main',$media=false){
		/*#if DEV */throw new Exception('Use HHead::linkCss() now'); /*#/if*/
		return HHead::linkCss($url,$media);
	}
	
	/** @deprecated */
	public static function favicon($imgUrl='favicon.png'){
		/*#if DEV */throw new Exception('Use HHead::favicon() now'); /*#/if*/
		return HHead::favicon($imgUrl);
	}

	/** @deprecated */
	public static function logoMobile($imgNamePrefix='logo'){
		/*#if DEV */throw new Exception('Use HHead::icons() now'); /*#/if*/
		return HHead::icons($imgNamePrefix);
	}
	
	/** @deprecated */
	public static function cssLinks(){
		/*#if DEV */throw new Exception('Use HHead::display() now'); /*#/if*/
	}
	
	/** @deprecated */
	public static function addCSS($url,$media=false){
		/*#if DEV */throw new Exception('Use HHead::linkCss() now'); /*#/if*/
		HHead::linkCss($url,$media);
	}
	
	/**
	 * Inline css
	 * 
	 * @param string
	 * @param array
	 * @return string
	 */
	public static function cssInline($content,$attributes=array()){
		return '<style type="text/css"'.self::_echoAttributes($attributes).'>'.$content.'</style>';
	}

	/**
	 * Start buffer for inline css
	 * 
	 * @return void
	 * @uses ob_start()
	 */
	public static function cssInlineStart(){
		ob_start();
	}
	
	/**
	 * End buffer for inline css and echo the inline css
	 * 
	 * @return void
	 * @uses ob_get_clean()
	 * @uses HHtml::cssInline()
	 */
	public static function cssInlineEnd(){
		echo self::cssInline(ob_get_clean());
	}

	/**
	 * Inline js
	 * 
	 * @param string
	 * @return string
	 */
	public static function jsInline($content){
		return '<script type="text/javascript">//<![CDATA[
'.trim($content).'
//]]>
</script>';
	}
	
	private static $jsReady='';
	
	/**
	 * Append javascript, displayed in the layout with HHtml::displayJsReady()
	 * 
	 * @param string
	 * @return void
	 */
	public static function jsReady($content){
		self::$jsReady.=rtrim(trim($content),';').';';
	}
	
	/**
	 * Display javascript appended in HHtml::jsReady()
	 * The javascript is wrapped in a function in S.ready()
	 * 
	 * @return void
	 */
	public static function displayJsReady(){
		if(self::$jsReady==='') return;
		echo '<script type="text/javascript">//<![CDATA[
S.ready(function(){'.substr(self::$jsReady,0,-1).'})
//]]>
</script>';
	}
	
	private static $jsHead='';
	
	/**
	 * Append javascript, displayed in the layout with HHtml::displayJsHead()
	 * 
	 * @param string
	 * @return void
	 */
	public static function jsHead($content){
		self::$jsHead.=rtrim(trim($content),';').';';
	}
	
	/**
	 * Display javascript appended in HHtml::jsHead()
	 * 
	 * @return void
	 */
	public static function displayJsHead(){
		if(self::$jsHead==='') return;
		echo '<script type="text/javascript">//<![CDATA[
'.substr(self::$jsHead,0,-1).'
//]]>
</script>';
	}
	
	/** @deprecated */
	public static function jsLink($url='/global'){
		/*#if DEV */throw new Exception('Use HHead::linkJs() now'); /*#/if*/
		return HHead::linkJs($url);
	}
	
	/** @deprecated */
	public static function jsLinks(){
		/*#if DEV */throw new Exception('Use HHead::display() now'); /*#/if*/
		if(isset(self::$_JS['all']))
			foreach(self::$_JS['all'] as $url) self::jsLink($url);
		if(isset(self::$_JS['ie']))
			foreach(self::$_JS['ie'] as $for=>$scripts){
				echo '<!--[if ';
				if(empty($for)) echo 'IE';
				else{
					$for=explode(' ',$for);
					if(!isset($for[1])) echo 'IE '.$for[0];
					else{
						switch ($for[0]){
							case '<': echo 'lt'; break;
							case '>': echo 'gt'; break;
							case '<=': echo 'lte'; break;
							case '>=': echo 'gte'; break;
						
							default: die('Unknown: '.$for);
						}
						echo ' IE '.$for[1];
					}
				}
				echo ']>';
				foreach($scripts as $url) self::jsLink($url);
				echo '<![endif]-->';
			}
	}
	/** @deprecated */
	public static function addJS($url){
		/*#if DEV */throw new Exception('Use HHead::linkAddJs() now'); /*#/if*/
		return HHead::linkJs($url);
	}
	/** @deprecated */
	public static function jsI18n(){
		/*#if DEV */throw new Exception('Use HHead::jsI18n() now'); /*#/if*/
		return self::jsLink('/i18n-'.CLang::get());
	}
	
	/**
	 * Start buffer for inline js
	 * 
	 * @return void
	 * @uses ob_start()
	 */
	public static function jsInlineStart(){
		ob_start();
	}
	
	/**
	 * Returns inline javascript
	 * 
	 * @return string
	 * @uses HHtml::jsInline
	 * @uses ob_get_clean
	 */
	public static function jsInlineEnd(){
		return self::jsInline(str_replace("\t",'',ob_get_clean()));
	}
	
	/**
	 * Starts a new buffer
	 * 
	 * @return void
	 * @uses ob_start
	 */
	public static function jsReadyStart(){
		ob_start();
	}
	
	/**
	 * Appends onready javascript
	 * 
	 * @return void
	 * @uses HHtml::jsReady
	 */
	public static function jsReadyEnd(){
		self::jsReady(str_replace("\t",'',ob_get_clean()));
	}
	
	/**
	 * Return an escaped value for js use
	 * 
	 * @param mixed
	 * @return string
	 */
	public static function jsEscape($string){
		return json_encode($string,JSON_UNESCAPED_UNICODE);
	}
	
	/** @deprecated */
	public static function addJS4IE($url,$for){
		/*#if DEV */throw new Exception('Use HHead::linkJsIe($ieVersion,$operator,$url) now. Note : if you have several scripts use HHead::startIeIf($ieVersion,$operator);HHead::linkJs($url);... HHead::endIeIf();'); /*#/if*/
		$for=explode(' ',$for);
		HHead::linkJsIe($for[1],$for[0],$url);
	}
	
	/**
	 * Add the google analytics script in the head
	 * 
	 * @param string
	 * @param bool
	 * @param string
	 * @return void
	 */
	public static function ganalytics($code,$trackPageLoadTime=false,$customVars=''){
		self::jsHead('
var _gaq=[["_setAccount","'.$code.'"],[\'_trackPageview\']'.($trackPageLoadTime?",['_trackPageLoadTime']":'').'];'.$customVars.'
(function(d,t){
var g=d.createElement(t),s=d.getElementsByTagName(t)[0];
g.type=\'text/javascript\';g.async=1;g.src=\''.(IS_HTTPS?'https://ssl':'http://www').'.google-analytics.com/ga.js\';
s.parentNode.insertBefore(g,s);
})(document,\'script\')');
	}
	
	/**
	 * Add the google analytics script in the head, for multi tracking
	 * 
	 * @param string
	 * @param string
	 * @param bool
	 * @return void
	 */
	public static function ganalyticsMultiTracker($codes,$domainName,$trackPageLoadTime=false){
		$gaq= 'var _gaq = _gaq || [];_gaq.push([\'_setDomainName\', \''.$domainName.'\']';
		foreach($codes as $key => $value) 
			$gaq.= ',[\''.$key.'_setAccount\',\''.$value.'\'],[\''.$key.'_trackPageview\']'.($trackPageLoadTime?",['".$key."_trackPageLoadTime']":'');
		self::jsHead($gaq.');
(function(d,t){
var g=d.createElement(t),s=d.getElementsByTagName(t)[0];
g.type=\'text/javascript\';g.async=1;g.src=\''.(IS_HTTPS?'https://ssl':'http://www').'.google-analytics.com/ga.js\';
s.parentNode.insertBefore(g,s);
})(document,\'script\')');
	}
	
	/**
	 * @return string
	 */
	public static function outdatedBrowser(){
		 return '<!--[if lt IE 7]> <p class="chromeframe">You are using an outdated browser. <a href="http://browsehappy.com/">Upgrade your browser today</a> or <a href="http://www.google.com/chromeframe/?redirect=true">install Google Chrome Frame</a> to better experience this site.</p> <![endif]-->';
	}
	
	/** @deprecated */
	public static function link($title,$url=false,$options=array()){
		$options=$options+array('confirm'=>false,'entry'=>null,'fullUrl'=>null,'cache'=>false,'https'=>null);
		/*#if DEV*/ $options['data-url-origin-DEV']=UVarDump::dump($url,5,false); /*#/if*/
		if($url){
			if($url!=='#' && $url[0]!=='?' && (is_array($url) || (substr($url,0,11)!=='javascript:' && substr($url,0,7)!=='mailto:')))
				$url=self::url($url,$options['entry'],$options['fullUrl'],false,$options['cache'],$options['https']);
			if($title===null) $title=$url;
		}else $title=$url=self::url($title,$options['entry'],$options['fullUrl'],false,$options['cache'],$options['https']);
		
		
		if(isset($options['escape'])) $escape=$options['escape'];
		else $escape=true;

		if($options['confirm']){
			$jsConfirm='confirm(\''.str_replace('\'','\\\'',str_replace('"','\"',$options['confirm'])).'\')';
			if(!isset($options['onclick'])) $options['onclick']='return '.$jsConfirm;
			else $options['onclick']='if(!'.$jsConfirm.')return false;'.$options['onclick'];
		}
		
		if(isset($options['current']) && $options['current']!==false){
			if($options['current']===1) $current=true;
			elseif($options['current'] && $url!==BASE_URL/*#if DEV */.CRoute::$_prefix/*#/if*/.'/') $current=$url==substr(BASE_URL/*#if DEV */.CRoute::$_prefix/*#/if*/.CRoute::getAll(),0,strlen($url));
			else $current=($url==(BASE_URL/*#if DEV */.CRoute::$_prefix/*#/if*/.CRoute::getAll()));
			//debugVar($url,$current);
			if($current){
				if(isset($options['class'])) $options['class'].=' current';
				else $options['class']='current';
			}
			
		}
		
		unset($options['escape'],$options['confirm'],$options['current'],$options['entry'],$options['fullUrl'],$options['cache'],$options['https']);
		
		$options['href']=$url;
		return self::tag('a',$options,$title,$escape);
	}
	/** @deprecated */
	public static function linkHtml($title,$url,$options=array()){
		$options['escape']=false;
		return self::link($title,$url,$options);
	}
	
	/** @deprecated */
	public static function cutLink($maxSize,$title,$url=false,$options=array()){
		if($url===false) $url=$title;
		if(($l=strlen($title)) > $maxSize){
			if(!isset($options['title'])) $options['title']=$title;
			$title=substr($title,0,$halfSize=floor(min($l,$maxSize-3)/2)).'...'.substr($title,$l-$halfSize);
		}
		return self::link($title,$url,$options);
	}
	
	/**
	 * @param string
	 * @param array atributes
	 * @return string
	 */
	public static function img($url,$options=array()){
		if(!isset($options['alt'])) $options['alt']='';
		$options['src']=$url[0]==='/'?self::staticUrl($url,'img'):self::url($url);
		return self::tag('img',$options);
	}
	
	/** @deprecated */
	public static function imgLink($img,$url,$optionsImg=array(),$optionsLink=array()){
		$optionsLink['escape']=false;
		if(!isset($optionsLink['class'])) $optionsLink['class']='img';
		return self::link(self::img($img,$optionsImg),$url,$optionsLink);
	}
	
	/** @deprecated */
	public static function iconLink($icon,$text,$url,$optionsLink=array()){
		return self::iconLinkHtml($icon,h($text),$url,$optionsLink);
	}
	/** @deprecated */
	public static function iconLinkHtml($icon,$html,$url,$optionsLink=array()){
		$optionsLink['escape']=false;
		if(!isset($optionsLink['class'])) $optionsLink['class']='aicon';
		return self::link('<span class="icon '.h($icon).'"></span>'.$html,$url,$optionsLink);
	}
	/** @deprecated */
	public static function iconBlockLink($icon,$text,$url,$optionsLink=array()){
		$optionsLink['escape']=false;
		if(!isset($optionsLink['class'])) $optionsLink['class']='aicon';
		return self::link('<div class="iconWrap"><span class="icon '.h($icon).'"></span><div>'.h($text).'</div></div>',$url,$optionsLink);
	}
	
	/** @deprecated */
	public static function iconAction($icon,$url,$optionsLink=array()){
		$optionsLink['class']='action icon '.$icon.(isset($optionsLink['class'])?' '.$optionsLink['class']:'');
		return self::link('',$url,$optionsLink);
	}
	
	/**
	 * Return a select without a form
	 * 
	 * @param array
	 * @param array
	 * @param array
	 */
	public static function select($list,$options,$attributes=null){
		$selectedText=null;
		if($options===null){ $selected=null; $options=array(); }
		elseif(is_string($options)){ $selected=$options; $options=array(); }
		else{ $selected=isset($options['selected'])?$options['selected']:null; unset($options['selected']);
			if(isset($options['selectedText'])) $selectedText=$options['selectedText']; unset($options['selectedText']);
		}
		
		if(isset($options['empty'])){ $empty=$options['empty']; unset($options['empty']); }
		else $empty=null;
		
		$res='';
		if($empty !== null){
			$optionAttributes=array('value'=>'');
			if($selected==='') $optionAttributes['selected']=true;
			$res.=HHtml::tag('option',$optionAttributes,$empty);
		}
		if(!empty($list)){
			if(is_object(current($list))){
				foreach($list as $model)
					$res.=HHtml::_option($model->id(),$model->name(),$selected,$selectedText);
			}else{
				foreach($list as $key=>$value)
					$res.=HHtml::_option($key,$value,$selected,$selectedText);
			}
		}
		return HHtml::tag('select',$options,$res,false);
	}
	
	/**
	 * returns an option tag
	 * 
	 * @param string
	 * @param string
	 * @param mixed
	 * @param string|null
	 * @return string
	 */
	public static function _option($value,$name,$selected,$selectedText=null){
		$attributes=array('value'=>$value);
		if($selected !== null){
			if($value==$selected && $selected!=='') $attributes['selected']=true;
		}elseif($selectedText !== null){
			if($name==$selectedText) $attributes['selected']=true;
		}
		
		return self::tag('option',$attributes,$name);
	}
	
	/**
	 * Resolve an url from a route or return if already is a full url
	 * 
	 * @param string|array a full url (http://springbok-framework.com), a string using the default route ('/myController/myAction/1/2'), an array with the route inside (['/my/route/:arg',$arg])
	 * @param string|null
	 * @param string|null
	 * @param bool
	 * @param bool
	 * @param bool|null
	 */
	public static function url($url=null,$entry=null,$full=null,$escape=false,$cache=false,$https=null){
		/*#if DEV */ if($entry===false || $entry===true) throw new Exception('Entry param cannot be false or true'); /*#/if*/
		$before='';
		if($entry===null){
			$entry=Springbok::$scriptname;
			if($full===true){
				if($cache===false) $full=App::siteUrl($entry,$https);
				else{
					$before='<?php echo App::siteUrl(Springbok::$scriptname,'.UPhp::exportCode($https).') ?>';
					$full=false;
				}
			}
		}elseif($cache){
			$before='<?php '.($full===true?'':'if("'.$entry.'"!==Springbok::$scriptname)').' echo App::siteUrl("'.$entry.'",'.UPhp::exportCode($https).') ?>';
		}elseif(($entry!==Springbok::$scriptname && $full===null) || $full===true) $full=App::siteUrl($entry,$https);
		/*#if DEV */if(is_string($full) && rtrim($full,'/')!==$full) throw new Exception('Please remove the "/" at the end of "'.$full.'"'); /*#/if*/
		if(is_array($url)){
			$url=(!$full?'':($full===true?FULL_BASE_URL:$full)).BASE_URL.CRoute::getArrayLink($entry,$url);
			$escape=false;
		}else{
			if(empty($url) || $url==='/') $url=($full===false?'':($full===true?FULL_BASE_URL:$full)).BASE_URL/*#if DEV */.CRoute::$_prefix/*#/if*/.'/';
			else{
				if(strpos($url,'://')>0) return $url;
				if(substr($url,0,2)==='\/') $url=($full===false?'':($full===true?FULL_BASE_URL:$full)).substr($url,1);
				elseif($url[0]==='/'){$url=substr($url,1); $url=($full===false?'':($full===true?FULL_BASE_URL:$full)).BASE_URL.CRoute::getStringLink($entry,$url);}
			}
		}
		return $escape?h($url,false):$url;
	}

	/**
	 * Return an escaped route
	 * 
	 * @param string
	 * @param string|null
	 * @param string|null
	 * @param bool
	 * @param bool|null
	 * @uses HHtml::url
	 */
	public static function urlEscape($url=null,$entry=null,$full=null,$cache=false,$https=null){
		return self::url($url,$entry,$full,true,$cache,$https);
	}
	
	/**
	 * Return a static url
	 * 
	 * A Static Url doesn't uses route, but the web folder
	 * 
	 * @param string
	 * @param string|false
	 * @param bool
	 * @return string
	 */
	public static function staticUrl($url=null,$folder=false,$escape=true){
		if(empty($url)) $url=WEB_URL.($folder?$folder.'/':'');
		if(strpos('://',$url)) return $url;
		if(substr($url,0,2)==='\/') $url=substr($url,1);
		elseif($url[0]==='/') $url=WEB_URL.($folder?$folder.'/':'').substr($url,1);
		return $escape?h($url):$url;
	}
	
	/**
	 * @internal
	 * @param array
	 * @param bool
	 * @return void
	 */
	public static function _echoAttributes($attributes,$escape=true){
		foreach($attributes as $k=>&$v) echo ' '.$k.'="'.($v===true?$k:($escape?h($v):$v)).'"';
	}
	
	/**
	 * @internal
	 * @param array
	 * @param bool
	 * @return string
	 */
	public static function _attributes($attributes,$escape=true){
		$res='';
		foreach($attributes as $k=>&$v){
			$res.=' '.$k;
			if($v!==null) $res.='="'.($v===true?$k:($escape?h($v):$v)).'"';
		}
		return $res;
	}
	
	/**
	 * @deprecated use HElement::create() now
	 */
	public static function tag($tagName,$attributes,$content=null,$contentEscape=true){
		return '<'.$tagName.(!empty($attributes)?self::_attributes($attributes):'')
			.($content===null?'/>':('>'.($contentEscape?h($content):$content).'</'.$tagName.'>'));
	}
	
	/** @deprecated */
	public static function openTag($tagName,$attributes){
		return '<'.$tagName.(!empty($attributes)?self::_attributes($attributes):'').'>';
	}
	
	/** @deprecated */
	public static function closeTag($tagName){
		return '</'.$tagName.'>';
	}
	
	
	/**
	 * Returns the Powered by link
	 * 
	 * @return string
	 */
	public static function powered(){
		return _tC('Powered by').' '.HHtml::link('Springbok Framework','http://springbok-framework.com',array('target'=>'_blank')).'.';
	}
	
	/**
	 * Return the enhance formatted date
	 * 
	 * @return string
	 */
	public static function enhanceDate(){
		return App::getLocale()->formatDateTime(APP_DATE);
	}
	
	/* ----- ----- ----- ----- ----- ----- ----- ----- ----- ----- ----- ----- ----- ----- ----- ----- ----- ----- ----- ----- ----- ----- ----- ----- */
	
	/* OLD!
	public static function ajaxCRDSelectFiltrable($url,$items,$added,$options=array()){
		$divid=uniqid('ajaxCRDSelectFiltrable_');
		$options+=array('selectAttributes'=>array(),'ulAttributes'=>array('class'=>'compact'));
		$res=HHtml::openTag('select',$options['selectAttributes'])
			.HHtml::tag('option',array('value'=>'','selected'=>true),'');
		$addedItems=array();
		foreach($items as $id=>&$name){
			$attributes=array('value'=>$id);
			if(in_array($id,$added)){
				$addedItems[$id]=$name;
				//$attributes['class']='hidden';
				continue;
			}
			
			$res.=HHtml::tag('option',$attributes,$name);
		}
		$res.=HHtml::closeTag('select').' '.self::iconAction('add vaMid','#');
		$res.=HHtml::openTag('ul',$options['ulAttributes']);
		foreach($addedItems as $id=>&$name){
			$res.=HHtml::tag('li',array('rel'=>$id),HHtml::tag('span',array(),$name,true).' '.self::iconAction('delete','#'),false);
		}
		$res.='</ul>';
		unset($options['selectAttributes']);
		return '<div id="'.$divid.'">'.$res.'</div>'.HHtml::jsInline('S.ready(function(){$(\'#'.$divid.'\').ajaxCRDSelectFiltrable('.json_encode(HHtml::url($url)).(!empty($options)?','.json_encode($options):'').')})');
	}

	public static function ajaxCRDInputAutocomplete($url,$items,$options=array()){
		$divid=uniqid('ajaxCRDInputAutocomplete_');
		$options+=array('inputAttributes'=>array(),'ulAttributes'=>array('class'=>'compact'),'modelFunctionName'=>'name','escape'=>true,'js'=>'{}');
		$res=HHtml::tag('input',$options['inputAttributes']).' '.self::iconAction('add vaMid','#');
		if(is_object(current($items))){
			$list=$items; $items=array();
			foreach($list as $model) $items[$model->id()]=array($model->{$options['modelFunctionName']}(),'editable'=>$model->isEditable(),'deletable'=>$model->isDeletable());
		}else foreach($items as $id=>&$name) $name=array($name,'editable'=>true,'deletable'=>true);
		$res.=HHtml::openTag('ul',$options['ulAttributes']);
		$actions= isset($options['actions']) && !empty($options['actions']) ? $options['actions'] : array();
		$actions[]='delete';
		$actions=array_combine($actions,$actions);
		foreach($actions as &$action) $action=' '.self::iconAction($action,'#');
		foreach($items as $id=>$item){
			$itemActions=$actions;
			if(!$item['editable']) unset($itemActions['edit']);
			if(!$item['deletable']) unset($itemActions['delete']);
			$res.=HHtml::tag('li',array('rel'=>$id),HHtml::tag('span',array(),$item[0],$options['escape']).implode('',$itemActions),false);
		}
		$res.='</ul>';
		//unset($options['inputAttributes'],$options['ulAttributes'],$options['modelFunctionName'],$options['escape']);
		if(isset($options['allowNew'])) $options['js']='{allowNew:1}';
		HHtml::jsReady((isset($options['inputAttributes']['placeholder'])?'$(\'#'.$divid.' input\').defaultInput();':'')
			.'$(\'#'.$divid.'\').ajaxCRDInputAutocomplete('.json_encode(HHtml::url($url)).(!empty($options)?','.$options['js']:'').')');
		return '<div id="'.$divid.'">'.$res.'</div>';
	}*/
	
	/**
	 * Ajax CRUD Select Filrable
	 * 
	 * @param string
	 * @param array
	 * @param array
	 * @param array
	 * @return string
	 */
	public static function ajaxCRDSelectFiltrable($url,$items,$added,$options=array()){
		$divid=uniqid('ajaxCRDSelectFiltrable_');
		if(!isset($options['selectAttributes'])) $options['selectAttributes']=array();
		$options=self::_ajaxInitOptions($options);
		$res=HHtml::openTag('select',$options['selectAttributes'])
			.HHtml::tag('option',array('value'=>'','selected'=>true),'');
		$addedItems=array();
		foreach($items as $id=>&$name){
			$attributes=array('value'=>$id);
			if(in_array($id,$added)){
				$addedItems[]=array($id,$name,'editable'=>isset($options['notEditable'])?!in_array($id,$options['notEditable']):true,
							'deletable'=>isset($options['notDeletable'])?!in_array($id,$options['notDeletable']):true);

				//$attributes['class']='hidden';
				continue;
			}

			$res.=HHtml::tag('option',$attributes,$name);
		}
		$res.=HHtml::closeTag('select').' '.self::iconAction('add vaMid','#');
		$res.=HHtml::openTag('ul',$options['ulAttributes']);
		foreach($addedItems as $id=>&$item) $res.=self::_ajaxCreateLi($item,$options);
		$res.='</ul>';
		unset($options['selectAttributes'],$options['actions']);
		return '<div id="'.$divid.'">'.$res.'</div>'.HHtml::jsInline('S.ready(function(){$(\'#'.$divid.'\').ajaxCRDSelectFiltrable('.json_encode(HHtml::url($url))
						.(!empty($options['js'])?','.$options['js']:(!empty($options)?','.json_encode($options):'')).')})');
	}
	
	/**
	 * Ajax CRUD Input Autocomplete
	 * 
	 * @param string
	 * @param array
	 * @param array
	 * @return string
	 */
	public static function ajaxCRDInputAutocomplete($url,$items,$options=array()){
		$divid=uniqid('ajaxCRDInputAutocomplete_');
		$options+=array('inputAttributes'=>array(),'js'=>'{}');
		$options=self::_ajaxInitOptions($options);
		$res=HHtml::tag('input',$options['inputAttributes']).' '.self::iconAction('add vaMid','#');
		$res.=HHtml::openTag('ul',$options['ulAttributes']);

		if(is_object(current($items))){
			$list=$items; $items=array();
			foreach($list as $model)
				$res.=self::_ajaxCreateLi(self::_ajaxCreateItem($model,$options),$options);
		}else{
			foreach($items as $id=>&$name)
				$res.=self::_ajaxCreateLi(array($id,$name,'editable'=>isset($options['notEditable'])?!in_array($id,$options['notEditable']):true,
							'deletable'=>isset($options['notDeletable'])?!in_array($id,$options['notDeletable']):true),$options);
		}

		$res.='</ul>';
		//unset($options['inputAttributes'],$options['ulAttributes'],$options['modelFunctionName'],$options['escape']);
		if(isset($options['allowNew'])) $options['js']='{allowNew:1}';
		HHtml::jsReady((isset($options['inputAttributes']['placeholder'])?'$(\'#'.$divid.' input\').defaultInput();':'')
			.'$(\'#'.$divid.'\').ajaxCRDInputAutocomplete('.json_encode(HHtml::url($url)).(!empty($options['js'])?','.$options['js']:'').')');
		return '<div id="'.$divid.'">'.$res.'</div>';
	}
	
	/**
	 * @internal
	 * @param array
	 * @return array
	 */
	public static function _ajaxInitOptions($options){
		if(!isset($options['ulAttributes'])) $options['ulAttributes']=array('class'=>'compact');
		if(!isset($options['modelFunctionName'])) $options['modelFunctionName']='name';
		if(!isset($options['escape'])) $options['escape']=true;
		$actions= isset($options['actions']) && !empty($options['actions']) ? $options['actions'] : array();
		$actions[]='delete';
		$actions=array_combine($actions,$actions);
		foreach($actions as &$action) $action=' '.self::iconAction($action,'#');
		$options['actions']=$actions;
		return $options;
	}
	
	/**
	 * @internal
	 * @param SModel
	 * @param array
	 * @return array
	 */
	public static function _ajaxCreateItem($model,$options){
		return array($model->id(),$model->{$options['modelFunctionName']}(),
					'editable'=>$model->isEditable(),'deletable'=>$model->isDeletable());
	}
	
	/**
	 * @internal
	 * @param array
	 * @param array
	 * @return string
	 */
	public static function _ajaxCreateLi($item,$options){
		$itemActions=$options['actions'];
		if(isset($itemActions['edit']) && !$item['editable']) unset($itemActions['edit']);
		if(!$item['deletable']) unset($itemActions['delete']);
		return HHtml::tag('li',array('rel'=>$item[0]),HHtml::tag('span',array(),$item[1],$options['escape']).implode('',$itemActions),false);
	}
}