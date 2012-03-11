<?php
class HTable{
	private static $tAligns=array('center'=>'center','right'=>'align_right');
	
	public static function table(CTable $component,$displayTotalResults=true){
		/* DEV */if(!($component instanceof CTable)) throw new Exception('Your component must be an instance of CTable'); /* /DEV */
		$component->execute();
		
		//if($component->export) return self::export($component);
		$results=$component->getResults();
		
		if($component->filter){
			$formId=uniqid();
			$form=HForm::create(NULL,array('id'=>$formId,'rel'=>'content'),false,false);
		}

		if(!empty($results) && $displayTotalResults===true) echo '<div class="totalResults">'.$component->getTotalResults().' '.($component->getTotalResults()===1?_tC('result'):_tC('results')).'</div>';
		if($component->export!==false){
			echo '<span class="exportLinks">'; 
			foreach(explode(',',$component->export[0]) as $exportType)
				echo HHtml::iconAction('page_'.$exportType,'?export='.$exportType,array('target'=>'_blank'));//target : springbok.ajax
			echo '</span>';
		}
		if($component->filter) echo '<div class="filterHelp">'.$form->submit(_tC('Filter')).' (<i>'._tC('filter.help').'</i>)</div>';
		
		if($component->hasPager()){
			$pagination=$component->pagination(); 
			if($component->filter){
				$idPage='page'.$formId;
				echo '<input id="'.$idPage.'" type="hidden" name="page"/>'.HHtml::jsInline('var changePage=function(num){$(\'#'.$idPage.'\').val(num);$(\'#'.$formId.'\').submit();return false;}');
			}else{
				$href=h2(HHtml::url(CRoute::getAll(),false,true).'?');
				if(!empty($_POST)) $href.=http_build_query($_POST,'','&').'&';
				if(!empty($_GET)){
					$get=$_GET;
					unset($get['page'],$get['ajax']);
					if(!empty($get)) $href.=http_build_query($get,'','&').'&';
				}
			}
			echo $pager='<div class="pager">'.HPagination::createPager($pagination->getPage(),$pagination->getTotalPages(),
			$component->filter?function($page) use(&$idPage,&$formId){
				return ' href="#" onclick="return changePage('.$page.');"';
			}:function($page) use(&$href){
				return ' href="'.$href.'page='.$page.'"';
			}).'</div>';
		}else $pager=false;


		echo '<table class="table">';
		if(!$component->filter && empty($results)) echo '<tr><td>'._tC('No result').'</td></td>';
		else{
			echo '<thead><tr>';
			
			foreach($component->fields as &$field){
				$th='';
				if(isset($field['align'])) $th=' class="'.self::$tAligns[$field['align']].'"';
				if(isset($field['widthPx'])) $th.=' style="width:'.$field['widthPx'].'px"';
				elseif(isset($field['width%'])) $th.=' style="width:'.$field['width%'].'%"';
				echo '<th'.$th.'>'.h2($field['title']).'</th>';
			}
			/*}else{
				$component->fields=array();
				foreach(current($results) as $name=>$val){
					$component->fields[$name]=array();
					echo '<th>'.h($name).'</th>';
				}
			}*/
			
			if($component->defaultAction!==null && is_string($component->defaultAction) && $component->defaultAction[0]!=='/') $component->defaultAction='/'.$component->controller.'/'.$component->defaultAction;
			if($component->rowActions!==null){
				echo '<th style="width:'.(count($component->rowActions)*16).'px">'.h2(_tC('Actions')).'</th>';
				foreach($component->rowActions as $k=>&$action){
					if(is_string($action)) $action=array('url'=>$action,'icon'=>$action);
					if($action['url'][0] !== '/') $component->rowActions[$k]['url']='/'.$component->controller.'/'.$action['url'];
				}
			}
			echo '</tr>';
			
			if($component->filter){
				echo '<tr class="form">';
				foreach($component->fields as &$field){
					$filterField=NULL; $attributes=array(); $filterName='filter_'.$field['key'];
					if(isset($field['filter']) && is_array($field['filter'])){
						$attributes['empty']='';
						$filterField=$form->select($filterName,$field['filter'],$attributes);
					}elseif(isset($field['tabResult'])){
						$attributes['empty']='';
						$filterField=$form->select($filterName,$field['tabResult'],$attributes);
					}
					if($filterField===NULL) $filterField=$form->input($filterName,$attributes).'</td>';
					echo '<td>'.$filterField.'</td>';
				}
				if(isset($component->rowActions)) echo '<td></td>';
				echo '</tr>';
			}
			
			echo '</thead><tbody>';
			
			if(empty($results)) echo '<tr><td colspan="'.count($component->fields).'">'._tC('No result').'</td></td>';
			else static::displayResults($component,$results);
			
		}
		echo '</tbody></table>';
		if($component->filter) $form->end(false);
		echo $pager;
	}

	protected static function displayResults(&$component,&$results){
		$iRow=0;
		foreach($results as $key=>&$model){
			if(isset($component->rowActions) || $component->defaultAction) $pkValue=$model->_getPkValue();
			$class=$iRow++%2 ? 'alternate' : '';
			echo '<tr';
			if($component->defaultAction !==null){
				if(is_array($component->defaultAction)){
					$defaultActionUrl=$component->defaultAction;
					$defaultActionUrl[]=$pkValue;
				}elseif(is_string($component->defaultAction)) $defaultActionUrl=$component->defaultAction.'/'.$pkValue;
				else{
					$callback=&$component->defaultAction;
					$defaultActionUrl=$callback($pkValue);
				}
				$class.=' pointer';
				echo ' onclick="S.redirect(\''.HHtml::url($defaultActionUrl,false,true).'\')"'; //event.target.nodeName
			}
			echo (empty($class)?'':' class="'.trim($class).'"').'>';
			foreach($component->fields as $i=>$field){
				$value=static::getValueFromModel($model,$field,$i);
				static::displayValue($field,$value,$model);
			}
			//foreach($values as $value) 
			if($component->rowActions !==null){
				echo '<td>';
				foreach($component->rowActions as &$action)
					echo HHtml::link('',$action['url'].'/'.$pkValue,array('class'=>'action '.$action['icon']));
				echo '</td>';
			}
			echo '</tr>';
		}
	}

	protected static function displayValue(&$field,&$value,&$obj){
		$attributes=isset($field['attributes'])?$field['attributes']:array();
				
		if(!isset($attributes['class'])){
			$class=isset($field['class'])?$field['class']:'';
			if(isset($field['align'])) $class.=' '.self::$tAligns[$field['align']];
			if($class !== '') $attributes['class']=trim($class);
		}
		
		echo HHtml::tag('td',$attributes,static::getDisplayableValue($field,$value,$obj),$field['escape']);
	}
	
	public static function getDisplayableValue(&$field,&$value,&$obj){
		if(isset($field['callback'])){
			if($value===null) $value=false;
			return call_user_func($field['callback'],$value);
		}elseif(isset($field['function'])){
			if($value===null) $value=false;
			return call_user_func($field['function'],$obj,$value);
		}elseif(isset($field['tabResult'])){
			if($value===null) $value=false;
			if(isset($field['tabResult'][$value])) return $field['tabResult'][$value];
		}
		return $value;
	}
	
	public static function getValueFromModel(&$model,&$field,&$i){
		return isset($field['key']) ? $model->_get($field['key']) : false;
	}
	
	
	public static function export($type,&$component,&$fields,&$exportOutput,$filename,$title){
		set_time_limit(120); ini_set('memory_limit', '768M'); //TXls use 512M memory cache
		$transformerClass='T'.ucfirst($type);
		if($exportOutput===null && false){
			header('Content-Description: File Transfer');
			header("Content-Disposition: attachment; filename=".date('Y-m-d')."_".$filename.".".$type);
			Controller::noCache();
			header("Content-type: ".$transformerClass::getContentType());
			while(ob_get_level()!==0) ob_end_clean();
		}
		$transformer=new $transformerClass($title);
		$thisClass=get_called_class();
		
		$component->callback(function(&$f) use(&$component,&$transformer,&$fields){
			$component->modelFields=$f;
			if($component->fields !== NULL) $component->_setFields($component->fields,false,true);
			else $component->_setFields($fields,true,true);
			$transformer->titles($component->fields);
		},function(&$row) use(&$component,&$transformer,&$thisClass){
			$transformer->row($row,$component->fields,$thisClass);
		});
		
		if($exportOutput!==null) $transformer->toFile($exportOutput);
		else $transformer->display();
	}
}
