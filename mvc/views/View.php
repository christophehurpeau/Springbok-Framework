<?php
/**
 * View Class
 */
class View{
	protected $layout,$vars/*,$active*/;
	
	/**
	 * @param string|false
	 * @param string|null
	 */
	public function __construct($title=false,$layout=null){
		if($layout===null) $layout=Controller::$defaultLayout;
		$this->vars=Controller::getLayoutVars();
		$this->vars['layout_title']=$title !==false ? $title : ucfirst(CRoute::getAction());
		$this->layout=$layout;
		ob_start();
	}
	
	/**
	 * Set the layout title
	 * @param string
	 * @return void
	 */
	public function layoutTitle($layoutTitle){
		$this->vars['layout_title']=$layoutTitle;
	}
	
	/**
	 * @param string
	 * @param string
	 * @return void
	 */
	public function ajaxHeaders($title,$to){
		if($title!==false){
			header('SpringbokAjaxTitle: '.json_encode($title));
			header('SpringbokAjaxTo: '.$to);
		}
		header('SpringbokAppVersion: '.APP_VERSION);
		HHtml::displayJsHead();
	}
	
	/**
	 * Set a values for the layout
	 * 
	 * @param string
	 * @param mixed
	 * @return void
	 */
	public function set($name,$value=null){
		$this->vars[$name]=$value;
	}
	
	/**
	 * Set multiple values for the layout
	 * 
	 * @param string
	 * @param mixed
	 * @return void
	 */
	public function mset($vars){
		$this->vars=$vars+$this->vars;
	}
	
	/**
	 * Set a value by reference
	 * 
	 * @param string
	 * @param mixed
	 * @return void
	 */
	public function set_($name,&$value){
		$this->vars[$name]=$value;
	}
	
	/**
	 * Return a value from the layout vars
	 * 
	 * @param string
	 * @return mixed
	 */
	public function get($name){
		return $this->vars[$name];
	}
	
	/**
	 * Render the layout
	 * This function is appended at the end of each views
	 * 
	 * @return void
	 * 
	 */
	public function render(){
		$this->vars['layout_content']=ob_get_clean();
		render(APP.'viewsLayouts/'.$this->layout.'.php',$this->vars);
	}
	
	/**
	 * Render an element and returns the result
	 * 
	 * @param string
	 * @param array variables for the element
	 * @return string
	 */
	public static function element($name,$vars){
		return render(APP.'viewsElements/'.$name.'.php',$vars,true);
	}
	
	/**
	 * return the content of a file in the DATA folder
	 * 
	 * @return string
	 */
	public static function fromData($filename){
		try{
			return file_get_contents(DATA.$filename);
		}catch(ErrorException $e){}
		return '';
	}
	
	/**
	 * include a file in the DATA folder
	 * 
	 * @return void
	 */
	public static function includeFromData($filename){
		try{
			include DATA.$filename;
		}catch(ErrorException $e){}
	}
}

/**
 * A View for mails
 * 
 * Use the layout 'mails' by default
 */
class MailView extends View{
	/**
	 * @param string
	 * @param string|null
	 */
	public function __construct($layout='mails',$title=null){
		$this->vars=array('layout_title'=>$title);
		$this->layout=$layout;
		ob_start();
	}
}
/**
 * A base View
 * 
 * Use the layout base by default
 */
class BaseView extends View{ public function __construct($title=false,$layout=null){ if($layout===null) $layout=Springbok::$prefix.'base'; parent::__construct($title,$layout); } }
/**
 * A page View
 * 
 * Use the layout page by default
 */
class PageView extends View{ public function __construct($title=false,$layout=null){ if($layout===null) $layout=Springbok::$prefix.'page'; parent::__construct($title,$layout); } }

/**
 * An Ajax View
 * This view render the layout only if the request is not in ajax
 */
class AjaxView extends View{
	protected $active;
	public function __construct($title=false,$layout=null){
		if(($this->active= !CHttpRequest::isAjax())===TRUE)
			parent::__construct($title,$layout);
	}
	public function render(){
		if($this->active===true) parent::render();
		else HHtml::displayJsReady();
	}
}
/**
 * @internal
 */
abstract class AbstractAjaxView extends View{
	protected $active;
	public function render(){
		if($this->active===true){
			echo '</div>';
			parent::render();
		}else HHtml::displayJsReady();
	}
}

/**
 * Ajax Base View, for Ajax Loading (use springbok.ajax in you js file)
 */
class AjaxBaseView extends AbstractAjaxView{
	public function __construct($title=false,$layout=null,$layoutNameOverride=null,$attrs=array()){
		if($layout===null) $layout=Springbok::$prefix.'base';
		if($layoutNameOverride===null) $layoutNameOverride=$layout;
		if(CSecure::isConnected()) $layoutNameOverride.=CSecure::connected();
		if(($this->active= !isset($_SERVER['HTTP_SPRINGBOKAJAXPAGE']))===true){
			parent::__construct($title,$layout);
			echo '<div id="container" data-layoutname="'.$layoutNameOverride.'"'.HHtml::_attributes($attrs).'>';
		}else $this->ajaxHeaders($title,'base');
	}
}

/**
 * Ajax Page View, for Ajax Loading (use springbok.ajax in you js file)
 */
class AjaxPageView extends AbstractAjaxView{
	public function __construct($title=false,$class='ml200',$layout=null,$layoutNameOverride=null){
		if($layout===null) $layout=Springbok::$prefix.'page';
		if($layoutNameOverride===null) $layoutNameOverride=$layout;
		if(CSecure::isConnected()) $layoutNameOverride.=CSecure::connected();
		if(($this->active= !isset($_SERVER['HTTP_SPRINGBOKAJAXPAGE'])||$_SERVER['HTTP_SPRINGBOKAJAXPAGE']!==$layoutNameOverride)===true){
			parent::__construct($title,$layout);
			echo '<div id="page"'.($class===''?'':' class="'.$class.'"').' data-layoutname="'.$layoutNameOverride.'">';
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
/** @deprecated */
class AjaxBreadcrumbsPageView extends AjaxPageView{
	protected static function pre_content(&$layout_title){ HBreadcrumbs::display(_tC('Home'),$layout_title); return ''; }
}
/** @deprecated */
class AjaxPageDynamicTabsView extends AjaxPageView{
	public function __construct($title=false,$class='ml200',$layout=null,$layoutNameOverride=null){
		if($layout===null) $layout=Springbok::$prefix.'page';
		parent::__construct($title,$class,$layout,$layoutNameOverride);
		echo '<nav class="dynamictabs top"><ul></ul></nav><div id="dynamictabsContent" class="clear"><div id="dynamictab1">';
	}
	public function render(){
		echo '</div></div>';
		parent::render();
	}
}
/**
 * Ajax Content Page View, for Ajax Loading (use springbok.ajax in you js file)
 */
class AjaxContentPageView extends AjaxPageView{
	public function __construct($title=false,$layout=null,$layoutNameOverride=null){
		parent::__construct($title,'',$layout,$layoutNameOverride);
		echo '<div class="'.static::attrClass().'">';
	}
	public function render(){
		echo '</div>';
		parent::render();
	}
	protected static function attrClass(){
		return 'variable padding';
	}
}

/**
 * Ajax Content View, for Ajax Loading (use springbok.ajax in you js file)
 */
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
		else{
			HHtml::displayJsReady();
			if(isset($_SERVER['HTTP_SPRINGBOKBREADCRUMBS'])) header('SpringbokAjaxBreadcrumbs: '.HBreadcrumbs::toJs($this->title));
		}
	}
}
