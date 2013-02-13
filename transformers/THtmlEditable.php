<?php
class THtmlEditable extends THtml{
	
	protected $form,$jsonPkValue,$modelName,$currentFormModelName;
	
	public function __construct($component){
		/*echo */$this->form=new HForm($this->currentFormModelName=$this->modelName=$component->getModelName(),null,'get','div',false,false);
		HHtml::jsReady('window.editableTable=new S.HTableEditable('.json_encode(HHtml::url($component->editableUrl)).')');
		parent::__construct($component);
	}
	public function end(){
		parent::end();
		echo $this->form->end(false);
	}
	
	public function startLine($iRow,$model,$id){
		if($id===null) $id=$model->id();
		$this->jsonPkValue=json_encode($id);
		parent::startLine($iRow,$model,$id);
	}
	
	public function displayValue($field,$value,$obj){
		if(isset($field['editable']) && $field['editable']) $field['escape']=false;
		parent::displayValue($field,$value,$obj);
	}
	
	public function isEditable($field,$value,$obj){
		return isset($field['editable']) && $field['editable'];
	}
	
	public function getDisplayableValue($field,$value,$obj){
		if($this->isEditable($field,$value,$obj)){
			$name=$fieldKey=$field['key'];
			if(!isset($field['editable'])){
				$modelName=$this->modelName;
			}elseif(is_array($field['editable'])){
				$modelName=$field['editable'][0];
				$fieldKey=$field['editable'][1];
			}else{
				$modelName=is_string($field['editable']) ? $field['editable'] : $this->modelName;
			}
			
			//<input type="text" value="'.h($value).'" style="width:98%" onchange=""/>
			$def=$modelName::$__PROP_DEF[$fieldKey];
			$infos=$modelName::$__modelInfos['columns'][$fieldKey];
			
			$attributes=array('id'=>$modelName.'_'.$name.'_'.$this->jsonPkValue,
				'onchange'=>'editableTable.updateField(\''.$name.'\','.$this->jsonPkValue.',this)','value'=>$value);
			$containerAttributes=array('sytle'=>'width:100%;position:relative');
			
			if($this->currentFormModelName!==$modelName)
				$this->form->setModelName($this->currentFormModelName=$modelName);
			
			if(substr($name,-3)==='_id' && Controller::_isset($vname=UInflector::pluralize(substr($name,0,-3))))
				return $this->form->select($name,Controller::get($vname));
			elseif($def['type']==='boolean'){
				$attrs=$attributes;
				if($value==='') $attrs['checked']=true;
				return $this->form->checkbox($name,false,$attrs,$containerAttributes);
			}elseif(isset($def['annotations']['Enum'])) return $this->form->select($name,call_user_func($modelName.'::'.$def['annotations']['Enum'].'List'),
																	array('onchange'=>$attributes['onchange'],'selected'=>$value),$containerAttributes);
			elseif(isset($def['annotations']['Text'])) return $thisform->textarea($name,$attributes,$containerAttributes);
			else return $this->form->input($name,$attributes+array('style'=>'width:98%'),$containerAttributes,1.4);
		}
		return parent::getDisplayableValue($field,$value,$obj);
	}
}
