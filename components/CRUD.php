<?php
class CRUD{
	public static function view($model,$id,$tableOptions=array(),$relations=array(),$renderView=true){
		$title=_tC('View:').' '.$id.' - '.$model;
		if($renderView){
			include_once CORE.'mvc/views/View.php';
			$v=new AjaxContentView($title);
		}
		
		$table=CTableOne::create($model::ById($id),true);
		foreach($tableOptions as $k=>&$val) $table->$k=$val;
		HTable::table($table,false);
		
		$obj=$table->getResult();
		if(!empty($relations)){
			$with=array();
			foreach($relations as $key=>&$options){
				if(is_numeric($key)){ $key=$options; $options=array();}
				QFind::_addWith($with,$key,$options,$model);
			};
			
			foreach($with as $key=>$w){
				echo '<h5 class="sepTop">'.$w['title'].'</h5>';
				$table=CTable::create(QFind::createWithQuery($obj,$w));
				if(isset($w['table'])) foreach($w['table'] as $k=>&$val) $table->$k=$val;
				HTable::table($table,false);
			}
		}
		
		if($renderView) $v->render();
	}
	
	public static function edit($model,$id,$fields=null,$renderView=true){
		$DATA=null;
		if(!empty($_POST)) $DATA=&$_POST;
		elseif(!empty($_GET)) $DATA=&$_GET;
		if(!empty($DATA)){
			$pName=lcfirst($model); $data=&$DATA[$pName];
			foreach($data as $key=>&$val) if($val==='') $val=null;
			$val=CBinder::_bindObject($model,$data,$pName,false);
			$val->id=$id;
			$val->update();
			Controller::redirect('/'.lcfirst(CRoute::getController()));
		}
		if(($val=$model::findOneById($id))===false) notFound();
		
		if($renderView){
			$title=_tC('Edit:').' '.$id.' - '._tF($model,'');
			include_once CORE.'mvc/views/View.php';
			$v=new AjaxContentView($title);
		}
		$form=HForm::create($model,array('id'=>'formCrud'.$model),'div',array('setValuesFromVar'=>false));
		$data=$val->_getData();
		$_POST[lcfirst($model)]=&$data;
		echo $form->fieldsetStart($title);
		$form->all();
		echo $form->end();
		echo HHtml::jsInline('$("#formCrud'.$model.'").ht5ifv();');
		if($renderView) $v->render();
	}
	
	public static function delete($model,$id){
		$model::QDeleteOne()->byId($id);
	}
	
	public static function setDeleted($model,$id){
		$model::QUpdateOneField('deleted',true)->byId($id);
	}
}
