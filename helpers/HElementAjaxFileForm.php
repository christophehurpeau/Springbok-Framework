<?php
/**
 * An Form for ajax file uploading
 */
class HElementAjaxFileForm extends HElementForm{
	/** @internal */
	public function __construct($formId,$action,$jsParams,$entry=null,$urlfull=null){
		$this->action=$action; $this->actionEntry=$entry; $this->urlfull=$urlfull;
		$this->attr('method','post');
		$this->attr('enctype','multipart/form-data');
		$this->id($id=('formUploadFiles'.$formId));
		HHtml::jsReady('$("#'.$id.'").sAjaxUploadFiles('.$jsParams.')');
	}
	
	/**
	 * @return string
	 */
	public function __toString(){
		return '<form target="'.$this->getAttr('id').'UploadTarget" action="'.HHtml::url($this->action,$this->actionEntry,$this->urlfull,true).'"'
				.$this->_attributes().'>';
	}
}