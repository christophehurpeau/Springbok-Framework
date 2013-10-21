<?php
/**
 * Tree Helper
 */
class HTree{
	private $tree,$actionView;
	
	/**
	 * @param array
	 */
	public function __construct($tree){
		$this->tree=$tree;
	}
	
	/**
	 * @param string
	 * @return HTree|self
	 */
	public function actionView($url){
		if(rtrim($url,'/')!==$url) throw new Exception('Please provide the url without the trailing "/"');
		$this->actionView=$url; return $this;
	}
	
	/**
	 * @return string
	 */
	public function render(){
		$id='tree'.uniqid();
		//HHtml::jsReady('S.tree.prepare("'.$id.'","/'.lcfirst(CRoute::getController()).'")');
		return $this->_display($this->tree,array('id'=>$id,'class'=>'spaced'));
	}
	
	/**
	 * @param array
	 * @param array
	 */
	private function _display($tree,$ulAttributes=array()){
		$res='<ul'.HHtml::_attributes($ulAttributes).'>';
		foreach($tree as $elt){
			$res.='<li data-id="'.$elt->id.'" draggable="true" ondragstart="event.dataTransfer.setData(\'text/plain\',\'This text may be dragged\')">';
			$res.=$this->actionView === null ? '<span class="name">'.h($elt->name()).'</span>' :'<a href="'.$this->actionView.'/'.$elt->id.'">'.h($elt->name()).'</a>';
				/*.' <span class="actions">'
				//	.'<a href="#" class="action icon add"></a>'
				//	.'<a href="#" class="action icon edit"></a>'
				//	.'<a href="#" class="action icon delete"></a>'
				//.'</span>';
				*/
			if(!empty($elt->children)) $res.=$this->_display($elt->children);
			$res.='</li>';
		}
		return $res.'</ul>';
	}
	
	/**
	 * @param array array of SModels
	 * @uses SModel::name()
	 */
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
