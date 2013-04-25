<?php
class SDetailedHtmlException extends SDetailedException{
	
	public function __construct($message,$code,$title,$details,$previous=null){
		parent::__construct($message,$code,$title,$details,$previous);
	}
	
	public function detailsHtml(){
		return $this->details;
	}
}
