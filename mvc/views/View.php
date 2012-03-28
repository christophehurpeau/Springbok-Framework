<?php
class View{
	private $layout,$vars/*,$active*/;
	
	public function __construct($title=false,$layout=null){
		if($layout===null) $layout=Controller::$defaultLayout;
		$this->vars=Controller::getLayoutVars();
		$this->vars['layout_title']=$title !==false ? $title : ucfirst(CRoute::getAction());
		$this->layout=$layout;
		ob_start();
	}
	
	public function ajaxHeaders($title,$to){
		if($title!==false){
			header('SpringbokAjaxTitle: '.json_encode($title));
			header('SpringbokAjaxTo: '.$to);
		}
	}
	
	public function set($name,$value=null){
		$this->vars[$name]=$value;
	}
	
	public function mset($vars){
		$this->vars=$vars+$this->vars;
	}
	
	public function set_($name,&$value){
		$this->vars[$name]=$value;
	}
	
	public function &get($name){
		return $this->vars[$name];
	}

	public function render(){
		$this->vars['layout_content']=ob_get_clean();
		render(APP.'viewsLayouts/'.$this->layout.'.php',$this->vars);
	}
	
	public static function element($name,$vars){
		return render(APP.'viewsElements/'.$name.'.php',$vars,true);
	}
}

class MailView extends View{ public function __construct($layout='mails'){ parent::__construct('',$layout); } }
class BaseView extends View{ public function __construct($title=false,$layout=null){ if($layout===null) $layout=Springbok::$prefix.'base'; parent::__construct($title,$layout); } }
class PageView extends View{ public function __construct($title=false,$layout=null){ if($layout===null) $layout=Springbok::$prefix.'page'; parent::__construct($title,$layout); } }


class AjaxView extends View{
	protected $active;
	public function __construct($title=false,$layout=null){
		if(($this->active= !CHttpRequest::isAjax())===TRUE)
			parent::__construct($title,$layout);
	}
	public function render(){
		if($this->active===true) parent::render();
	}
}

class AbstractAjaxView extends View{
	protected $active;
	public function render(){
		if($this->active===true){
			echo '</div>';
			parent::render();
		}
	}
}

class AjaxBaseView extends AbstractAjaxView{
	public function __construct($title=false,$layout=null,$layoutNameOverride=null){
		if($layout===null) $layout=Springbok::$prefix.'base';
		if($layoutNameOverride===null) $layoutNameOverride=$layout;
		if(CSecure::isConnected()) $layoutNameOverride.=CSecure::connected();
		if(($this->active= !isset($_SERVER['HTTP_SPRINGBOKAJAXPAGE']))===true){
			parent::__construct($title,$layout);
			echo '<div id="container" data-layoutname="'.$layoutNameOverride.'">';
		}else $this->ajaxHeaders($title,'base');
	}
}

class AjaxPageView extends AbstractAjaxView{
	public function __construct($title=false,$class='ml200',$layout=null,$layoutNameOverride=null){
		if($layout===null) $layout=Springbok::$prefix.'page';
		if($layoutNameOverride===null) $layoutNameOverride=$layout;
		if(CSecure::isConnected()) $layoutNameOverride.=CSecure::connected();
		if(($this->active= !isset($_SERVER['HTTP_SPRINGBOKAJAXPAGE'])||$_SERVER['HTTP_SPRINGBOKAJAXPAGE']!==$layoutNameOverride)===true){
			parent::__construct($title,$layout);
			echo '<div id="page" class="'.$class.'" data-layoutname="'.$layoutNameOverride.'">';
		}else{
			//if(isset($_SERVER['SPRINGBOKBREADCRUMBS'])) header('SpringbokAjaxBreadcrumbs: '.HBreadcrumbs::toJs($title));
			$this->ajaxHeaders($title,'page');
			header('SpringbokAjaxPageClass: '.$class);
		}
		echo static::pre_content($title);
	}
	protected static function pre_content(&$layout_title){ return ''; }
	protected static function post_content(){ return ''; }
}
class AjaxBreadcrumbsPageView extends AjaxPageView{
	protected static function pre_content(&$layout_title){ HBreadcrumbs::display(_tC('Home'),$layout_title); return ''; }
}
class AjaxPageDynamicTabsView extends AjaxPageView{
	public function __construct($title=false,$class='ml200',$layout=null,$layoutNameOverride=null){
		if($layout===null) $layout=Springbok::$prefix.'page';
		parent::__construct($title,$class,$layout,$layoutNameOverride);
		echo '<menu class="dynamictabs top"></menu><div id="dynamictabsContent" class="clear">';
	}
	public function render(){
		echo '</div>';
		parent::render();
	}
}

class AjaxContentView extends AbstractAjaxView{
	private $title;
	public function __construct($title=false,$layout=null,$layoutNameOverride=null){
		if($layout===null) $layout=Controller::$defaultLayout;
		if($layoutNameOverride===null) $layoutNameOverride=Controller::$defaultLayoutOverride;
		if($layoutNameOverride===null) $layoutNameOverride=$layout;
		if(CSecure::isConnected()) $layoutNameOverride.=CSecure::connected();
		if(($this->active= !isset($_SERVER['HTTP_SPRINGBOKAJAXCONTENT'])||$_SERVER['HTTP_SPRINGBOKAJAXCONTENT']!==$layoutNameOverride)===true){
			parent::__construct($title,$layout);
			echo '<div class="content" data-layoutname="'.$layoutNameOverride.'">';
		}else{
			$this->ajaxHeaders($this->title=$title,'content');
		}
	}
	public function render(){
		if($this->active===true) parent::render();
		else if(isset($_SERVER['HTTP_SPRINGBOKBREADCRUMBS'])) header('SpringbokAjaxBreadcrumbs: '.HBreadcrumbs::toJs($this->title));
	}
}

