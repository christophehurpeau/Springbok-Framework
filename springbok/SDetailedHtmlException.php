<?php
/**
 * Fully details Exception in html
 */
class SDetailedHtmlException extends SDetailedException{
	/**
	 * @param string
	 * @param int
	 * @param string
	 * @param string
	 * @param Exception|null
	 */
	public function __construct($message,$code,$title,$detailsInHtml,$previous=null){
		parent::__construct($message,$code,$title,$detailsInHtml,$previous);
	}
	
	/**
	 * @return string
	 */
	public function detailsHtml(){
		return $this->details;
	}
}
