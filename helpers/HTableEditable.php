<?php
/** @deprecated */
class HTableEditable extends HTable{
	private static $form,$modelName,$pkField,$pkValue;
	public static function table($component,$pkField=null,$url=null,$displayTotalResults=true){
		self::$form=new HForm(self::$modelName=$component->getModelName(),null,'get','div',false,false);
		self::$pkField=&$pkField;
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
			$modelName=&self::$modelName;
			$jsonPkValue=h(json_encode(self::$pkValue));
			
			//<input type="text" value="'.h($value).'" style="width:98%" onchange=""/>
			$def=$modelName::$__PROP_DEF[$name=$field['key']];
			$infos=$modelName::$__modelInfos['columns'][$name];
			
			$attributes=array('onchange'=>'editableTable.updateField(\''.$name.'\','.$jsonPkValue.',this)','value'=>&$value);
			$containerAttributes=array('sytle'=>'width:100%;position:relative');
			
			if(substr($name,-3)==='_id' && Controller::_isset($vname=UInflector::pluralize(substr($name,0,-3))))
				return self::$form->select($name,Controller::get($vname));
			elseif($def['type']==='boolean'){
				$attrs=$attributes;
				if($value==='') $attrs['checked']=true;
				return self::$form->checkbox($name,false,$attrs,$containerAttributes);
			}elseif(isset($def['annotations']['Enum'])) return self::$form->select($name,call_user_func($modelName.'::'.$def['annotations']['Enum'].'List'),
																	array('onchange'=>$attributes['onchange'],'selected'=>&$value),$containerAttributes);
			elseif(isset($def['annotations']['Text'])) return self::$form->textarea($name,$attributes,$containerAttributes);
			else return self::$form->input($name,$attributes+array('style'=>'width:98%'),$containerAttributes,1.4);
		}
		return HTable::getDisplayableValue($field,$value,$obj);
	}
}
