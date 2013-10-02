<?php
/**
 * Menu navigation helper
 */
class HMenu{
	/**
	 * @var string
	 */
	public static $tagName='nav';
	/**
	 * @var string
	 */
	public static $separatorTop='-';
	/**
	 * @var string
	 */
	public static $separator='-----------';
	
	/**
	 * Create a top menu
	 * 
	 * @param array
	 * @param array
	 * @return string
	 */
	public static function top($links,$options=array()){
		return self::create($links,$options,'top');
	}
	
	/**
	 * Create a top menu with unescaped links content
	 * 
	 * @param array
	 * @param array
	 * @return string
	 */
	public static function topHtml($links,$options=array()){
		$options['linkoptions']['escape']=false;
		return self::create($links,$options,'top');
	}
	
	/**
	 * Create an ajax top menu
	 * 
	 * @param array
	 * @param array
	 * @return string
	 */
	public static function ajaxTop($links,$options=array()){
		return self::create($links,$options,'top ajax');
	}
	
	/**
	 * Create a left menu
	 * 
	 * @param array
	 * @param array
	 * @return string
	 */
	public static function left($links,$options=array()){
		return self::create($links,$options,'left');
	}
	
	/**
	 * Create a left menu with unescaped links content
	 * 
	 * @param array
	 * @param array
	 * @return string
	 */
	public static function leftHtml($links,$options=array()){
		$options['linkoptions']['escape']=false;
		return self::create($links,$options,'left');
	}
	
	/**
	 * Create an ul menu instead of using the standard nav tag
	 * 
	 * @param array
	 * @param array
	 * @return string
	 */
	public static function ul($links,$options=array()){
		self::$tagName='ul';
		$res=self::create($links,$options,'');
		self::$tagName='nav';
		return $res;
	}
	
	/**
	 * Create a menu
	 * 
	 * @param array
	 * @param array
	 * @param string
	 * @return string
	 */
	public static function create($links,$options=array(),$type='top'){
		$options=$options+array('lioptions'=>array(),'linkoptions'=>array(),'startsWith'=>0);
		if(!isset($options['menuAttributes']['class'])) $options['menuAttributes']['class']=$type;
		$res=HHtml::openTag(self::$tagName,$options['menuAttributes']);
		if(self::$tagName!=='ul') $res.='<ul>';
		foreach($links as $title=>$value) $res.=self::_li($type,$title,$value,$options);
		if(self::$tagName!=='ul') $res.='</ul>';
		return $res.HHtml::closeTag(self::$tagName);
	}
	
	/**
	 * @param string
	 * @param string
	 * @param string
	 * @param array
	 * @return string
	 */
	private static function _li($type,$title,$value,$options){
		if(is_int($title)){
			if($value===false) return HHtml::tag('li',array('class'=>'separator'),$type==='top'?self::$separatorTop:self::$separator);
			$title=$value['title'];
		}
		if(is_array($value) && isset($value['children'])){
			if( isset($value['visible']) && !$value['visible']) return '';
			$res='<li>'.(!empty($value['escape'])?h($title):$title).'<ul>';
			foreach($value['children'] as $childTitle=>$childValue){
				$res.=self::link($childTitle,$childValue,$options['linkoptions'],array('startsWith'=>$options['startsWith']),$options['lioptions']);
			}
			return $res.'</ul></li>';
		}else
			return self::link($title,$value,$options['linkoptions'],array('startsWith'=>$options['startsWith']),$options['lioptions']);
	}
	
	/**
	 * @param string
	 * @param string
	 * @param array
	 * @param array
	 * @param array
	 * @return string
	 */
	public static function link($title,$value,$linkoptions=array(),$options=array(),$lioptions=array()){
		if(!isset($options['startsWith'])) $options['startsWith']=false;
		$isValueArray=is_array($value);
		if($isValueArray && isset($value['lioptions'])){
			$lioptions=$value['lioptions']+$lioptions;
			unset($value['lioptions']);
		}
		if($isValueArray && isset($value['visible'])){ if(!$value['visible']) return ''; unset($value['visible']); }
		if($isValueArray && isset($value['current'])){
			$url=$value[0];
			if($value['current']){
				$value['current']=1;
			}
			unset($value[0]);
			if(!empty($value)) $linkoptions=$value+$linkoptions;
		}else{
			$startsWith=$options['startsWith'];
			if(!$isValueArray) $url=$value;
			else{
				if(isset($value['startsWith'])) $startsWith=$value['startsWith'];
				$url=$value[0];
				unset($value[0],$value['startsWith']);
				/*#if DEV */
				if(isset($value['options'])) throw new Exception('Deprecated');
				/*#/if */
				if(!empty($value)) $linkoptions=$value+$linkoptions;
			}
			$linkoptions['current']=$startsWith;
		}
		//if($linkoptions['current']) $lioptions['class']="current";
		$res=HHtml::link($title,$url?$url:'/',$linkoptions);
		if($lioptions!==false) $res=HHtml::tag('li',$lioptions,$res,false);
		return $res;
	}
	
	/**
	 * Creates a menu left or a select filtrable if there are too many items
	 * 
	 * @param int
	 * @param array
	 * @param array
	 * @return string
	 */
	public static function menuOrSelectFiltrable($max,$links,$options=array()){
		if(count($links)<$max) return self::left($links,$options);
		return self::selectFiltrable($links,$options);
	}
	
	/**
	 * @param array
	 * @param array
	 * @return string
	 */
	public static function selectFiltrable($links,$options=array()){
		$options+=array('selectAttributes'=>array('id'=>uniqid('selectFiltrable_')));
		$res=HHtml::openTag('select',$options['selectAttributes'])
			.HHtml::tag('option',array('value'=>''),'');
		foreach($links as $title=>&$value){
			if(is_int($title)){
				if($value===false) continue;
				$title=$value['title'];
			}
			if(is_array($value) && isset($value['visible']) && !$value['visible']){ continue; }
			$attributes=array();
			if(is_array($value) && isset($value['current'])){
				$url=$value['url'];
				if($value['current']) $attributes['selected']=true;
			}else{
				if(!is_array($value)) $url=$value;
				else{
					$url=$value['url'];
					unset($value['url'],$value['current']);
					if(!empty($value)) $attributes=$value+$attributes;
				}
			}
			$attributes['onclick']='S.redirect("'.HHtml::url($url).'")';
			$res.=HHtml::tag('option',$attributes,$title);
		}
		return $res.HHtml::closeTag('select').HHtml::jsInline('$(document).ready(function(){$(\'#'.$options['selectAttributes']['id'].'\').combobox()})');
	}
}