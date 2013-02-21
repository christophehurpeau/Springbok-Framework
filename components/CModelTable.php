<?php
class CModelTable extends CModelTableAbstract{
	public $actionClick,$rowActions,$controller,$afterContent='';
	
	/* !!! => CModelTableAbstract */
	public function actionClick($action='view'){$this->actionClick=$action; return $this; }
	public function actions(){$this->rowActions=func_get_args(); return $this; }
	public function setActions($actions){$this->rowActions=$actions; return $this; }
	public function addAction($action){$this->rowActions[]=$action; return $this; }
	public function controller($controller){$this->controller=$controller; return $this; }
	
	
	public function addAfter($content){$this->afterContent.=$content; return $this; }
	
	public function setActionsRUD($iconPrefix='',$confirm=true){
		self::actionView($iconPrefix);
		self::actionEdit($iconPrefix);
		self::actionDelete($iconPrefix,$confirm);
		return $this;
	}
	public function setActionsRU($iconPrefix=''){
		self::actionView($iconPrefix);
		self::actionEdit($iconPrefix);
		return $this;
	}
	public function setActionsUD($iconPrefix='',$confirm=true){
		$this->actionClick='edit';
		self::actionEdit($iconPrefix);
		self::actionDelete($iconPrefix,$confirm);
		return $this;
	}
	public function setActionsRD($iconPrefix='',$confirm=true){
		self::actionView($iconPrefix);
		self::actionDelete($iconPrefix,$confirm);
		return $this;
	}
	public function actionView($iconPrefix=''){
		$this->actionClick='view';
		$this->rowActions[]=array($iconPrefix.($iconPrefix===''?'view':'View'),'title'=>_tC('View'));
		return $this;
	}
	public function actionEdit($iconPrefix=''){
		$this->rowActions[]=array($iconPrefix.($iconPrefix===''?'edit':'Edit'),'title'=>_tC('Modify'));
		return $this;
	}
	public function actionDelete($iconPrefix='',$confirm=true){
		$options=array($iconPrefix.($iconPrefix===''?'delete':'Delete'),'title'=>_tC('Delete'));
		if($confirm===true) $options['data-confirm']="1";
		elseif($confirm) $options['data-confirm']=$confirm;
		$this->rowActions[]=$options;
		return $this;
	}
	
	public function render($title,$add=false,$layout=null,$transformerClass='THtml'){
		include_once CORE.'mvc/views/View.php';
		$v=new AjaxContentView($title,$layout);
		$this->_add($add);
		$this->display(true,$transformerClass);
		$v->render();
		return $this;
	}
	
	public function renderEditable($title,$url,$add=false,$layout=null,$transformerClass='THtml'){
		include_once CORE.'mvc/views/View.php';
		$v=new AjaxContentView($title,$layout);
		$this->_add($add);
		$this->displayEditable($url,true,$transformerClass);
		$v->render();
		return $this;
	}

	public function renderTransformer($transformerClass,$title,$add=false,$layout=null){
		return $this->render($title,$add,$layout,$transformerClass);
	}
	
	private function _add($add){
		if($add===false) return;
		if($add===true) $add=array('modelName'=>$this->getModelName());
		elseif(is_string($add)) $add=array('modelName'=>$add);
		elseif(is_object($add) && $add instanceof Closure) return $add();
		elseif(!isset($add['modelName'])) $add['modelName']=$this->getModelName();
		if(!isset($add['form']['action'])) $add['form']['action']='/'.lcfirst($add['modelName']::$__pluralized).'/add';
		if(!isset($add['formContainer'])) $add['formContainer']=false;
		if(!isset($add['fields'])) $add['fields']=array($add['modelName']::$__displayField=>_tF($add['modelName'],'New').' :');
		$form=HForm::create($add['modelName'],$add['form'],$add['formContainer']);
		foreach($add['fields'] as $field=>$label)
			echo ' '.$form->autoField($field,array('label'=>$label));
		echo $form->end(_tC('Add'));
	}
	
	public $editableUrl;
	public function displayEditable($url,$displayTotalResults=true,$transformerClass='THtmlEditable'){
		/* DEV */ if($this->isFiltersAllowed()) throw new Exception('Filters are not allowed for editable tables.'); /* /DEV */
		/* DEV */ if($this->isExportable()) throw new Exception('Exports are not allowed for editable tables.'); /* /DEV */
		
		$this->editableUrl=$url;
		$this->display($displayTotalResults,$transformerClass);
	}
	
	public function display($displayTotalResults=true,$transformerClass='THtml'){
		$pagination=$this->query->getPagination();
		$results=$pagination->getResults();
		
		if($pagination->getTotalResults() !== 0 || $this->mustDisplayTable()) $this->_setFields();
		
		$this->initController();
		
		if($this->hasForm()){
			$formId=uniqid();
			echo $form=HForm::Post()->id($formId)->noContainer()->noDefaultLabel();
		}else $form=null;
		
		
		if($this->isExportable()){
			echo '<div class="exportLinks">'; 
			foreach($this->query->getExportableTypes() as $exportType)
				echo HHtml::iconAction('file'.ucfirst($exportType),'?export='.$exportType,array('target'=>'_blank'));//target : springbok.ajax
			echo '</div>';
		}
		
		//if($this->isFiltersAllowed()) echo '<div class="filterHelp">'.$form->submit(_tC('Filter')).' (<i>'._tC('filter.help').'</i>)</div>';
		
		if($pagination->hasPager()){
			if($this->hasForm()){
				$idPage='page'.$formId;
				echo '<input id="'.$idPage.'" type="hidden" name="page"/>'.HHtml::jsInline('var changePage=function(num){$(\'#'.$idPage.'\').val(num);$(\'#'.$formId.'\').submit();return false;}');
			}else{
				$hrefQuery='';
				if(!empty($_POST)){
					$post=$_POST;
					unset($post['page'],$post['add']);
					$hrefQuery.=http_build_query($_POST,'','&').'&';
				}
				if(!empty($_GET)){
					$get=$_GET;
					unset($get['page'],$get['ajax'],$get['add']);
					if(!empty($get)) $hrefQuery.=http_build_query($get,'','&').'&';
				}
				$href=HHtml::urlEscape(array(true,CRoute::getAll(),'?'=>$hrefQuery));
			}
			echo $pager='<div class="pager">'.HPagination::createPager($pagination->getPage(),$pagination->getTotalPages(),
				$this->hasForm()?function($page){
					return ' href="#" onclick="return changePage('.$page.');"';
				}:function($page) use($href){
					return ' href="'.$href.'page='.$page.'"';
				},3,3,null,null).'</div>';
		}else $pager='';
		
		if(!empty($results) && $displayTotalResults===true)
			echo '<div class="totalResults">'.$pagination->getTotalResults().' '.($pagination->getTotalResults()===1?_tC('result'):_tC('results')).'</div>';
		
		$this->callTransformer($transformerClass,$results,$form);
		if($form!==null) $form->end(false);
		echo $pager.$this->afterContent;
	}
	protected function initController(){
		if($this->controller===null && ($this->actionClick!==null || $this->rowActions!==null))
			$this->controller=lcfirst(CRoute::getController());
	}

	protected function callTransformer($transformerClass,$results,$form=null){
		$transformer=new $transformerClass($this);
		if(empty($results) && !$this->mustDisplayTable()){
			$transformer->startBody();
			$transformer->noResults();
		}else{
			$transformer->startHead();
			$transformer->titles($this->fields,$this->query->getFields());
			if($this->isFiltersAllowed()) $transformer->filters($form,$this->fields,$this->query->getFilters(),$this->query->isFilterAdvancable());
			elseif($this->hasAddInTable()) $transformer->addInTable($form,$this->fields);
			$transformer->endHead();
			$transformer->startBody();
			if(empty($results)) $transformer->noResults(count($this->fields));
			else $transformer->displayResults($results,$this->fields);
		}
		$transformer->end();
	}
	
	
	
	
	/* ----- PLUS ----- */
	
	
	public function jsActions(){
		return new HTableJsActions($this);
	}
}