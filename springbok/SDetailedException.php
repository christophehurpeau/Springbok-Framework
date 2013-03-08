<?php
class SDetailedException extends Exception{
	private $title,$details;
	
	public function __construct($message,$code=0,$title=null,$details='',$previous=null){
		parent::__construct($message,$code,$previous);
		$this->title=empty($title)?$message:$title;
		$this->details=$details;
	}
	
	public function getTitle(){
		return $this->title;
	}
	
	public function setDetails($details){
		$this->details=$details;
	}
	
	public function hasDetails(){
		return $this->details!=='';
	}
	
	public function getDetails(){
		return $this->details;
	}
	
	public function detailsHtml(){
		return h($this->details);
	}
	
	public function toHtml(){
		return '<b>'.h(__CLASS__).'</b>'.($this->code===0?'':' ['.h($this->code).']').': '.h($this->title)."<br>".$this->detailsHtml();
	}
}
