<?php
/**
 * A <a> element
 */
class HElementLink extends HElementWithContent{
	/**
	 * Adds a icon and a text
	 * 
	 * @param string
	 * @param string
	 * @return HElementLink|self
	 * 
	 * @see HElement::iconLink()
	 */
	public function icon($icon,$text){
		return $this->iconHtml($icon,h($text));
	}
	
	/**
	 * Adds a icon and a text
	 * 
	 * @param string
	 * @param string
	 * @return HElementLink|self
	 * 
	 * @see HElement::iconLinkHtml()
	 */
	public function iconHtml($icon,$html){
		/*#if DEV */ if(!empty($this->content)) throw new Exception('$this->content is not empty'); /*#/if*/
		$this->content='<span class="icon '.h($icon).'"></span>'.$html;
		$this->contentEscape=false;
		/*#if DEV */ if(isset($this->attributes['class'])) throw new Exception('specify your attr "class" after calling icon() or iconHtml()'); /*#/if*/
		$this->setClass('aicon');
		return $this;
	}
	
	/**
	 * Set the href attribute, without url resolution
	 * 
	 * @param string
	 * @return HElementLink|self
	 */
	public function href($href){ $this->attributes['href']=$href; return $this; }
	
	/**
	 * Set the href attribute with url resolution and routes
	 * 
	 * @param string|array
	 * @param string|null
	 * @param string|null|false
	 * @param bool
	 * @param bool
	 * @param bool|null
	 * @return HElementLink|self
	 * 
	 * @see HHtml::url
	 */
	public function url($url,$entry=null,$full=null,$escape=false,$cache=false,$https=null){
		$this->attributes['href']=HHtml::url($url,$entry,$full,$escape,$cache,$https);
		return $this;
	}
	
	
	public function cut($maxSize,$title,$href=null){
		if($href===false) $href=$title;
		if(($l=strlen($title)) > $maxSize){
			$this->hasAttr('title') || $this->attr('title',$title);
			$title=substr($title,0,$halfSize=floor(min($l,$maxSize-3)/2)).'...'.substr($title,$l-$halfSize);
		}
		$this->href($href);
		$this->content($title);
		return $this;
	}
	
	/**
	 * @param HElementImage
	 * @return HElementLink|self
	 */
	public function image($image){
		
	}
	
	/**
	 * @return string
	 */
	public function __toString(){ return $this->_render('a'); }
}