<?php
class HTree{
	private $tree,$actionView;
	
	public function __construct($tree){
		$this->tree=$tree;
	}
	
	public function actionView($url){
		if(rtrim($url,'/')!==$url) throw new Exception('Please provide the url without the trailing "/"');
		$this->actionView=$url; return $this;
	}
	
	
	public function render(){
		$id='tree'.uniqid();
		HHtml::jsReady('S.tree.prepare("'.$id.'","/'.lcfirst(CRoute::getController()).'")');
		return $this->_display($this->tree,array('id'=>$id));
	}
	
	private function _display($tree,$ulAttributes=array()){
		$res='<ul'.HHtml::_attributes($ulAttributes).'>';
		foreach($tree as $elt){
			$res.='<li data-id="'.$elt->id.'">';
			$res.=$this->actionView === null ? '<span class="name">'.h($elt->name()).'</span>' :'<a href="'.$this->actionView.'/'.$elt->id.'">'.h($elt->name()).'</a>'
				.' <span class="actions">'
					.'<a href="#" class="action icon add"></a>'
					.'<a href="#" class="action icon edit"></a>'
					.'<a href="#" class="action icon delete"></a>'
				.'</span>';
			if(!empty($elt->children)) $res.=$this->_display($elt->children);
			$res.='</li>';
		}
		return $res.'</ul>';
	}
	
	public static function display($tree){
		$res='<ul>';
		foreach($tree as $elt){
			$res.='<li>';
			$res.='<span class="elt">'.h($elt->name()).'</span>';
			if(!empty($elt->children)) $res.=self::display($elt->children);
			$res.='</li>';
		}
		return $res.'</ul>';
	}
}