<?php
class HElementAjaxFileForm extends HElement{
	public static function create(){
		return new static();
	}
	
	private $action,$actionEntry,$urlfull;
	
	
	public function __construct($formId,$action,$entry=null,$urlfull=null){
		$this->action=$action; $this->actionEntry=$entry; $this->urlfull=$urlfull;
		$this->attr('method','post');
		$this->attr('enctype','multipart/form-data');
		$this->id($id=('formUploadFiles'.$formId));
		HHtml::jsReady('S.upload($("#'.$id.'"))');
	}
	
	
	public function __toString(){
		return '<form action="'.HHtml::url($this->action,$this->actionEntry,$this->urlfull,true).'"'
				.$this->_attributes().'>';
	}
	
	public function end($title=false){
		return '</form>';
	}
}