<?php
class HElementAjaxFileForm extends HElementForm{
	public function __construct($formId,$action,$jsParams,$entry=null,$urlfull=null){
		$this->action=$action; $this->actionEntry=$entry; $this->urlfull=$urlfull;
		$this->attr('method','post');
		$this->attr('enctype','multipart/form-data');
		$this->id($id=('formUploadFiles'.$formId));
		HHtml::jsReady('$("#'.$id.'").sAjaxUploadFiles('.$jsParams.')');
	}
	
	
	public function __toString(){
		return '<form target="'.$this->getAttr('id').'UploadTarget" action="'.HHtml::url($this->action,$this->actionEntry,$this->urlfull,true).'"'
				.$this->_attributes().'>';
	}
	
	public function end($title=false){
		if($title!==false) $res=$this->submit($title);
		else $res='';
		if($this->fieldsetStarted) $res.=$this->fieldsetStop();
		return $res.'</form>';
	}
}