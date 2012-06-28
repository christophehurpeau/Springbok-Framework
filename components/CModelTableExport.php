<?php
class CModelTableExport extends CModelTableAbstract{
	public $type,$fileName,$title,$transformerClass,$params;
	
	public function init($type,$fileName,$title){
		$this->type=$type;
		$this->fileName=$fileName;
		$this->title=$title;
		return $this;
	}
	public function type($type){ $this->type=$type; return $this; }
	public function fileName($fileName){ $this->fileName=$fileName; return $this; }
	public function title($title){ $this->title=$title; return $this; }
	public function transformerClass($transformerClass){ $this->transformerClass=$transformerClass; return $this; }
	public function params($params){ $this->params=$params; return $this; }
	
	private function process($setHeaders){
		set_time_limit(120); ini_set('memory_limit', '768M'); //TXls use 512M memory cache	
		$transformerClass=$this->transformerClass===null ? $this->transformers[$this->type] : $this->transformerClass;
		
		if($setHeaders){
			header('Content-Description: File Transfer');
			header("Content-Disposition: attachment; filename=".date('Y-m-d')."_".$this->fileName.".".$this->type);
			Controller::noCache();
			header("Content-type: ".$transformerClass::getContentType());
			while(ob_get_level()!==0) ob_end_clean();
		}
		
		$transformer=new $transformerClass($this);
		$transformer->startHead();
		
		$component=&$this; $query=&$this->query->noCalcFoundRows();
		$query->callback(function(&$f) use(&$component,&$transformer,&$query){
			$component->_setFields(true);
			$transformer->titles($component->fields,$query->getFields());
			$transformer->endHead();
			$transformer->startBody();
		},function(&$row) use(&$component,&$transformer){
			$transformer->row($row,$component->fields);
		});
		$transformer->end();
		return $transformer;
	}
	
	public function display(){
		$this->process(true)->display();
	}
	public function toFile($path){
		$this->process(false)->toFile($path);
	}
	
	public function displayIfExport(){ $this->display(); exit; }
	
	/* Compatibility with CModelTable */
	public function render(){ $this->displayIfExport(); }
	public function renderEditable(){ $this->displayIfExport(); }
}
