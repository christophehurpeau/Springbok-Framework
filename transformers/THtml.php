<?php
class THtml extends STransformer{
	private static $tAligns=array('center'=>'center','right'=>'alignRight');
	protected $component;
	
	public function __construct(&$component){
		echo '<table class="table">';
		$this->component=&$component;
	}
	
	public function noResults($colspan=0){
		echo '<tr><td'.($colspan===0?'':' colspan="'.$colspan.'"').'>'._tC('No result').'</td></td>';
	}
	
	public function startHead(){
		echo '<thead>';
	}
	
	public function titles(&$fields,&$queryFields){
		echo '<tr>';
		foreach($fields as &$field){
			$th='';
			if(isset($field['align'])) $th=' class="'.self::$tAligns[$field['align']].'"';
			if(isset($field['widthPx'])) $th.=' style="width:'.$field['widthPx'].'px"';
			elseif(isset($field['width%'])) $th.=' style="width:'.$field['width%'].'%"';
			echo '<th'.$th.'>'.h2($field['title']);
			if($this->component->isOrderAllowed() && $queryFields!==null && isset($field['key']) && in_array($field['key'],$queryFields) && $field['type'] !=='boolean') echo '<div class="order">'
						.'<a class="arrow arrowUp" href="?orderBy='.($hKey=h($field['key'])).'&orderByDesc"></a>'
						.'<a class="arrow arrowDown" href="?orderBy='.($hKey=h($field['key'])).'"></a>'
					.'</div>';
			echo '</th>';
		}
		
		if($this->component->actionClick!==null && is_string($this->component->actionClick) && $this->component->actionClick[0]!=='/')
			$this->component->actionClick='/'.$this->component->controller.'/'.$this->component->actionClick;
		if($this->component->rowActions!==null){
			echo '<th style="width:'.(1+ count($this->component->rowActions)*17).'px">'.h2(_tC('Actions')).'</th>';
			foreach($this->component->rowActions as $k=>&$action){
				if(is_string($action)) $action=array(array('class'=>'action '.$action),$action);
				else{
					$attrs=$action; unset($attrs[0],$attrs[1]);
					$attrs['class']='action '.$action[0];
					$action=array($attrs,isset($action[1]) ? $action[1] : $action[0]);
				}
				if($action[1] !== '/') $action[1]='/'.$this->component->controller.'/'.$action[1];
			}
		}
		echo '</tr>';
	}

	public function filters(&$form,&$fields,&$FILTERS){
		echo '<tr class="form">';
		foreach($fields as &$field){
			$filterField=NULL; $attributes=array(); $filterName='filters['.$field['key'].']';
			if(isset($field['filter']) && is_array($field['filter'])){
				$attributes['empty']='';
				if(isset($FILTERS[$field['key']])) $attributes['selected']=$FILTERS[$field['key']];
				$filterField=$form->select($filterName,$field['filter'],$attributes);
			}elseif(isset($field['tabResult'])){
				$attributes['empty']='';
				if(isset($FILTERS[$field['key']])) $attributes['selected']=$FILTERS[$field['key']];
				$filterField=$form->select($filterName,$field['tabResult'],$attributes);
			}
			if($filterField===NULL){
				if(isset($FILTERS[$field['key']])) $attributes['value']=$FILTERS[$field['key']];
				$filterField=$form->input($filterName,$attributes);
			}
			echo '<td>'.$filterField.'</td>';
		}
		if(isset($this->component->rowActions)) echo '<td></td>';
		echo '</tr>';
	}

	public function endHead(){
		echo '</thead>';
	}
	
	public function startBody(){
		echo '<tbody>';
	}
	
	public function displayResults(&$results,&$fields){
		$iRow=0;
		foreach($results as $key=>&$model){
			if(isset($this->component->rowActions) || $this->component->actionClick) $pkValue=$model->_getPkValue();
			$class=$iRow++%2 ? 'alternate' : '';
			echo '<tr';
			if($this->component->actionClick !==null){
				if(is_array($this->component->actionClick)){
					$defaultActionUrl=$this->component->actionClick;
					$defaultActionUrl[]=$pkValue;
				}elseif(is_string($this->component->actionClick)) $defaultActionUrl=$this->component->actionClick.'/'.$pkValue;
				else{
					$callback=&$this->component->actionClick;
					$defaultActionUrl=$callback($pkValue,$model);
				}
				$class.=' pointer';
				echo ' onclick="S.redirect(\''.HHtml::url($defaultActionUrl,false,true).'\')"'; //event.target.nodeName
			}
			echo (empty($class)?'':' class="'.trim($class).'"').'>';
			foreach($fields as $i=>&$field){
				$value=static::getValueFromModel($model,$field,$i);
				$this->displayValue($field,$value,$model);
			}
			//foreach($values as $value) 
			if($this->component->rowActions !==null){
				echo '<td>';
				foreach($this->component->rowActions as &$action)
					echo HHtml::link('',$action[1].'/'.$pkValue,$action[0]);
				echo '</td>';
			}
			echo '</tr>';
		}
	}

	public function displayValue(&$field,&$value,&$obj){
		$attributes=isset($field['attributes'])?$field['attributes']:array();
				
		if(!isset($attributes['class'])){
			$class=isset($field['class'])?$field['class']:'';
			if(isset($field['align'])) $class.=' '.self::$tAligns[$field['align']];
			if($class !== '') $attributes['class']=trim($class);
		}
		
		echo HHtml::tag('td',$attributes,$this->getDisplayableValue($field,$value,$obj),$field['escape']);
	}
	
	//end
	public function end(){
		echo '</tbody></table>';
	}
}