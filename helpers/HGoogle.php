<?php
class HGoogle{
	public static function staticMap($size,$zoom,$center_or_path,$markers=null,$mapType=null){
		if(is_array($center_or_path)){ $center=false; $path=$center_or_path; }
		else{ $center=$center_or_path; $path=false; }
		$url='';
		if($center!==false) $url.='&center='.$center;
		if($path!==false) $url.='&path='.implode('|',$path);
		if($zoom!==false) $url.='&zoom='.$zoom;
		$url.='&size='.$size.($mapType===null?'':'&maptype='.$mapType);
		return 'http://maps.googleapis.com/maps/api/staticmap?sensor=false'.$url;
	}
}
