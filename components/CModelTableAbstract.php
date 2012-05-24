<?php
class CModelTableAbstract{
	protected $query;
	public function __construct($query){
		$this->query=&$query;
	}
	
	public $fields,$modelFields,$fieldsEditable,$translateField=true,
		$transformers=array('csv'=>'TCsv','xls'=>'TXls');
	
	public function &fields($fields){$this->fields=&$fields; return $this; }
	public function &doNotTranslateFields(){ $this->translateField=false; return $this; }
	public function &fieldsEditable($fields){ $this->fieldsEditable=&$fields; return $this; }
	
	public function &actionClick($action='view'){ return $this; }
	public function &actions($actions){ return $this; }
	public function &controller($controller){ return $this; }
	
	public function getModelName(){ return $this->query->getModelName(); }
	
	public function &displayIfExport(){ return $this; }
	
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
}