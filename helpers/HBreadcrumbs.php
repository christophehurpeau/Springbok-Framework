<?php
class HBreadcrumbs{
	public static $tagName='div';
	private static $_links=array(),$_lastTitle;
	
	public static function set($links,$lastTitle=null){
		self::$_links=array();
		foreach($links as $key=>$link) self::$_links[]=array($key,$link);
		self::$_lastTitle=$lastTitle;
	}
	
	public static function add($titleLink,$link){
		self::$_links[]=array($titleLink,$link);
	}
	
	public static function setLast($lastTitle){
		self::$_lastTitle=$lastTitle;
	}
	
	public static function display($homeLink,$lastTitle,$options=array()){
		if(!isset($options['class'])) $options['class']='breadcrumbs';
		if(empty(self::$_links) && (self::$_lastTitle===false || empty($lastTitle))) return;
		if(isset($options['spanAttributes'])) $spanAttributes=$options['spanAttributes'];
		else $spanAttributes=array();
		if(isset($options['linkoptions'])) $linkoptions=$options['linkoptions'];
		else $linkoptions=array();
		$separator=isset($options['separator'])?$options['separator']:' &raquo; ';
		$linkoptions['itemprop']='url';
		$linkoptions['escape']=false;
		
		$homelinkoptions=isset($options['homelinkoptions']) ? $options['homelinkoptions']+$linkoptions : $linkoptions;
		
		unset($options['spanAttributes'],$options['linkoptions'],$options['homelinkoptions'],$options['separator']);
		$attributes=array('id'=>'breadcrumbs');
		
		echo HHtml::openTag(self::$tagName,$attributes);
		echo '<span itemscope itemtype="http://data-vocabulary.org/Breadcrumb">'.(is_array($homeLink) ?  self::link($homeLink[0],$homeLink[1],$homelinkoptions,$spanAttributes) : self::link($homeLink,'/',$homelinkoptions,$spanAttributes)).'</span>';
		foreach(self::$_links as $value)
			echo $separator.'<span itemscope itemtype="http://data-vocabulary.org/Breadcrumb">'
				.self::link($value[0],$value[1],$linkoptions,$spanAttributes).'</span>';
		{
			$spanAttributes['class']='last';
			if(self::$_lastTitle!==null) echo $separator.'<span class="last" itemscope itemtype="http://data-vocabulary.org/Breadcrumb"><span itemprop="title">'.h(self::$_lastTitle).'</span></span>';
			elseif(!empty($lastTitle)) echo $separator.'<span class="last" itemscope itemtype="http://data-vocabulary.org/Breadcrumb"><span itemprop="title">'.h($lastTitle).'</span></span>';
		}
		echo HHtml::closeTag(self::$tagName);
	}
	
	public static function link($title,$value,$linkoptions,$spanAttributes){
		if(is_int($title)){
			return HHtml::tag('span',$spanAttributes,$value);
		}else{
			if(!is_array($value)) $url=$value;
			else{
				$url=$value[0];
				if(!empty($value['options'])) $linkoptions=$value['options']+$linkoptions;
			}
			return HHtml::link('<span itemprop="title">'.h($title).'</span>',$url,$linkoptions);
			//echo HHtml::link($title,$url,$linkoptions);
		}
	}
	
	
	public static function toJs($lastTitle){
		if(self::$_lastTitle!==null) self::$_links[]=array(0,self::$_lastTitle);
		elseif(!empty($lastTitle)) self::$_links[]=array(0,$lastTitle);
		$js='[';
		foreach(self::$_links as $value){
			$title=$value[0]; $value=$value[1];
			if(is_int($title)){
				$js.=json_encode($value).',';
			}else{
				if(!is_array($value)) $value=array('url'=>HHtml::url($value));
				else{ $value['url']=HHtml::url($value[0]); unset($value[0]); }
				$value['_title']=$title;
				$js.=json_encode($value).',';
			}
		};
		return substr($js,0,-1).']';
	}
}
	