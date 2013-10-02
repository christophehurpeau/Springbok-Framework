<?php
/** @deprecated */
class HTableJsActions{
	private $modelTableComponent,$content='',$canCancel=false;
	public function __construct($modelTableComponent){
		$this->modelTableComponent=$modelTableComponent;
	}
	
	public function canCancel(){
		$this->canCancel=true;
		return $this;
	}
	public function action($actionName,$code){
		$this->content.='$("table a.action.'.$actionName.'").click(function(){var t=$(this),p=t.parent().html(S.imgLoading).addClass("center");$.get(t.attr("href"),function(data){'
				.'p'.($this->canCancel===false?'.empty()':'.html("Annuler")').';'
				.$code.'});return false;});';
		return $this;
	}
	
	public function end(){
		HHtml::jsReady($this->content);
		return $this->modelTableComponent;
	}
}
