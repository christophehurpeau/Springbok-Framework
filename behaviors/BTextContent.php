<?php
trait BTextContent{
	public function regenerateText(){
		if(!empty($this->text)) VSeo::generate(static::$__className,$this->id);
		return true;
	}
	
	public function _setTextToNullIfEmtpy(){
		if(empty($this->text) && isset($this->text)) $this->text=null;
		return true;
	}
	
	public static function findOneForSeo($id){
		return self::QOne()->where(array('id'=>$id))->execute();
	}
}