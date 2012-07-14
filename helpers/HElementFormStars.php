<?php
class HElementFormStars extends HElementFormInputSelect{
	
	public function __construct($form,$name,$list=5,$selected=null){
		if(is_int($list)) $list=array_fill(1,$list,null);
		parent::__construct($form,$name,$list,$selected);
		$this->radio();
	}
	public function container(){ return new HElementFormContainer($this->form,$this,'radio stars'); }
}