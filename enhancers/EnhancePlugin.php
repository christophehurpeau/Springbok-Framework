<?php
include_once __DIR__.DS.'AEnhance.php';
include_once __DIR__.DS.'DefaultFolderEnhancer.php';

class EnhancePlugin extends AEnhance{
	
	public function initDev(&$dev){
		parent::initDev($dev);
		
		$d=new Folder($dev->getPath().'logs',0777);
	}
	
	public function initProd(&$prod){	
		parent::initProd($prod);
		
		$d=new Folder($prod->getPath().'logs',0777);
	}
	
	
	/**
	 * @param Folder $dir
	 */
	public function recursiveDir($srcDir,Folder $dir,$devDir,$prodDir,$exclude=false,$class=false){
		$dirs=$dir->listDirs(false);
		$devFolder=new Folder($devDir); $prodFolder=new Folder($prodDir);
		/*
		foreach(array_diff_key($devFolder->listDirs(false),$dirs) as $d) if(!$exclude || !in_array($d->getName(),$exclude)) $d->delete();
		foreach(array_diff_key($prodFolder->listDirs(false),$dirs) as $d) if(!$exclude || !in_array($d->getName(),$exclude)) $d->delete();
		*/
		$this->newDef['enhancedFolders'][$dir->getPath()]=array('dev'=>$devDir,'prod'=>$prodDir);
		
		
		$defaultClass=$class;
		foreach($dirs as $d){
			$dirname=$d->getName();
			if($dirname[0]==='.') continue;
			
			$newDevDir=$devDir.$dirname.DS; $newProdDir=$prodDir.$dirname.DS;
			
			$excludeFiles=false;
			if($defaultClass===false){
				$class='PhpFile';
				switch($d->getPath()){
					case $srcDir.'config'.DS: $class='ConfigFile'; $excludeFiles=array('modules.php'); break;
					case $srcDir.'controllers'.DS: $class='ControllerFile'; $exclude=array('methods'); break;
					case $srcDir.'jobs'.DS: $class='JobFile'; break;
					case $srcDir.'models'.DS: $class='ModelFile'; $exclude=array('infos'); break;
					case $srcDir.'modules'.DS: $class='ModuleFile'; break;
					case $srcDir.'views'.DS: $class='ViewFile'; break;
					case $srcDir.'web'.DS.'img'.DS: $excludeFiles=array('img-sprite.png'); break;
				}
				
				if($class !== 'PhpFile')
					$class::startEnhanceApp();
			}else $class=$defaultClass;
			
			$folderEnhancer=new DefaultFolderEnhancer($this->config,$d, $newDevDir,$newProdDir,$this->oldDef,$this->newDef);
			$folderEnhancer->process($class,$excludeFiles);
			
			$this->recursiveDir($srcDir,$d, $newDevDir,$newProdDir,$exclude,$class);
			
			if($class !== 'PhpFile'){
				$class::endEnhanceApp();
			}
		}
	}
}
