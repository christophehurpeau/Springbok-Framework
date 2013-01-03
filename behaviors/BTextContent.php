<?php
trait BTextContent{
	public static $afterUpdate=['regenerateText'];
	
	public function regenerateText(){
		if(!empty($this->text)) VSeo::generate(static::$__className,$this->id);
		return true;
	}
	
	public static function findOneForSeo($id){
		return self::QOne()->where(array('id'=>$id))->execute();
	}
}
