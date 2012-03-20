<?php
class CModelTable{
	private $query,$result;
	public function __construct($query){
		$this->query=&$query;
	}
	
	private $actionClick,$rowActions,$export=false;
	
	public function actionClick($action='view'){$this->actionClick=&$action; return $this;}
	public function rowActions($actions){$this->rowActions=&$actions; return $this;}
	public function exports($exports){$this->exports=&$exports; return $this;}
	
	public function render($title,$add=false,$layout=null){
		include_once CORE.'mvc/views/View.php';
		$v=new AjaxContentView($title,$layout);
		self::_add($add);
		self::display();
		$v->render();
	}
	
	private static function _add($add){
		if($add!==false){
			if($add===true) $add=array('modelName'=>$this->query->getModelName());
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
	
	public function display($displayTotalResults=true){
		$pagination=$this->query->getPagination();
		$results=$pagination->getResults();
		
		if($this->query->isFiltersAllowed()){
			$formId=uniqid();
			$form=HForm::create(NULL,array('id'=>$formId,'rel'=>'content'),false,false);
		}
		
		
		if($this->export!==false){
			echo '<span class="exportLinks">'; 
			foreach(explode(',',$component->export[0]) as $exportType)
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
		
		echo '<table class="table">';
		if(!$this->query->isFiltersAllowed() && empty($results)) echo '<tr><td>'._tC('No result').'</td></td>';
		else{
			echo '<thead><tr>';
			echo '</tr>';
		
			echo '</thead><tbody>';
			
			echo '</tbody>';
		}
		echo '</table>';
		if($this->query->isFiltersAllowed()) $form->end(false);
		echo $pager;
	}
	
	
}