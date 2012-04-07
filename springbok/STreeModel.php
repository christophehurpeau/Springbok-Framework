<?php
/*
 * http://www.sitepoint.com/hierarchical-data-database-3/
 * http://www.phpro.org/tutorials/Managing-Hierarchical-Data-with-PHP-and-MySQL.html#2
 */
class STreeModel extends SSqlModel{
	protected static $where=array();
	
	protected function beforeInsert(){
		$this->level_depth=self::QValue()->field('level_depth')->byId($this->parent_id)->execute()+1;
		if($this->parent_id){
			
		}else{
			$edge=self::_getMax();
		}
		
		return parent::beforeInsert();
	}
	
	protected function afterInsert(){
		$this->_setParent($this->parent_id);
	}
	
	private $mustRebuild;
	protected function beforeUpdate(){
		if(!empty($this->parent_id) && self::QValue()->field('parent_id')->execute()) $this->mustRebuild=true;
		return parent::beforeUpdate();
	}
	
	protected function afterUpdate(){
		if($this->mustRebuild===true) $this->_setParent($this->parent_id);
	}
	
	protected function _setParent($parentId){
		
	}
	
	
	
	
	public function isDescendantOf($id){
		return false;//TODO
	}
	
	public function isAncestorOf($id){
		return false;//TODO
	}
	
	
	/**
	 * @param int|bool $number how many places to move the node or true to move to last position
	 */
	public function moveDown($number=1){
		if(!$number) return false;
		if($this->parent_id){
			$parent=$this->parent('id,left,right');
			if(($parent->right + 1) == $parentNode[$right]) return false;
		}
		$nextNode=$this->nextNode('id,left,right');
		if(!$nextNode) return false;
		
		$edge=self::_getMax();
		self::_sync($edge-$this->left+1,'+','BETWEEN '.$this->left.' AND '.$this->right);
		self::_sync($nextNode->left-$this->left,'-','BETWEEN '.$nextNode->left.' AND '.$nextNode->right);
		self::_sync($edge-$this->left - ($nextNode->right - $nextNode->left),'-','> '.$edge);
		
		if(is_int($number)) $number--;
		if($number) $this->moveDown($number);
		return true;
	}
	
	
	public function childCount($direct=false){
		if($direct) return static::QCount()->byParent_id($this->id);
		return ($this->right-$this->left -1) / 2;
	}
	
	public function &QChildren($direct=false){
		$query=static::QAll()->orderBy('left');
		if($direct){
			$where['parent_id']=$this->id;
		}else{
			$where['left >']=$this->left;
			$where['right <']=$this->right;
		}
		return $query->where($where);
	}
	public function children($direct=false){
		return $this->QChildren()->execute();
	}
	
	
	public function &QParent(){
		$query=static::QOne()->byId($this->parent_id);
		return $query;
	}
	/**
	 * Return parent model : reads the parent id
	 * @return Model
	 */
	public function parent($fields=NULL){
		$query=$this->QParent();
		if($fields!==NULL) $query->fields($fields);
		return $query->execute();
	}
	
	
	public function QNextNode(){
		$query=static::QOne()->byLeft($this->right+1);
		return $query;
	}
	public function nextNode($fields=NULL){
		$query=$this->QNextNode();
		if($fields!==NULL) $query->fields($fields);
		return $query->execute();
	}
	
	public function &QPath(){
		$query=static::QAll()->where(array('left <='=>$this->left,'right >='=>$this->right))->orderBy('left');
		return $query;
	}
	
	public function path($fields=NULL){
		$query=$this->QPath();
		if($fields !== NULL) $query->fields($fields);
		return $query->where(array('left <='=>$this->left,'right >='=>$this->right))->orderBy('left')->execute();
	}
	
	public static function &getQPath($id){
		$model=static::QOne()->fields('left,right')->byId($id);
		return $model->QPath();
	}
	
	public static function &getPath($id,$fields){
		$model=static::QOne()->fields('left,right')->byId($id);
		return $model->path($fields);
	}
	
	
	
	public static function generateTreeList(){
		return array();
	}
	
	public static function generateSimpleTreeList($query=NULL,$parentId='parent_id'){
		if($query===NULL) $query=static::QAll();
		
		$result=$query->tabResKey(self::_getPkName())->execute();
		
		$tree=array();
		
		foreach($result as &$res){
			if($res->$parentId){
				$result[$res->$parentId]->children[]=&$res;
			}else $tree[]=$res;
		}
		
		return $tree;
	}
	
	public static function _getMax($created=false){
		$query=self::QValue()->field('MAX(`right`)')->execute();
		if($created){
			$where=static::$where;
			$where['id !=']=$model->id;
			$query->where($where);
		}
		return $query->execute();
	}
	public static function _getMin(){
		return self::QValue()->field('MIN(`left`)')->where($where)->execute();
	}
	
	public static function sync($shift,$dir='+',$cond=array(),$created=false,$field='both'){
		if($field==='both'){
			self::sync($shift,$dir,$cond,$created,'left');
			$field='right';
		}
		
		$where=NULL;
		if(is_string($cond)){
			$where=array('`'.$field.'` '.$cond);
			if($created) $where['id !=']=$model->id;
		}elseif($created){
			$where=array('id !='=>$model->id);
		}
		return self::QUpdateOneField($field,array('`'.$field.'` '.$dir.$shift))->where($where)->execute();
	}
	
	
	public static function rebuild($parent=1,$left=1){
		$right = $left+1;
		$children=self::QValues()->field('id')->byParent_id($parent);
		foreach($children as $childId) $right=self::rebuild($modelName,$childId,$right);
		$model=new $modelName;
		$model->id=$parent;
		$model->left=$left;
		$model->right=$right;
		$model->update();
		return $right+1;
	}
}
