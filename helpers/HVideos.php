<?php
/**
 * Videos helper
 * 
 * Display videos
 */
class HVideos{
	/**
	 * @param int
	 * @param int|string
	 * @param array
	 * @see CVideos
	 */
	public static function display($type,$id,$options){
		switch ($type){
			case CVideos::TYPE_YOUTUBE: return self::youtube($id,$options);
			case CVideos::TYPE_DAILYMOTION: return self::dailymotion($id,$options);
			case CVideos::TYPE_VIMEO: return self::vimeo($id,$options['width'],$options['height']);
		}
	}
	
	/**
	 * Display a youtube video
	 * 
	 * @param string
	 * @param array
	 * @param bool
	 * @return string
	 */
	public static function youtube($id,$options=array(),$jsapiEnable=false){
		$url='http://www.youtube.com/v/'.$id.'&hl=fr_FR&fs=1&';
		if($jsapiEnable) $url.='&enablejsapi=1&playerapiid='.$options['id'];

		return self::videoObject($url, $options);
	}

	/**
	 * Display a dailymotion video
	 * 
	 * @param string
	 * @param array
	 * @return string
	 */
	public static function dailymotion($id,$options=array()){
		$url='http://www.dailymotion.com/swf/'.$id;
		return self::videoObject($url, $options);
	}
	
	/**
	 * Display a vimeo video
	 * 
	 * @param string
	 * @param int
	 * @param int
	 * @return string
	 */
	public static function vimeo($id,$width=400,$height=225){
		return '<iframe src="http://player.vimeo.com/video/'.$id.'" width="'.$width.'" height="'.$height.'" frameborder="0"></iframe>';
	}
	
	/**
	 * Display a google video
	 * 
	 * @param string
	 * @param array
	 * @param int
	 * @param int
	 * @return string
	 */
	public static function google($id,$attributes=array(),$width=400,$height=326){
		$attributes=$attributes+array('allowFullScreen'=>true,'allowScriptAccess'=>'always');
		$attributes['allowFullScreen'] = ($attributes['allowFullScreen']) ? 'true' : 'false';
		$attributes['src']='http://video.google.fr/googleplayer.swf?docid='.$id;
		$attributes['type']='application/x-shockwave-flash';
		if(!isset($attributes['style']) && $width && $width) $attributes['style']='width:'.$width.'px;height:'.$height.'px';
		return HHtml::tag('embed',$attributes,'',false);
	}

	/**
	 * @param string
	 * @param array
	 * @return string
	 */
	private static function videoObject($url,$options){
		// allowScriptAccess : (always, never, samedomain)
		$options = $options+array('id'=>false,'width'=>400,'height'=>225,'allowFullScreen'=>true,'allowScriptAccess'=>'always');
		$options['allowFullScreen'] = ($options['allowFullScreen']) ? 'true' : 'false';
		
		return '<object width="'.$options['width'].'" height="'.$options['height'].'"'.($options['id'] ? 'id="'.$options['id'].'"' : '').'>'
			.'<param name="movie" value="'.$url.'"></param>'
			.'<param name="allowFullScreen" value="'.$options['allowFullScreen'].'"></param>'
			.'<param name="allowscriptaccess" value="'.$options['allowScriptAccess'].'"></param>'
			.'<param name="wmode" value="transparent"></param>'
			.'<embed src="'.$url.'" type="application/x-shockwave-flash" allowscriptaccess="'.$options['allowScriptAccess'].'" allowfullscreen="'.$options['allowFullScreen'].'" width="'.$options['width'].'px" height="'.$options['height'].'px"></embed>'
		.'</object>';
	}
}
	