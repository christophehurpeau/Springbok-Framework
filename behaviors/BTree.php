<?php
/**
 * Behavior for trees
 * 
 * http://www.sitepoint.com/hierarchical-data-database-3/
 * http://www.phpro.org/tutorials/Managing-Hierarchical-Data-with-PHP-and-MySQL.html#2
 * 
 * @property int $parent_id
 * @property int $left
 * @property int $right
 * @property int $level_depth
 * 
 */
trait BTree{
	public static function treeAlias(){
		return static::$__alias.'.';
	}
	public static function treeOrder(){
		return array(static::treeAlias().'left');
	}
	
	
	public function insert(){
		throw new Exception('Use insertChild($parentId) or insertAfter($nodeId)');
	}
	/*
	public function update(){
		throw new Exception('Use updateParent($parentId) or updatePostionAfter($nodeId)')
	}
	*/
	
	public function insertAfter($nodeId){
		self::beginTransaction();
		try{
			$leftNode=self::QRow()->fields('right,level_depth,parent_id')->byId($nodeId)->execute();
			self::QUpdateOneField('right','(right +2)')->where(array('right >'=>$leftNode['right']))->execute();
			self::QUpdateOneField('left','(left +2)')->where(array('left >'=>$leftNode['right']))->execute();
			$this->left=$leftNode['right']+1;
			$this->right=$leftNode['right']+2;
			$this->parent_id=$leftNode['parent_id'];
			$this->level_depth=$leftNode['level_depth'];
			parent::insert();
			self::commit();
		}catch(Exception $e){
			self::rollBack();
			throw $e;
		}
	}
	
	
	public function insertChild($parentId){
		self::beginTransaction();
		try{
			$parentNode=self::QRow()->fields('left,level_depth')->byId($parentId)->execute();
			self::QUpdateOneField('right','(right +2)')->where(array('right >'=>$leftNode['right']))->execute();
			self::QUpdateOneField('left','(left +2)')->where(array('left >'=>$leftNode['right']))->execute();
			$this->left=$parentNode['left']+1;
			$this->right=$parentNode['left']+2;
			$this->parent_id=$parentId;
			$this->level_depth=$parentNode['level_depth']+1;
			parent::insert();
			self::commit();
		}catch(Exception $e){
			self::rollBack();
			throw $e;
		}
	}
	
	public function delete(){
		if($this->beforeDelete()){
			try{
				$width=$this->right-$this->left+1;
				parent::QDeleteAll()->where(array('left BETWEEN'=>array($this->left,$this->right)))->execute();
				self::QUpdateOneField('right','(right -'.$width.')')->where(array('right >'=>$this->right))->execute();
				self::QUpdateOneField('left','(left -'.$width.')')->where(array('left >'=>$this->right))->execute();
				self::commit();
			}catch(Exception $e){
				self::rollBack();
				throw $e;
			}
			return true;
		}
	}
	
	/* Override Queries */
	
	public static function QDeleteAll(){throw new Exception('Use $node->delete() or ::deleteNode($nodeId)');}
	public static function QDeleteOne(){throw new Exception('Use $node->delete() or ::deleteNode($nodeId)');}
	
	public static function deleteNode($nodeId){
		$node=self::ById($nodeId)->fields('left,right')->execute();
		$node->delete();
	}
	
	
	
	public function hasChildren(){
		return $this->right !== $this->left+1;
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
		throw new Exception;
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
	
	public function QChildren($direct=false){
		$query=static::QAll()->orderBy(static::treeOrder());
		if($direct){
			$where[static::treeAlias().'parent_id']=$this->id;
		}else{
			$where[static::treeAlias().'left >']=$this->left;
			$where[static::treeAlias().'right <']=$this->right;
		}
		return $query->where($where);
	}
	public function children($direct=false){
		return $this->QChildren($direct)->execute();
	}
	
	
	public function QParent(){
		return /**/static::QOne()->where(array(static::treeAlias().'id'=>$this->parent_id));
	}
	/**
	 * Return parent model : reads the parent id
	 * @return SModel
	 */
	public function parent($fields=NULL){
		$query=$this->QParent();
		if($fields!==NULL) $query->fields($fields);
		return $query->execute();
	}
	
	
	public function QNextNode(){
		$query=static::QOne()->where(array(static::treeAlias().'left'=>$this->right+1));
		return $query;
	}
	public function nextNode($fields=NULL){
		$query=$this->QNextNode();
		if($fields!==NULL) $query->fields($fields);
		return $query->execute();
	}
	
	public function QPath(){
		return static::QAll()->where(array(static::treeAlias().'left <='=>$this->left,static::treeAlias().'right >='=>$this->right))
					->orderBy(static::treeOrder());
	}
	
	public function path($fields=NULL){
		$query=$this->QPath();
		if($fields !== NULL) $query->fields($fields);
		return $query->execute();
	}
	
	public static function getQPath($id){
		$model=static::QOne()->fields('left,right')->where(array(static::treeAlias().'id'=>$id));
		return $model->QPath();
	}
	
	public static function getPath($id,$fields){
		$model=static::QOne()->fields('left,right')->where(array(static::treeAlias().'id'=>$id));
		return $model->path($fields);
	}
	
	
	
	public static function generateTreeList(){
		return array();
	}
	
	public static function generateSimpleTreeList($query=NULL){
		if($query===NULL) $query=static::QAll();
		
		$result=$query->tabResKey(self::_getPkName())->execute();
		
		$tree=array();
		foreach($result as &$res) $res->_set('children',array());
		foreach($result as &$res){
			if($res->parent_id && isset($result[$res->parent_id])){
				$result[$res->parent_id]->_getRef('children')[]=$res;
			}else $tree[]=$res;
		}
		
		return $tree;
	}
	
	
	public static function TreeView(){
		return new HTree(self::generateSimpleTreeList());
	}
	
	public static function rebuild(){
		self::beginTransaction();
		self::_rebuild(1,1,0);
		self::commit();
	}
	
	private static function _rebuild($parent=1,$left=1,$depth=0){
		$right=$left+1; $childDepth=$depth+1;
		self::QValues()->field('id')->byParent_id($parent)
			->orderBy(static::$__orderByField)
			->callback(function($childId) use(&$right,$childDepth){
				$right=self::_rebuild($childId,$right,$childDepth);
			}); 
		/*
		$children=self::QValues()->field('id')->byParent_id($parent);
		foreach($children as $childId) $right=self::rebuild($childId,$right);*/
		if($parent!==0)
			self::QUpdate()->set(array('left'=>$left,'right'=>$right,'level_depth'=>$depth,'updated'=>false))
					->where(array('id'=>$parent))->execute();
		return $right+1;
	}
	
	public static function rebuildNoRootParent(){
		self::beginTransaction();
		self::_rebuild(0,0,0);
		self::commit();
	}
}
