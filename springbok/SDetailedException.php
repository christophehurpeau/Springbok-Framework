<?php
/**
 * A Detailed Exception : can be used to display more info in a HTML Error page
 */
class SDetailedException extends Exception{
	protected $title,$details;
	
	/**
	 * @param string
	 * @param int
	 * @param string|null
	 * @param string
	 * @param Exception|null
	 */
	public function __construct($message,$code=0,$title=null,$details='',$previous=null){
		parent::__construct($message,$code,$previous);
		$this->title=empty($title)?$message:$title;
		$this->details=$details;
	}
	
	/**
	 * @return string
	 */
	public function getTitle(){
		return $this->title;
	}
	
	/**
	 * @param string
	 * @return void
	 */
	public function setDetails($details){
		$this->details=$details;
	}
	
	/**
	 * @return bool
	 */
	public function hasDetails(){
		return $this->details!=='';
	}
	
	/**
	 * @return string
	 */
	public function getDetails(){
		return $this->details;
	}
	
	/**
	 * @return string
	 */
	public function detailsHtml(){
		return nl2br(h($this->details));
	}
	
	/**
	 * @return string
	 */
	public function toHtml(){
		$class=__CLASS__;
		return ($class==='SDetailedException'?'':'<b>'.h($class).'</b>').($this->code===0?'':' ['.h($this->code).']')
					.($this->code===0 && $class==='SDetailedException'?'':': ')
					.h($this->title)."<br>".$this->detailsHtml();
	}
}
