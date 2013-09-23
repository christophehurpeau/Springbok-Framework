<?php
/** Common class for export and html tables */
class CModelTableAbstract{
	protected $query;
	public function __construct($query){
		$this->query=$query;
	}
	
	public $fields,$modelFields,$fieldsEditable,$translateField=true,
		$transformers=array('csv'=>'TCsv','xls'=>'TXls');
	
	/**
	 * Fields : columns in the table
	 * 
	 * @param array
	 * @return CModelTableAbstract
	 */
	public function fields($fields){
		$this->fields=$fields;
		return $this;
	}
	/**
	 * Force to not translate fields
	 * 
	 * @return CModelTableAbstract
	 */
	public function doNotTranslateFields(){
		$this->translateField=false;
		return $this;
	}
	
	/**
	 * Set the editable fields
	 * 
	 * @param array
	 * @return CModelTableAbstract
	 */
	public function fieldsEditable($fields){
		$this->fieldsEditable=$fields;
		return $this;
	}
	
	/** Method overrided by subclass */
	public function actionClick($action='view'){ return $this; }
	/** Method overrided by subclass */
	public function actions(){ return $this; }
	/** Method overrided by subclass */
	public function controller($controller){ return $this; }
	
	/**
	 * Return the model name of this table
	 * 
	 * @return string
	 */
	public function getModelName(){
		return $this->query->getModelName();
	}
	/**
	 * Return if the table can be filtrable with selects and inputs
	 * 
	 * @return bool
	 */
	public function isFiltersAllowed(){
		return $this->query->isFiltersAllowed();
	}
	
	/**
	 * Return if the table can be ordered with arrows icons in titles
	 * 
	 * @return bool
	 */
	public function isOrderAllowed(){
		return $this->query->isOrderAllowed();
	}
	
	/**
	 * Return if the table can be exported in xls, csv, ...
	 * 
	 * @return bool
	 */
	public function isExportable(){
		return $this->query->isExportable();
	}
	
	/**
	 * @return bool
	 */
	public function mustDisplayTable(){
		return $this->query->mustDisplayTable();
	}
	
	/**
	 * @return bool
	 */
	public function hasForm(){
		return $this->query->hasForm();
	}
	
	/**
	 * Return if the form is inside the table
	 * 
	 * @return bool
	 */
	public function hasAddInTable(){
		return $this->query->hasAddInTable();
	}
	
	/**
	 * @return mixed
	 */
	public function getAddInTable(){
		return $this->query->getAddInTable();
	}
	
	public function displayIfExport(){ return $this; }
	
	
	/**
	 * Set the fields from the model, defined in the query, and set in this class
	 * 
	 * @param mixed
	 */
	public function _setFields($export=false){
		if($this->fields !== null){
			$fields=$this->fields;
			$fromQuery=false;
		}else{
			$fields=$this->query->getFieldsForTable();
			$fromQuery=true;
		}
		$this->modelFields=$this->query->getModelFields();
		
		$this->fields=array(); $belongsToFields=$this->query->getBelongsToFields();
		foreach($fields as $key=>&$val){
			if($fromQuery || is_string($val)){ $key=$val; $val=array(); }//debug([$key,$val,$belongsToFields[$key]]);
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
							if(!isset($val['filter'])) $val['filter']=$val['tabResult'];
							if(isset($propDef['annotations']['Icons'])&&!isset($val['icons'])) $val['icons']=$propDef['annotations']['Icons'];
						}elseif(!isset($val['callback'])){
							if(isset($propDef['annotations']['Format'])) $val['callback']=array('HFormat',$propDef['annotations']['Format']);
						}
					}
				}else $type='string';
				
				if(isset($belongsToFields[$key]) && is_array($belongsToFields[$key]))
					$val['tabResult']=$val['filter']=$belongsToFields[$key];
				
				if(isset($val['tabResult']) || isset($val['callback'])) $type='string';
				$val['type']=$type;
				
				if(!isset($val['title'])) $val['title']=($this->translateField?_tF(isset($belongsToFields[$key])?$this->getModelName():$modelName,$key):$key);
				if(!isset($val['align'])) switch($type){
					case 'int'; case 'boolean':
						$val['align']='center';
						break;
				}
				if($export===false){
					$infos=$modelName !== NULL && isset($modelName::$__modelInfos['columns'][$key]) ? $modelName::$__modelInfos['columns'][$key] : null;
					if(!isset($val['required'])) $val['required']=$infos['notnull']===true;
					if($type==='int'){
						if(!isset($val['widthPx']) && !isset($val['width%'])) $val['widthPx']='60';
					}elseif($type==='boolean'){
						if(!isset($val['icons'])) $val['icons']=array(false=>'disabled',true=>'enabled',''=>'enabled');
						if(!isset($val['widthPx']) && !isset($val['width%'])) $val['widthPx']='25';
						$val['filter']=array('1'=>_tC('Yes'),'0'=>_tC('No'));
					}elseif($type==='float'){
						if(!isset($val['widthPx']) && !isset($val['width%'])) $val['widthPx']='130';
					}elseif($infos!==null){
						if($infos['type']==='datetime'){
							if(!isset($val['widthPx']) && !isset($val['width%'])) $val['widthPx']='160';
						}
					}
					if(isset($val['icons']) && $val['icons']){
						$tabResult=array();$titleIcons=isset($val['tabResult'])?$val['tabResult']:false;
						foreach($val['icons'] as $key=>&$icon)
							$tabResult[$key]='<span class="icon '.$icon.'"'
									.($titleIcons===false||!isset($titleIcons[$key])?'':' title="'.h($titleIcons[$key]).'"').'></span>';
						$val['tabResult']=$tabResult;
						$val['escape']=false;
						$val['align']='center';
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