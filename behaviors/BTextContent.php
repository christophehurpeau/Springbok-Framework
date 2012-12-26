<?php
trait BTextContent{
	public static $afterUpdate=['regenerateText'];
	
	public function regenerateText(){
		if(!empty($this->text)) VSeo::generate(static::$__className,$this->id);
		return true;
	}
}
