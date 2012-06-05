<?php
class CModelTable extends CModelTableAbstract{
	public $actionClick,$rowActions,$controller;
	
	/* !!! => CModelTableAbstract */
	public function &actionClick($action='view'){$this->actionClick=&$action; return $this; }
	public function &actions($actions){$this->rowActions=func_get_args(); return $this; }
	public function &setActions($actions){$this->rowActions=&$actions; return $this; }
	public function &addAction($action){$this->rowActions[]=&$action; return $this; }
	public function &controller($controller){$this->controller=&$controller; return $this; }
	
	public function &setActionsRUD(){
		$this->actionClick='view';
		$this->rowActions=array('view','edit','delete');
		return $this;
	}
	
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
			if($this->query->isFiltersAllowed()) $transformer->filters($form,$this->fields,$this->query->getFilters(),$this->query->isFilterAdvancable());
			$transformer->endHead();
			$transformer->startBody();
			empty($results) ? $transformer->noResults(count($this->fields)) : $transformer->displayResults($results,$this->fields);
		}
		$transformer->end();
		if($this->query->isFiltersAllowed()) $form->end(false);
		echo $pager;
	}
}