<?php
/** @deprecated */
class HTableCompare extends HTable{
	
	protected static function displayResults(&$component,&$results){
		foreach($results as $key=>&$model){
			if(isset($component->rowActions) || $component->defaultAction) $pkValue=$model->_getPkValue();
			$class=$i++%2 ? 'alternate' : '';
			echo '<tr';
			if($component->defaultAction!==null){
				$link=HHtml::urlEscape($component->defaultAction.'/'.$pkValue);
				$class.=' pointer';
				echo ' onclick="S.redirect(\''.$link.'\')"';
			}else $link=false;
			echo (empty($class)?'':' class="'.trim($class).'"').'>';
			foreach($component->displayedModelFields as $key=>$field){
				$value=$model->_get($field);
				if($link){
					echo '<td class="center"><a href="'.h($link).'">'.$value.'</a></td>';
					//$field['callback']=function($val) use(&$link){return '<a href="'.h($link).'">'.$val.'</a>';};
					$link=false;
				}else self::displayValue($component->fields[$key],$value,$model);
			}
			$keyAdd=$key+1;
			
			foreach($component->_fieldsCompared() as $key=>$field){
				$field=$component->fields[$key+$keyAdd];

				$same=true; $val=$model->{$component->comparedKeys[0]}->$field['key'];
				foreach(array_slice($component->comparedKeys,1) as $key){
					$comp=isset($field['icons']) ? ($field['icons'][$model->$key->$field['key']] != $field['icons'][$val]) : $model->$key->$field['key'] != $val;
					if($comp){ $same=false; break; }
				}
				$field['attributes']['class']=$same?'valid':'invalid';
				
				$val='';
				foreach($component->comparedKeys as $key){
					$cValue=$model->$key->$field['key']; $cObj=$model->$key;
					if(!$same) $val.='<b>'.$key.':</b> ';
					$val.=self::getDisplayableValue($field,$cValue,$cObj).'<br />';
					if($same) break;
				}
				
				$field['escape']=false;
				unset($field['icons'],$field['tabResult']);
				self::displayValue($field,$val,$model);
			}
			
			//foreach($values as $value) 
			if(isset($component->rowActions)){
				echo '<td>';
				foreach($component->rowActions as $action=>$options){
					if(is_int($action)){ $action=$options; $options=array(); }
					$options['class']='action '.$action;
					echo HHtml::link('',$action.'/'.$pkValue,$options);
				}
				echo '</td>';
			}
			echo '</tr>';
		}
		/*
		
		foreach($results as $key=>&$values){
			if(isset($component->rowActions) || isset($component->defaultAction)) $pkValue=$values->_getPkValue();
			echo '<tr';
			if($component->defaultAction!==null) echo ' class="pointer" onclick="window.location=\''.HHtml::url('/'.strtolower(CRoute::getController()).'/'.$component->defaultAction.'/'.$pkValue,false,true).'\'"';
			echo '>';

			foreach($component->displayedModelFields as $key=>$field)
				self::displayValue($component->fields[$key],$values->$field);
			$keyAdd=$key+1;
			
			foreach($component->_fieldsCompared() as $key=>$field){
				$field=$component->fields[$key+$keyAdd];
				$field['escape']=false;

				$same=true; $val=$values->{$component->comparedKeys[0]}->$field['key'];
				foreach(array_slice($component->comparedKeys,1) as $key)
					if($values->$key->$field['key'] != $val){ $same=false; break; }
				$field['attributes']['class']=$same?'valid':'invalid';
				
				if(!$same){
					$val='';
					foreach($component->comparedKeys as $key) $val.='<b>'.$key.':</b> '.h($values->$key->$field['key']).'<br />';
				}
				
				self::displayValue($field,$val);
			}
			//foreach($values as $value) 
			if(isset($component->rowActions)){
				echo '<td>';
				foreach($component->rowActions as $action)
					echo HHtml::imgLink('/actions/'.$action.'.png','/'.$component->controller.'/'.$action.'/'.$pkValue);
				echo '</td>';
			}
			echo '</tr>';
		}*/
	}
}
