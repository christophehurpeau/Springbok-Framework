<?php
class HTree{
	private $tree,$actionView;
	
	public function __construct($tree){
		$this->tree=$tree;
	}
	
	public function actionView($url){ $this->actionView=$url; return $this; }
	
	
	public function render(){
		return $this->_display($this->tree);
	}
	
	private function _display($tree){
		$id='tree'.uniqid();
		$res='<ul id="'.$id.'">';
		foreach($tree as $elt){
			$res.='<li>';
			$res.=$this->actionView === null ? h($elt->name()) :'<a href="'.$this->actionView.'/'.$elt->id.'">'.h($elt->name()).'</a>'
				.'<span class="actions">'
					.'<a href="#" class="action icon add"></a>'
					.'<a href="#" class="action icon edit"></a>'
					.'<a href="#" class="action icon delete"></a>'
				.'</span>';
			if(!empty($elt->children)) $res.=$this->_display($elt->children);
			$res.='</li>';
		}
		HHtml::jsReady('S.tree.prepare("'.$id.'","/'.lcfirst(CRoute::getController()).'")');
		return $res.'<ul>';
	}
	
	public static function display($tree){
		$res='<ul>';
		foreach($tree as $elt){
			$res.='<li>';
			$res.='<span class="elt">'.h($elt->name()).'</span>';
			if(!empty($elt->children)) $res.=self::display($elt->children);
			$res.='</li>';
		}
		return $res.'<ul>';
	}
}