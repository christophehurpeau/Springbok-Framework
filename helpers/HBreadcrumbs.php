<?php
/**
 * Help generate a Breadcrumb in a site
 * 
 * In the layout :
 * <code>
 * <?php HBreadcrumbs::display(_tC('Home'),$layout_title) ?>
 * </code>
 * 
 * In a view / a controller / a view element :
 * <code>
 * <?php HBreadcrumbs::add('Title displayed in the breadcrumb',array('/a/:route',$arg)) ?>
 * </code>
 * 
 * @see HHtml::url()
 */
class HBreadcrumbs{
	public static $tagName='div';
	private static $_links=array(),$_lastTitle;
	
	/**
	 * Set a list of links in the breadcrumbs.
	 * 
	 * <code>
	 * <?php HBreadcrumbs::set(array('Pages'=>'/pages')); ?>
	 * </code>
	 * 
	 * @param array
	 * @param string|null
	 * @return void
	 * 
	 * @see add
	 * @see setLast
	 */
	public static function set($links,$lastTitle=null){
		self::$_links=array();
		foreach($links as $key=>$link) self::$_links[]=array($key,$link);
		self::$_lastTitle=$lastTitle;
		/*#if DEV */ return '<div style="color:red;font-size:12pt">Please do not echo HBreadcrumbs::set()</div>'; /*#/if*/
	}
	
	/**
	 * Add a title/link in the end of the breadcrumbs (but before the last title)
	 * 
	 * @param string
	 * @param string|array
	 * @return void
	 */
	public static function add($titleLink,$link){
		self::$_links[]=array($titleLink,$link);
		/*#if DEV */ return '<div style="color:red;font-size:12pt">Please do not echo HBreadcrumbs::add()</div>'; /*#/if*/
	}
	
	/**
	 * Set the last title in the breadcrumbs
	 * 
	 * @param string
	 * @return void
	 */
	public static function setLast($lastTitle){
		self::$_lastTitle=$lastTitle;
		/*#if DEV */ return '<div style="color:red;font-size:12pt">Please do not echo HBreadcrumbs::setLast()</div>'; /*#/if*/
	}
	
	/**
	 * Display the breadcrumbs
	 * 
	 * @param string|array
	 * @param string
	 * @param array
	 * @return void
	 */
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
		/*#if DEV */ return '<div style="color:red;font-size:12pt">Please do not echo HBreadcrumbs::display()</div>'; /*#/if*/
	}
	
	/**
	 * Internal function : display a link from the breadcrumbs
	 */
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
	
	/**
	 * @internal
	 * 
	 * Transform the breadcrumbs in JSON to export it when using ajax content loading
	 * 
	 * @param string
	 * @return string
	 */
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
	