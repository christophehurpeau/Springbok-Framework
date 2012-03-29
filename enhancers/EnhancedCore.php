<?php
class EnhancedCore extends Enhanced{
	private $fileDef;
	public function loadFileDef($force){
		if(file_exists(($this->filedef=$this->getAppDir().'enhance_def.json')) && !isset($_GET['force']))
			$this->oldDef=json_decode(file_get_contents($this->filedef),true);
	}
	
	public function writeFileDef($force){
		if(!empty($this->newDef)){
			$this->newDef['ENHANCED_TIME']=time();
			$this->newDef['ENHANCED_DATE']=date('Y-m-d H:i:s');
			if(!isset($this->newDef['LAST_CHANGE_IN_ENHANCERS']))
				$this->newDef['LAST_CHANGE_IN_ENHANCERS']=!empty($this->oldDef['LAST_CHANGE_IN_ENHANCERS'])?$this->oldDef['LAST_CHANGE_IN_ENHANCERS']:time();
			file_put_contents($this->filedef,json_encode($this->newDef));
		}
	}
}
