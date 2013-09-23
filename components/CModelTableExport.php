<?php
class CModelTableExport extends CModelTableAbstract{
	public $type,$fileName,$title,$transformerClass,$params;
	
	public function init($type,$fileName,$title){
		$this->type=$type;
		$this->fileName=$fileName;
		$this->title=$title;
		return $this;
	}
	
	/**
	 * Set the export type
	 * 
	 * @param string
	 * @return CModelTableExport
	 */
	public function type($type){
		$this->type=$type;
		return $this;
	}
	
	/**
	 * Set the filename, when downloaded
	 * 
	 * @param string
	 * @return CModelTableExport
	 */
	public function fileName($fileName){
		$this->fileName=$fileName;
		return $this;
	}
	
	/**
	 * Set the title, ie in xls
	 * 
	 * @param string
	 * @return CModelTableExport
	 */
	public function title($title){
		$this->title=$title;
		return $this;
	}
	
	/**
	 * Set the transformer class
	 * 
	 * @param string
	 * @return CModelTableExport
	 */
	public function transformerClass($transformerClass){
		$this->transformerClass = $transformerClass;
		return $this;
	}
	
	/**
	 * Set the params
	 * 
	 * @param array
	 * @return CModelTableExport
	 */
	public function params($params){
		$this->params = $params;
		return $this;
	}
	
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
		
		$component=$this; $query=$this->query->noCalcFoundRows();
		$query->callback(function($f) use($component,$transformer,$query){
			$component->_setFields(true);
			$transformer->titles($component->fields,$query->getFields());
			$transformer->endHead();
			$transformer->startBody();
		},function($row) use($component,$transformer){
			$transformer->row($row,$component->fields);
		});
		$transformer->end();
		return $transformer;
	}
	
	/**
	 * Display the export
	 * 
	 * @return void
	 */
	public function display(){
		$this->process(true)->display();
	}
	/**
	 * Generate a file from the export
	 * 
	 * Export to a local file on the server
	 * 
	 * @param string
	 * @return void
	 */
	public function toFile($path){
		$this->process(false)->toFile($path);
	}
	
	/**
	 * Displays then exist
	 * 
	 * @return void
	 */
	public function displayIfExport(){
		$this->display();
		exit;
	}
	
	/* Compatibility with CModelTable */
	
	/**
	 * Render : display then exit
	 * 
	 * @return void
	 */
	public function render(){ $this->displayIfExport(); }
	
	/**
	 * Render : display then exit
	 * 
	 * @return void
	 */
	public function renderEditable(){ $this->displayIfExport(); }
}
