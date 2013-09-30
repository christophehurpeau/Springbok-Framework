<?php
/**
 * Google Helper
 */
class HGoogle{
	/**
	 * Returns a static Google Map image url
	 * 
	 * @param string the size of the map
	 * @param string|false
	 * @param array|string|false
	 * @param array|null
	 * @param string|null
	 * @return string
	 */
	public static function staticMap($size,$zoom,$center_or_path,$markers=null,$mapType=null){
		if(is_array($center_or_path)){ $center=false; $path=$center_or_path; }
		else{ $center=$center_or_path; $path=false; }
		$url='';
		if($center!==false) $url.='&center='.$center;
		if($path!==false) $url.='&path='.implode('|',$path);
		if($zoom!==false) $url.='&zoom='.$zoom;
		if($markers!==null){
			if(isset($markers['multiple'])) unset($markers['multiple']);
			else $markers=array($markers);
			foreach($markers as $marker){
				$url.='&markers=';
				if(isset($marker['style'])){
					$url.=implode('|',$marker['style']).'|';
					unset($marker['style']);
				}
				$url.=implode('|',$marker);
			}
		}
		$url.='&size='.$size.($mapType===null?'':'&maptype='.$mapType);
		return 'http://maps.googleapis.com/maps/api/staticmap?sensor=false'.$url;
	}
}
