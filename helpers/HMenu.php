<?php
class HMenu{
	public static $tagName='nav',$separatorTop='-',$separator='-----------';
	
	
	public static function top($links,$options=array()){
		return self::create($links,$options,'top');
	}
	public static function topHtml($links,$options=array()){
		$options['linkoptions']['escape']=false;
		return self::create($links,$options,'top');
	}
	public static function ajaxTop($links,$options=array()){
		return self::create($links,$options,'top ajax');
	}
	
	public static function left($links,$options=array()){
		return self::create($links,$options,'left');
	}
	public static function leftHtml($links,$options=array()){
		$options['linkoptions']['escape']=false;
		return self::create($links,$options,'left');
	}
	public static function ul($links,$options=array()){
		self::$tagName='ul';
		$res=self::create($links,$options,'');
		self::$tagName='nav';
		return $res;
	}
	
	public static function create($links,$options=array(),$type='top'){
		$options=$options+array('lioptions'=>array(),'linkoptions'=>array(),'startsWith'=>0);
		if(!isset($options['menuAttributes']['class'])) $options['menuAttributes']['class']=$type;
		$res=HHtml::openTag(self::$tagName,$options['menuAttributes']);
		if(self::$tagName!=='ul') $res.='<ul>';
		foreach($links as $title=>$value){
			if(is_int($title)){
				if($value===false){ $res.=HHtml::tag('li',array('class'=>'separator'),$type==='top'?self::$separatorTop:self::$separator); continue; }
				$title=$value['title'];
			}
			if(is_array($value) && isset($value['children'])){
				if( isset($value['visible']) && !$value['visible']) continue;
				$res.='<li>'.(!empty($value['escape'])?h($title):$title).'<ul>';
				foreach($value['children'] as $childTitle=>$childValue){
					$res.=self::link($childTitle,$childValue,$options['linkoptions'],array('startsWith'=>$options['startsWith']),$options['lioptions']);
				}
				$res.='</ul></li>';
			}else
				$res.=self::link($title,$value,$options['linkoptions'],array('startsWith'=>$options['startsWith']),$options['lioptions']);
		}
		if(self::$tagName!=='ul') $res.='</ul>';
		return $res.HHtml::closeTag(self::$tagName);
	}
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
				/* DEV */
				if(isset($value['options'])) throw new Exception('Deprecated');
				/* /DEV */
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