<?php
class CModelTable{
	private $query,$result;
	public function __construct($query){
		$this->query=&$query;
	}
	
	public $actionClick,$rowActions,$controller,
		$export,$transformers=array('csv'=>'TCsv','xls'=>'TXls'),
		$fields,$modelFields,$fieldsEditable,$translateField=true;
	
	public function &actionClick($action='view'){$this->actionClick=&$action; return $this; }
	public function &actions($actions){$this->rowActions=&$actions; return $this; }
	public function &controller($controller){$this->controller=&$controller; return $this; }
	public function &fields($fields){$this->fields=&$fields; return $this; }
	public function &doNotTranslateFields(){ $this->translateField=false; return $this; }
	public function &fieldsEditable($fields){ $this->fieldsEditable=&$fields; return $this; }
	
	public function getModelName(){ return $this->query->getModelName(); }
	
	public function render($title,$add=false,$layout=null){
		include_once CORE.'mvc/views/View.php';
		$v=new AjaxContentView($title,$layout);
		$this->_add($add);
		$this->display();
		$v->render();
	}
	
	public function renderEditable($title,$pkField,$url,$add=false,$layout=null){
		include_once CORE.'mvc/views/View.php';
		$v=new AjaxContentView($title,$layout);
		$this->_add($add);
		$this->displayEditable($pkField,$url);
		$v->render();
	}
	
	private function _add($add){
		if($add!==false){
			if($add===true) $add=array('modelName'=>$this->getModelName());
			elseif(is_string($add)) $add=array('modelName'=>$add);
			if(!isset($add['form']['action'])) $add['form']['action']='/'.lcfirst($add['modelName']::$__pluralized).'/add';
			if(!isset($add['formContainer'])) $add['formContainer']=false;
			if(!isset($add['fields'])) $add['fields']=array($add['modelName']::$__displayField=>_tF($add['modelName'],'New').' :');
			$form=HForm::create($add['modelName'],$add['form'],$add['formContainer']);
			foreach($add['fields'] as $field=>$label)
				echo $form->input($field,array('label'=>$label));
			echo $form->end(_tC('Add'));
		}
	}
	
	public function _setFields($export=false){
		if($this->fields !== null){
			$fields=$this->fields;
			$fromQuery=false;
		}else{
			$fields=$this->query->getFieldsForTable();
			$fromQuery=true;
		}
		$this->modelFields=$this->query->getModelFields();
		
		$this->fields=array();
		foreach($fields as $key=>&$val){
			if($fromQuery || is_string($val)){ $key=$val; $val=array(); }
			if(is_int($key)){
			}else{
				$val['key']=$key;
				if($this->fieldsEditable !==null && isset($this->fieldsEditable[$key])) $val['editable']=$this->fieldsEditable[$key];
	
				$modelName=&$this->modelFields[$key];
				if($modelName !== NULL){
					$propDef=&$modelName::$__PROP_DEF[$key];
					if($propDef===null){
						$type=isset($val['type']) ? $val['type'] : 'string';
					}else{
						$type=$propDef['type'];
						
						if(isset($propDef['annotations']['Enum'])){
							$val['tabResult']=call_user_func(array($modelName,$propDef['annotations']['Enum'].'List')); //TODO ou $modelName->{$propDef['annotations']['Enum'].'List'}() ?
							$val['align']='center';
						}elseif(!isset($val['callback'])){
							if(isset($propDef['annotations']['Format'])) $val['callback']=array('HFormat',$propDef['annotations']['Format']);
						}
					}
				}else $type='string';
				
				if(isset($this->belongsToFields[$key]) && is_array($this->belongsToFields[$key]))
					$val['tabResult']=$val['filter']=$this->belongsToFields[$key];
				
				if(isset($val['tabResult']) || isset($val['callback'])) $type='string';
				$val['type']=$type;
				
				if(!isset($val['title'])) $val['title']=$this->translateField?_tF(isset($this->belongsToFields[$key])?$this->modelName:$modelName,$key):$key;
				if(!isset($val['align'])) switch($type){
					case 'int'; case 'boolean':
						$val['align']='center';
						break;
				}
				if($export===false){
					if($type==='int'){
						if(!isset($val['widthPx']) && !isset($val['width%'])) $val['widthPx']='60';
					}elseif($type==='boolean'){
						if(!isset($val['icons'])) $val['icons']=array(false=>'disabled',true=>'enabled',''=>'enabled');
						if(!isset($val['widthPx']) && !isset($val['width%'])) $val['widthPx']='25';
						$val['filter']=array('1'=>_tC('Yes'),'0'=>_tC('No'));
					}elseif($type==='float'){
						if(!isset($val['widthPx']) && !isset($val['width%'])) $val['widthPx']='130';
					}elseif($modelName !== NULL && isset($modelName::$__modelInfos['columns'][$key])){
						$infos=$modelName::$__modelInfos['columns'][$key];
						if($infos['type']==='datetime'){
							if(!isset($val['widthPx']) && !isset($val['width%'])) $val['widthPx']='160';
						}
					}
					if(isset($val['icons']) && $val['icons']){
						$tabResult=array();
						foreach($val['icons'] as $key=>&$icon) $tabResult[$key]='<span class="icon '.$icon.'"></span>';
						$val['tabResult']=$tabResult;
						$val['escape']=false;
					}
				}
	
				
				
				/*
				if(isset($field['icons']) && $field['icons'] && isset($field['icons'][$value]))
					$value=HHtml::img($field['icons'][$value]);
				//TODO : class instead
				*/
				
				if(!isset($val['escape'])){
					$val['escape']=$type==='string';
				}
			}
			$this->fields[]=$val;
		}
	}

	public $editablePkField,$editableUrl;
	public function displayEditable($pkField,$url,$displayTotalResults=true){
		/* DEV */ if($this->query->isFiltersAllowed()) throw new Exception('Filters are not allowed for editable tables.'); /* /DEV */
		/* DEV */ if($this->query->isExportable()) throw new Exception('Exports are not allowed for editable tables.'); /* /DEV */
		
		$this->editablePkField=&$pkField;
		$this->editableUrl=&$url;
		$this->display($displayTotalResults,'THtmlEditable');
	}
	
	public function display($displayTotalResults=true,$transformerClass='THtml'){
		$pagination=$this->query->getPagination();
		$results=$pagination->getResults();
		
		if($pagination->getTotalResults() !== 0 || $this->query->isFiltersAllowed()) $this->_setFields();
		
		if($this->controller===null && ($this->actionClick!==null || $this->rowActions!==null))
			$this->controller=lcfirst(CRoute::getController());
		
		
		if($this->query->isFiltersAllowed()){
			$formId=uniqid();
			$form=HForm::create(NULL,array('id'=>$formId,'rel'=>'content'),false,false);
		}
		
		
		if($this->query->isExportable()){
			echo '<span class="exportLinks">'; 
			foreach($this->query->getExportableTypes() as $exportType)
				echo HHtml::iconAction('page_'.$exportType,'?export='.$exportType,array('target'=>'_blank'));//target : springbok.ajax
			echo '</span>';
		}
		
		if($this->query->isFiltersAllowed()) echo '<div class="filterHelp">'.$form->submit(_tC('Filter')).' (<i>'._tC('filter.help').'</i>)</div>';
		
		if($pagination->hasPager()){
			if($this->query->isFiltersAllowed()){
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
				$this->query->isFiltersAllowed()?function($page) use(&$idPage,&$formId){
					return ' href="#" onclick="return changePage('.$page.');"';
				}:function($page) use(&$href){
					return ' href="'.$href.'page='.$page.'"';
				},3,3,null,null).'</div>';
		}else $pager='';
		
		if(!empty($results) && $displayTotalResults===true)
			echo '<div class="totalResults">'.$pagination->getTotalResults().' '.($pagination->getTotalResults()===1?_tC('result'):_tC('results')).'</div>';
		
		$transformer=new $transformerClass($this);
		if(!$this->query->isFiltersAllowed() && empty($results)){
			$transformer->startBody();
			$transformer->noResults();
		}else{
			$transformer->startHead();
			$transformer->titles($this->fields,$this->query->getFields());
			if($this->query->isFiltersAllowed()) $transformer->filters($form,$this->fields,$this->query->getFilters());
			$transformer->endHead();
			$transformer->startBody();
			empty($results) ? $transformer->noResults(count($this->fields)) : $transformer->displayResults($results,$this->fields);
		}
		$transformer->end();
		if($this->query->isFiltersAllowed()) $form->end(false);
		echo $pager;
	}
	
	
	public function export($type,$fileName,$title,$exportOutput){
		set_time_limit(120); ini_set('memory_limit', '768M'); //TXls use 512M memory cache	
		$transformerClass=$this->transformers[$type];
		
		if($exportOutput===null){
			header('Content-Description: File Transfer');
			header("Content-Disposition: attachment; filename=".date('Y-m-d')."_".$fileName.".".$type);
			Controller::noCache();
			header("Content-type: ".$transformerClass::getContentType());
			while(ob_get_level()!==0) ob_end_clean();
		}
		
		
		$transformer=new $transformerClass($this);
		$transformer->startHead();
		
		$component=&$this; $query=&$this->query->noCalcFoundRows();
		$query->callback(function(&$f) use(&$component,&$transformer,&$query){
			$component->_setFields(true);
			$transformer->titles($component->fields,$query->getFields());
			$transformer->endHead();
			$transformer->startBody();
		},function(&$row) use(&$component,&$transformer){
			$transformer->row($row,$component->fields);
		});
		
		if($exportOutput!==null) $transformer->toFile($exportOutput);
		else $transformer->display();
	}
}