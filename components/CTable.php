<?php
class CTable{
	
	/**
	 * @return CTable
	 */
	public static function create($query){
		/* DEV */if(!($query instanceof QFindAll || $query instanceof QSql)) throw new Exception('Your query must be an instance of QFindAll'); /* /DEV */
		return new CTable($query);
	}
	
	public $modelName,$fields,$modelFields,$queryFields,$fieldsEditable,$rowActions,$defaultAction,$filter=false,$export=false,$translateField=true,$autoBelongsTo=true,$belongsToFields=array(),$controller,
		$FILTERS;
	protected $pagination,$query,$executed,$results,$totalResults;

	public function __construct($query){
		$this->modelName=$query->getModelName();
		$this->query=&$query;
	}
	
	
	public function execute($exportOutput=null){
		if($this->executed===true) return; $this->executed=true;
		$modelName=&$this->modelName;
		$this->queryFields=$fields=$this->query->getFields();
		if($fields===NULL) $fields=$modelName::$__modelInfos['colsName'];
		
		if($this->controller===null && ($this->defaultAction!==null || $this->rowActions!==null))
			$this->controller=lcfirst(CRoute::getController());
		
		$belongsToFields=&$this->belongsToFields; $belongsToRel=array();
		if($this->autoBelongsTo!==false){
			if($belongsToFields!==false && empty($this->belongsToFields)){
				foreach($modelName::$__modelInfos['relations'] as $relKey=>&$rel)
					if($rel['reltype']==='belongsTo' && in_array($rel['foreignKey'],$fields)) $belongsToFields[$rel['foreignKey']]=$relKey;
			}
			foreach($belongsToFields as $field=>$relKey){
				$belongsToRel[$field]=$modelName::$_relations[$relKey];
				$relModelName=$belongsToRel[$field]['modelName'];
				if($relModelName::$__cacheable) $belongsToFields[$field]=$relModelName::findCachedListName();
				elseif(is_array($this->autoBelongsTo) && isset($this->autoBelongsTo[$field]))
					$belongsToFields[$field]=$relModelName::QList()->setFields(array('id',$relModelName::$__displayField))->with($modelName,array('fields'=>false,'type'=>QFind::INNER,'forceJoin'=>true));
				else $this->query->with($relKey,array('fields'=>array($relModelName::$__displayField=>$field),'fieldsInModel'=>true));
			}
		}
		$SESSION_SUFFIX=$this->modelName.CRoute::getAll();
		
		if(isset($_GET['orderBy']) && in_array($_GET['orderBy'],$fields)){
			CSession::set('CTableOrderBy'.$SESSION_SUFFIX,$orderByField=$_GET['orderBy']);
			CSession::set('CTableOrderByWay'.$SESSION_SUFFIX,isset($_GET['orderByDesc'])?'DESC':'ASC');
		}else $orderByField=CSession::getOr('CTableOrderBy'.$SESSION_SUFFIX);
		
		if($orderByField !==null){
			if(isset($belongsToFields[$orderByField])){
				$rel=$belongsToRel[$orderByField];
				$relModelName=$rel['modelName'];
				$orderByField=$rel['alias'].'.'.$relModelName::$__displayField;
			}
			$this->query->orderBy(array($orderByField=>CSession::get('CTableOrderByWay'.$SESSION_SUFFIX)));
		}
		
		if($this->filter===true){
			$filter=false;
			if(!empty($_POST['filters'])){
				$this->FILTERS=$_POST['filters'];
				CSession::set('CTableFilters'.$SESSION_SUFFIX,$this->FILTERS);
			}elseif(!empty($_GET['filters'])){
				$this->FILTERS=$_GET['filters'];
				CSession::set('CTableFilters'.$SESSION_SUFFIX,$this->FILTERS);
			}else
				$this->FILTERS=CSession::getOr('CTableFilters'.$this->modelName.CRoute::getAll(),array());
			
			if(!empty($this->FILTERS)){
				foreach($fields AS $key=>$fieldName){
					if(isset($this->FILTERS[$fieldName]) && (!empty($this->FILTERS[$fieldName]) || $this->FILTERS[$fieldName]==='0')){
						$filter=true;
						
						$postValue=$this->FILTERS[$fieldName];
						if(isset($belongsToFields[$fieldName])){
							$rel=$belongsToRel[$fieldName];
							$relModelName=$rel['modelName'];
							$relFieldName=$relModelName::$__displayField;
							$condK=$rel['alias'].'.'.$relFieldName;
							
							$propDef=&$relModelName::$__PROP_DEF[$relFieldName];
							$type=$propDef['type'];
						}else{
							$condK=$fieldName;
							
							$propDef=&$modelName::$__PROP_DEF[$fieldName];
							$type=$propDef['type'];
						}
						$condV=CBinder::bind($type,$postValue);
						
						if(is_int($condV) || is_float($condV)){
							
						}elseif(is_string($condV)){
							if(!isset($this->fields[$fieldName]['filter']) || $this->fields[$fieldName]['filter'] === 'like')
								$condK.=' LIKE';
						}elseif(is_array($condV)){
							notImplemented();
						}
						
						if(is_int($key)) $this->query->addCondition($condK,$condV);
						else $this->query->addHavingCondition($condK,$condV);
						
						/*if (is_array($value)){
								if($type=='rangeint' || $type=='rangedecimal'){
									$values=array();
									if(isset($value[0]) && $value[0] !=='') $values[]= $type=='rangeint' ? (int)$value[0] : (float)$value[0];
									if(isset($value[1]) && $value[1] !=='') $values[]= $type=='rangeint' ? (int)$value[1] : (float)$value[1];
									if(!empty($values)){
										$sqlFilter .= ' AND ';
										if(count($values)==1)
											$sqlFilter .= (($key == $this->identifier OR $key == '`'.$this->identifier.'`') ? 'a.' : '').pSQL($key).' = '.current($values).' ';
										else
											$sqlFilter .= (($key == $this->identifier OR $key == '`'.$this->identifier.'`') ? 'a.' : '').pSQL($key).' BETWEEN '.$values[0].' AND '.$values[1].' ';
									}
								}else{ //datetime or date
									if (!empty($value[0])){
										if (!Validate::isDate($value[0])) $this->_errors[] = Tools::displayError('\'from:\' date format is invalid (YYYY-MM-DD)');
										else $sqlFilter .= ' AND '.pSQL($key).' >= \''.pSQL(Tools::dateFrom($value[0])).'\'';
									}
									if (!empty($value[1])){
										if (!Validate::isDate($value[1])) $this->_errors[] = Tools::displayError('\'to:\' date format is invalid (YYYY-MM-DD)');
										else $sqlFilter .= ' AND '.pSQL($key).' <= \''.pSQL(Tools::dateTo($value[1])).'\'';
									}
								}
							}else{
								$sqlFilter .= ' AND ';
								if ($type == 'int' OR $type == 'bool')
									$sqlFilter .= (($key == $this->identifier OR $key == '`'.$this->identifier.'`') ? 'a.' : '').pSQL($key).' = '.intval($value).' ';
								elseif ($type == 'decimal')
									$sqlFilter .= (($key == $this->identifier OR $key == '`'.$this->identifier.'`') ? 'a.' : '').pSQL($key).' = '.floatval($value).' ';
								elseif ($type == 'select')
									$sqlFilter .= (($key == $this->identifier OR $key == '`'.$this->identifier.'`') ? 'a.' : '').pSQL($key).' = '.pSQL($value).' ';
								else
									$sqlFilter .= (($key == $this->identifier OR $key == '`'.$this->identifier.'`') ? 'a.' : '').pSQL($key).' LIKE \'%'.pSQL($value).'%\' ';
							}*/
					}
				}
			}
			if($filter) $this->query->calcFoundRows();
		}else{
			if($this->autoBelongsTo!==false) foreach($belongsToFields as $field=>$relKey){
				$relModelName=$modelName::$_relations[$relKey]['modelName'];
				$this->query->with($relKey,array('fields'=>array($relModelName::$__displayField=>$field),'fieldsInModel'=>true));
			}
		}
		
		if($this->export!==false && isset($_GET['export']) ? true : false){
			ob_clean();
			HTable::export($_GET['export'],$this,$fields,$exportOutput,isset($this->export[1])?$this->export[1]:$this->query->getModelName(),isset($this->export[2])?$this->export[2]:null);
			if($exportOutput!==null) return; else exit;
		}
		
		$this->pagination=CPagination::create($this->query);
		$this->pagination->pageSize(25);
		$this->pagination->execute($this);
		$this->modelFields=$this->query->getModelFields();
		$this->totalResults=$this->pagination->getTotalResults();
		$this->results=$this->pagination->getResults();
		
		if($this->filter === 0 && $this->filter && empty($_POST)) $this->filter=false;
		
		if($this->pagination->getTotalResults() !== 0 || $this->filter){
			if($this->fields !== NULL) $this->_setFields($this->fields,false);
			else $this->_setFields($fields,true);
		}
	}
	
	public function getModelName(){ return $this->query->getModelName(); }
	public function getTotalResults(){ return $this->totalResults; }
	public function hasPager(){ return $this->pagination->hasPager(); }
	public function &getResults(){ return $this->results; }

	public function &pagination(){ return $this->pagination; }
	public function setActionsRUD(){
		$this->defaultAction='view';
		$this->rowActions=array('view','edit','delete');
	}
	public function callback($callback1,$callback2){
		return $this->query->noCalcFoundRows()->callback($callback1,$callback2);
	}
	
	public function _setFields($fields,$fromQuery,$export=false){
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