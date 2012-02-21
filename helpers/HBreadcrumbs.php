<?php
class HBreadcrumbs{
	public static $tagName='div';
	private static $_links,$_lastTitle;
	
	public static function set($links,$lastTitle=null){
		self::$_links=&$links;
		self::$_lastTitle=&$lastTitle;
	}
	
	public static function display($homeLink,$lastTitle,$options=array('class'=>'breadcrumbs')){
		if(empty(self::$_links) && empty($lastTitle)) return;
		if(isset($options['spanAttributes'])) $spanAttributes=&$options['spanAttributes'];
		else $spanAttributes=array();
		if(isset($options['linkoptions'])) $linkoptions=&$options['linkoptions'];
		else $linkoptions=array();
		$separator=isset($options['separator'])?$options['separator']:' &raquo; ';
		$linkoptions['itemprop']='url';
		$linkoptions['escape']=false;
		
		unset($options['spanAttributes'],$options['linkoptions'],$options['separator']);
		$attributes=array('id'=>'breadcrumbs','itemscope'=>null,'itemtype'=>'http://data-vocabulary.org/Breadcrumb');
		
		echo HHtml::openTag(self::$tagName,$attributes);
		echo is_array($homeLink) ?  self::link($homeLink[0],$homeLink[1],$linkoptions,$spanAttributes) : self::link($homeLink,'/',$linkoptions,$spanAttributes);
		foreach(self::$_links as $title=>$value)
			echo $separator.'<span itemscope itemtype="http://data-vocabulary.org/Breadcrumb">'
				.self::link($title,$value,$linkoptions,$spanAttributes);
		{
			$spanAttributes['class']='last';
			if(self::$_lastTitle!==null) echo $separator.'<span class="last" itemscope itemtype="http://data-vocabulary.org/Breadcrumb"><span itemprop="title">'.h(self::$_lastTitle).'</span></span>';
			elseif(!empty($lastTitle)) echo $separator.'<span class="last" itemscope itemtype="http://data-vocabulary.org/Breadcrumb"><span itemprop="title">'.h($lastTitle).'</span></span>';
		}
		foreach(self::$_links as $title=>$value) echo '</span>';
		echo HHtml::closeTag(self::$tagName);
	}
	
	public static function link($title,$value,$linkoptions,$spanAttributes){
		if(is_int($title)){
			return HHtml::tag('span',$spanAttributes,$value);
		}else{
			if(!is_array($value)) $url=$value;
			else{
				$url=$value['url'];
				if(!empty($value['options'])) $linkoptions=$value['options']+$linkoptions;
			}
			return HHtml::link('<span itemprop="title">'.h($title).'</span>',$url,$linkoptions);
			//echo HHtml::link($title,$url,$linkoptions);
		}
	}
	
	
	public static function toJs($lastTitle){
		if(!empty($lastTitle)) self::$_links[]=$lastTitle;
		array_walk(self::$_links,function(&$value,&$title){
			if(!is_int($title)){
				if(!is_array($value)) $value=HHtml::url($value,false,false);
				else $value['url']=HHtml::url($value['url'],false,false);
			}
		});
		return json_encode(self::$_links);
	}
}
	