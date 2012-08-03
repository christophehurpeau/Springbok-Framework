<?php
class HHtml{
	public static function doctype(){
		return CHttpRequest::isIElt8() ? '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">' : '<!DOCTYPE html>';
	}
	
	public static function linkRSS($title,$url){
		echo '<link rel="alternate" type="application/rss+xml" href="'.self::url($url).'" title="'.$title.'"/>';
	}
	public static function linkAtom($title,$url){
		echo '<link rel="alternate" type="application/atom+xml" href="'.self::url($url).'" title="'.$title.'"/>';
	}
	
	public static function metaCharset($encoding='utf-8'){
		return '<meta charset="'.$encoding.'"/><meta http-equiv="Content-Type" content="text/html; charset='.$encoding.'"/>';
	}
	public static function metaRobots($content){
		echo '<meta name="robots" content="'.$content.'"/>';
	}
	public static function metaLanguage($lang=null){
		if($lang===null) $lang=CLang::get();
		return '<meta name="language" content="'.$lang.'"/><meta http-equiv="content-language" content="'.$lang.'"/>';
	}
	
	public static function cssLink($url='/main',$media=false){
		echo '<link rel="stylesheet" type="text/css" href="'.self::staticUrl(strpos($url,'?')?$url:($url.'.css'),'css').'"'.($media?' media="'.$media.'"':'').'/>';
	}
	
	public static function favicon($imgUrl='favicon.png'){
		$href=self::staticUrl('/'.$imgUrl,'img');
		return '<link rel="icon" type="image/vnd.microsoft.icon" href="'.$href.'"/>'
			.'<link rel="shortcut icon" type="image/x-icon" href="'.$href.'"/>';
	}
	
	private static $_CSS;
	public static function cssLinks(){
		if(self::$_CSS) foreach(self::$_CSS as $url=>$media)
			self::cssLink($url,$media);
	}
	public static function addCSS($url,$media=false){
		self::$_CSS[$url]=$media;
	}
	public static function cssInline($content,$attributes=array()){
		return '<style type="text/css"'.self::_echoAttributes($attributes).'>'.$content.'</style>';
	}
	public static function cssInlineStart(){
		ob_start();
	}
	public static function cssInlineEnd(){
		echo self::cssInline(ob_get_clean());
	}

	public static function jsInline($content){
		return '<script type="text/javascript">//<![CDATA[
'.trim($content).'
//]]>
</script>';
	}
	public static function jsLink($url='/global'){
		echo '<script type="text/javascript" src="'.self::staticUrl($url.'.js','js').'"></script>';
	}
	private static $_JS;
	public static function jsLinks(){
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
	public static function addJS($url){
		self::$_JS['all'][]=$url;
	}
	public static function jsI18n(){
		return self::jsLink('/i18n-'.CLang::get());
	}
	
	public static function jsInlineStart(){
		ob_start();
	}
	public static function jsInlineEnd(){
		return self::jsInline(ob_get_clean());
	}
	
	public static function jsEscape($string){
		return json_encode($string/*,JSON_UNESCAPED_UNICODE*/);
	}
	
	public static function addJS4IE($url,$for){
		self::$_JS['ie'][$for][]=$url;
	}
	
	
	public static function ganalytics($code,$trackPageLoadTime=false,$https=false){
		return '<script type="text/javascript">//<![CDATA[
var _gaq=[["_setAccount","'.$code.'"],[\'_trackPageview\']'.($trackPageLoadTime?",['_trackPageLoadTime']":'').'];
(function(d,t){
var g=d.createElement(t),s=d.getElementsByTagName(t)[0];
g.type=\'text/javascript\';g.async=1;g.src=\''.($https?'https://ssl':'http://www').'.google-analytics.com/ga.js\';
s.parentNode.insertBefore(g,s);
})(document,\'script\');
//]]>
</script>';
	}
	
	public static function ganalyticsMultiTracker($codes,$domainName,$trackPageLoadTime=false,$https=false){
		$gaq= '<script type="text/javascript">//<![CDATA[
var _gaq = _gaq || [];_gaq.push([\'_setDomainName\', \''.$domainName.'\']';
		foreach($codes as $key => $value) 
			$gaq.= ',[\''.$key.'_setAccount\',\''.$value.'\'],[\''.$key.'_trackPageview\']'.($trackPageLoadTime?",['".$key."_trackPageLoadTime']":'');
		return $gaq.');
(function(d,t){
var g=d.createElement(t),s=d.getElementsByTagName(t)[0];
g.type=\'text/javascript\';g.async=1;g.src=\''.($https?'https://ssl':'http://www').'.google-analytics.com/ga.js\';
s.parentNode.insertBefore(g,s);
})(document,\'script\');
//]]></script>';
	}
	
	public static function link($title,$url=false,$options=array()){
		$options=$options+array('confirm'=>false,'entry'=>null,'fullUrl'=>null);
		if($url){
			if($url!=='#' && $url[0]!=='?' && (is_array($url) || (substr($url,0,11)!=='javascript:' && substr($url,0,7)!=='mailto:'))) $url=self::url($url,$options['entry'],$options['fullUrl']);
		}else $title=$url=self::url($title,$options['entry'],$options['fullUrl']);
		
		
		if(isset($options['escape'])) $escape=$options['escape'];
		else $escape=true;

		if($options['confirm']){
			$jsConfirm='confirm(\''.str_replace('\'','\\\'',str_replace('"','\"',$options['confirm'])).'\')';
			if(!isset($options['onclick'])) $options['onclick']='return '.$jsConfirm;
			else $options['onclick']='if(!'.$jsConfirm.')return false;'.$options['onclick'];
		}
		
		if(isset($options['current']) && $options['current']!==false){
			if($options['current']===1) $current=true;
			elseif($options['current'] && $url!==BASE_URL/* DEV */.CRoute::$_prefix/* /DEV */.'/') $current=$url==substr(BASE_URL/* DEV */.CRoute::$_prefix/* /DEV */.CRoute::getAll(),0,strlen($url));
			else $current=($url==(BASE_URL/* DEV */.CRoute::$_prefix/* /DEV */.CRoute::getAll()));
			//debugVar($url,$current);
			if($current){
				if(isset($options['class'])) $options['class'].=' current';
				else $options['class']='current';
			}
			
		}
		
		unset($options['escape'],$options['confirm'],$options['current'],$options['entry'],$options['fullUrl']);
		
		$options['href']=$url;
		return self::tag('a',$options,$title,$escape);
	}
	public static function linkHtml($title,$url,$options=array()){
		$options['escape']=false;
		return self::link($title,$url,$options);
	}

	public static function cutLink($maxSize,$title,$url=false,$options=array()){
		if($url===false) $url=$title;
		if(($l=strlen($title)) > $maxSize){
			if(!isset($options['title'])) $options['title']=$title;
			$title=substr($title,0,$halfSize=floor(min($l,$maxSize-3)/2)).'...'.substr($title,$l-$halfSize);
		}
		return self::link($title,$url,$options);
	}
	
	public static function img($url,$options=array()){
		if(!isset($options['alt'])) $options['alt']='';
		$options['src']=$url[0]==='/'?self::staticUrl($url,'img'):self::url($url);
		return self::tag('img',$options);
	}
	
	public static function imgLink($img,$url,$optionsImg=array(),$optionsLink=array()){
		$optionsLink['escape']=false;
		if(!isset($optionsLink['class'])) $optionsLink['class']='img';
		return self::link(self::img($img,$optionsImg),$url,$optionsLink);
	}

	public static function iconLink($icon,$text,$url,$optionsLink=array()){
		$optionsLink['escape']=false;
		if(!isset($optionsLink['class'])) $optionsLink['class']='aicon';
		return self::link('<span class="icon '.h($icon).'"></span>'.h($text),$url,$optionsLink);
	}
	public static function iconBlockLink($icon,$text,$url,$optionsLink=array()){
		$optionsLink['escape']=false;
		if(!isset($optionsLink['class'])) $optionsLink['class']='aicon';
		return self::link('<div class="iconWrap"><span class="icon '.h($icon).'"></span><div>'.h($text).'</div></div>',$url,$optionsLink);
	}
	
	public static function iconAction($icon,$url,$optionsLink=array()){
		$optionsLink['class']='action icon '.$icon.(isset($optionsLink['class'])?' '.$optionsLink['class']:'');
		return self::link('',$url,$optionsLink);
	}
	
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
					$res.=HHtml::_option($model->_getPkValue(),$model->name(),$selected,$selectedText);
			}else{
				foreach($list as $key=>$value)
					$res.=HHtml::_option($key,$value,$selected,$selectedText);
			}
		}
		return HHtml::tag('select',$options,$res,false);
	}
	
	public static function _option($value,$name,&$selected,$selectedText=null){
		$attributes=array('value'=>$value);
		if($selected !== null){
			if($value==$selected && $selected!=='') $attributes['selected']=true;
		}elseif($selectedText !== null){
			if($name==$selectedText) $attributes['selected']=true;
		}
		
		return self::tag('option',$attributes,$name);
	}
	
	public static function url($url=null,$entry=null,$full=null,$escape=false){
		/* DEV */ if($entry===false || $entry===true) throw new Exception('Entry param cannot be false or true'); /* /DEV */
		if($entry===null){
			$entry=Springbok::$scriptname;
			if($full===true) $full=Config::$siteUrl[$entry];
		}elseif(($entry!==Springbok::$scriptname && $full===null) || $full===true) $full=Config::$siteUrl[$entry];
		if(is_array($url)){
			$url=(!$full?'':($full===true?FULL_BASE_URL:$full)).BASE_URL.CRoute::getArrayLink($entry,$url);
			$escape=false;
		}else{
			if(empty($url) || $url==='/') $url=($full===false?'':($full===true?FULL_BASE_URL:$full)).BASE_URL/* DEV */.CRoute::$_prefix/* /DEV */.'/';
			else{
				if(strpos($url,'://')>0) return $url;
				if(substr($url,0,2)==='\/') $url=substr($url,1);
				elseif($url[0]==='/'){$url=substr($url,1); $url=($full===false?'':($full===true?FULL_BASE_URL:$full)).BASE_URL.CRoute::getStringLink($entry,$url);}
			}
		}
		return $escape?h($url,false):$url;
	}
	public static function urlEscape($url=null,$entry=null,$full=null){
		return self::url($url,$entry,$full,true);
	}
	
	public static function staticUrl($url=null,$folder=false,$escape=true){
		if(empty($url)) $url=STATIC_URL.($folder?$folder.'/':'');
		if(strpos('://',$url)) return $url;
		if(substr($url,0,2)==='\/') $url=substr($url,1);
		elseif($url[0]==='/') $url=STATIC_URL.($folder?$folder.'/':'').substr($url,1);
		return $escape?h($url):$url;
	}
	
	public static function _echoAttributes($attributes,$escape=true){
		foreach($attributes as $k=>&$v) echo ' '.$k.'="'.($v===true?$k:($escape?h($v):$v)).'"';
	}
	public static function &_attributes($attributes,$escape=true){
		$res='';
		foreach($attributes as $k=>&$v){
			$res.=' '.$k;
			if($v!==null) $res.='="'.($v===true?$k:($escape?h($v):$v)).'"';
		}
		return $res;
	}
	
	public static function tag($tagName,$attributes,$content=NULL,$contentEscape=true){
		return '<'.$tagName.(!empty($attributes)?self::_attributes($attributes):'')
			.($content===NULL?'/>':('>'.($contentEscape?h($content):$content).'</'.$tagName.'>'));
	}
	
	public static function openTag($tagName,$attributes){
		return '<'.$tagName.(!empty($attributes)?self::_attributes($attributes):'').'>';
	}
	public static function closeTag($tagName){
		return '</'.$tagName.'>';
	}
	
	
	public static function powered(){
		return _tC('Powered by').' '.HHtml::link('Springbok Framework','http://www.springbok-framework.com',array('target'=>'_blank')).'.';
	}
	
	public static function enhanceDate(){
		return App::getLocale()->formatDateTime(APP_DATE);
	}
	
	/* ----- ----- ----- ----- ----- ----- ----- ----- ----- ----- ----- ----- ----- ----- ----- ----- ----- ----- ----- ----- ----- ----- ----- ----- */
	
	public static function ajaxCRDSelectFiltrable($url,$items,$added,$options=array()){
		$divid=uniqid('ajaxCRDSelectFiltrable_');
		$options+=array('selectAttributes'=>array());
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
		$res.='<ul class="compact">';
		foreach($addedItems as $id=>&$name){
			$res.=HHtml::tag('li',array('rel'=>$id),HHtml::tag('span',array(),$name,true).' '.self::iconAction('delete','#'),false);
		}
		$res.='</ul>';
		unset($options['selectAttributes']);
		return '<div id="'.$divid.'">'.$res.'</div>'.HHtml::jsInline('S.ready(function(){$(\'#'.$divid.'\').ajaxCRDSelectFiltrable('.json_encode(HHtml::url($url)).(!empty($options)?','.json_encode($options):'').')})');
	}

	public static function ajaxCRDInputAutocomplete($url,$items,$options=array()){
		$divid=uniqid('ajaxCRDInputAutocomplete_');
		$options+=array('inputAttributes'=>array());
		$res=HHtml::tag('input',$options['inputAttributes']).' '.self::iconAction('add vaMid','#');
		if(is_object(current($items))){
			$list=$items; $items=array();
			foreach($list as $model) $items[$model->_getPkValue()]=$model->name();
		}	
		$res.='<ul class="compact">';
		foreach($items as $id=>&$name)
			$res.=HHtml::tag('li',array('rel'=>$id),HHtml::tag('span',array(),$name,true).' '.self::iconAction('delete','#'),false);
		$res.='</ul>';
		unset($options['inputAttributes']);
		return '<div id="'.$divid.'">'.$res.'</div>'.HHtml::jsInline('S.ready(function(){$(\'#'.$divid.'\').ajaxCRDInputAutocomplete('.json_encode(HHtml::url($url)).(!empty($options)?','.json_encode($options):'').')})');
	}
}