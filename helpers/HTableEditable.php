<?php
class HTableEditable extends HTable{
	private static $pkField,$pkValue;
	public static function table(CTable $component,$pkField=null,$url=null,$displayTotalResults=true){
		self::$pkField=$pkField;
		echo HHtml::jsInline('var editableTable=new HTableEditable('.json_encode(HHtml::url($url)).')');
		parent::table($component,$displayTotalResults);
	}
	
	protected static function displayValue(&$field,&$value,&$obj){
		if(isset($field['editable']) && $field['editable']) $field['escape']=false;
		if($field['key']===self::$pkField) self::$pkValue=$value;
		parent::displayValue($field,$value,$obj);
 	}
	
	public static function getDisplayableValue(&$field,&$value,&$obj){
		if(isset($field['editable']) && $field['editable']){
			return '<div class="input text" style="width:100%;position:relative"><input type="text" value="'.h($value).'" style="width:98%" onchange="editableTable.updateField('.h2(json_encode(self::$pkValue)).',this)"/></div>';
		}
		return HTable::getDisplayableValue($field,$value,$obj);
	}
}
