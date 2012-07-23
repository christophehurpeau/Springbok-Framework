<?php
class HBreadcrumbs{
	public static $tagName='div';
	private static $_links=array(),$_lastTitle;
	
	public static function set($links,$lastTitle=null){
		self::$_links=$links;
		self::$_lastTitle=$lastTitle;
	}
	
	public static function add($titleLink,$link){
		self::$_links[$titleLink]=$link;
	}
	
	public static function display($homeLink,$lastTitle,$options=array()){
		if(!isset($options['class'])) $options['class']='breadcrumbs';
		if(empty(self::$_links) && empty($lastTitle)) return;
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
		foreach(self::$_links as $title=>$value)
			echo $separator.'<span itemscope itemtype="http://data-vocabulary.org/Breadcrumb">'
				.self::link($title,$value,$linkoptions,$spanAttributes).'</span>';
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
		if(self::$_lastTitle!==null) self::$_links[]=self::$_lastTitle;
		elseif(!empty($lastTitle)) self::$_links[]=$lastTitle;
		array_walk(self::$_links,function($value,$title){
			if(!is_int($title)){
				if(!is_array($value)) $value=HHtml::url($value);
				else{ $value['url']=HHtml::url($value[0]); unset($value[0]); }
			}
		});
		return json_encode(self::$_links,JSON_FORCE_OBJECT);
	}
}
	