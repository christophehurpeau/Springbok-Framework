<?php
/**
 * Input radio for stars
 * 
 * @see HElementForm::stars
 */
class HElementFormStars extends HElementFormInputSelect{
	
	/**
	 * @internal
	 * @param HElementForm
	 * @param string
	 * @param int
	 * @param mixed
	 */
	public function __construct($form,$name,$list=5,$selected=null){
		if(is_int($list)) $list=array_fill(1,$list,null);
		parent::__construct($form,$name,$list,$selected);
		$this->radio();
	}
	
	/**
	 * @return HElementFormContainer
	 */
	public function container(){ return new HElementFormContainer($this->form,$this,'radio stars'); }
}