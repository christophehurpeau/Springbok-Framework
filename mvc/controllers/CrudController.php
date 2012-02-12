<?php
exit('depreated');
class CrudController extends Controller{
	public $modelName;
	
	public function index(){
		$modelName=$this->modelName;
		$this->set('data',$modelName::findAll()->execute());
		$this->render('index');
	}
	
	public function view($id){
		if(empty($id)) $this->redirect('/'.CRoute::getController());
		$obj=$modelName::findOne()->pk($id)->execute();
		if(empty($obj)){
			$this->redirect('/'.CRoute::getController(),false);
			notFound();
		}
		$this->render('view');
	}
	
	public function add(){
		$this->render('modify');
	}
	
	public function modify($id){
		$this->render('modify');
	}
	
	protected function render($actionName=null){
		$this->_render(CORE.'mvc'.DS.'views'.DS.'crud'.DS.$actionName.'.php');
	}
}