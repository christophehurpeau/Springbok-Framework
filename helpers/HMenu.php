<?php
class HMenu{
	public static $tagName='nav',$separator='-';
	
	
	public static function top($links,$options=array()){
		return self::create($links,$options,'top');
	}
	public static function ajaxTop($links,$options=array()){
		return self::create($links,$options,'top ajax');
	}
	
	public static function left($links,$options=array()){
		return self::create($links,$options,'left');
	}
	public static function &ul($links,$options=array()){
		self::$tagName='ul';
		$res=self::create($links,$options,'');
		self::$tagName='nav';
		return $res;
	}
	
	public static function create($links,$options=array(),$type='top'){
		$options=$options+array('lioptions'=>array(),'linkoptions'=>array(),'startsWith'=>false);
		if(!isset($options['menuAttributes']['class'])) $options['menuAttributes']['class']=$type;
		$res=HHtml::openTag(self::$tagName,$options['menuAttributes']);
		if(self::$tagName!=='ul') $res.=HHtml::openTag('ul',array());
		foreach($links as $title=>$value){
			if(is_int($title)){
				if($value===false){ $res.=HHtml::tag('li',array('class'=>'separator'),self::$separator); continue; }
				$title=$value['title'];
			}
			
			if(is_array($value) && isset($value['visible'])){ if(!$value['visible']) continue; unset($value['visible']); }
			$res.=self::link($title,$value,$options['linkoptions'],array('startsWith'=>$options['startsWith']),$options['lioptions']);
		}
		if(self::$tagName!=='ul') $res.=HHtml::closeTag('ul');
		return $res.HHtml::closeTag(self::$tagName);
	}
	public static function link($title,$value,$linkoptions=array(),$options=array(),$lioptions=false){
		if(!isset($options['startsWith'])) $options['startsWith']=false;
		$isValueArray=is_array($value);
		if($isValueArray && isset($value['current'])){
			$url=$value['url'];
			if($value['current']){
				$linkoptions['current']=1;
			}
			if(!empty($value)) $linkoptions=$value+$linkoptions;
		}else{
			$startsWith=$options['startsWith'];
			if(!$isValueArray) $url=$value;
			else{
				if(isset($value['startsWith'])) $startsWith=$value['startsWith'];
				$url=$value['url'];
				unset($value['url'],$value['startsWith']);
				/* DEV */
				if(isset($value['options'])) throw new Exception('Deprecated');
				/* /DEV */
				if(!empty($value)) $linkoptions=$value+$linkoptions;
			}
			$linkoptions['current']=$startsWith;
		}
		//if($linkoptions['current']) $lioptions['class']="current";
		$res=HHtml::link($title,$url?$url:'/',$linkoptions);
		if($linkoptions!==false) $res=HHtml::tag('li',$lioptions,$res,false);
		return $res;
	}
	
	/**
	*/
	public static function menuOrSelectFiltrable($max,$links,$options=array()){
		if(count($links)<$max) return self::left($links,$options);
		return self::selectFiltrable($links,$options);
	}
	
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