<?php
/**
 * A <input type="submit"> element
 * 
 * @see HElementForm::submit()
 */
class HElementFormInputSubmit extends HElementFormContainable{
	/**
	 * @internal
	 * @param HElementForm
	 * @param string
	 */
	public function __construct($form,$title){
		$this->form=$form;
		$this->error=false;
		if($title===true) $title=_tC('Save');
		$this->attributes['value']=$title;
		$this->attributes['type']=$this->attributes['class']='submit';
	}
	
	/**
	 * @return HElementFormContainer
	 */
	public function container(){
		$container=new HElementFormContainer($this->form,$this,'submit');
		return $container->noError();
	}
	
	/**
	 * @return string
	 */
	public function toString(){
		return HHtml::tag('input',$this->attributes);
	}
	
}